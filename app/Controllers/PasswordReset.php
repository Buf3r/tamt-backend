<?php

namespace App\Controllers;

use App\Libraries\BrevoMailer;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;

class PasswordReset extends BaseController
{
    use ResponseTrait;

    private const TTL_MINUTES      = 60;   // vigencia del enlace
    private const COOLDOWN_SECONDS = 60;   // mínimo entre solicitudes por usuario
    private const MAX_PER_HOUR     = 5;    // máximo de correos por usuario por hora

    /**
     * POST /api/password/forgot   { "email": "..." }
     * Siempre responde 200 con el mismo mensaje (no revela si el correo existe).
     */
    public function forgot()
    {
        if (!$this->validate(['email' => 'required|valid_email|max_length[64]'])) {
            return $this->failValidationErrors(['email' => 'Correo inválido']);
        }

        $email = strtolower(trim((string) $this->request->getVar('email')));
        $user  = (new UserModel())->where('email', $email)->first();

        if ($user) {
            $this->issueAndSend($user);
        }

        return $this->respond([
            'status'  => 200,
            'message' => 'Si el correo está registrado, te enviamos un enlace para restablecer tu contraseña.',
        ]);
    }

    /** GET /password/reset/{token}  -> formulario web */
    public function form(string $token = '')
    {
        $state = $this->findValidReset($token) ? 'form' : 'invalid';
        return $this->render($state, $token);
    }

    /** POST /password/reset  (token, password, password_confirm) */
    public function submit()
    {
        $token    = (string) $this->request->getPost('token');
        $password = (string) $this->request->getPost('password');
        $confirm  = (string) $this->request->getPost('password_confirm');

        $reset = $this->findValidReset($token);
        if (!$reset) {
            return $this->render('invalid', $token);
        }

        if ($password !== $confirm) {
            return $this->render('form', $token, 'Las contraseñas no coinciden.');
        }

        $rule = $this->passwordError($password);
        if ($rule !== null) {
            return $this->render('form', $token, $rule);
        }

        $users = new UserModel();
        $ok    = $users->update((int) $reset['user_id'], [
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        if (!$ok) {
            log_message('error', 'PasswordReset: no se pudo actualizar la contraseña del usuario ' . $reset['user_id']);
            return $this->render('form', $token, 'No se pudo actualizar la contraseña. Inténtalo de nuevo.');
        }

        // Invalida este y cualquier otro enlace pendiente del usuario
        db_connect()->table('password_resets')
            ->where('user_id', $reset['user_id'])
            ->where('used_at', null)
            ->update(['used_at' => date('Y-m-d H:i:s')]);

        return $this->render('done', $token);
    }

    // ───────────────────────── helpers ─────────────────────────

    private function issueAndSend(array $user): void
    {
        $db    = db_connect();
        $table = $db->table('password_resets');
        $now   = time();

        // Límite por hora
        $recent = $db->table('password_resets')
            ->where('user_id', $user['user_id'])
            ->where('created_at >=', date('Y-m-d H:i:s', $now - 3600))
            ->countAllResults();
        if ($recent >= self::MAX_PER_HOUR) {
            return;
        }

        // Enfriamiento entre solicitudes
        $last = $db->table('password_resets')
            ->where('user_id', $user['user_id'])
            ->orderBy('id', 'DESC')
            ->get(1)
            ->getRowArray();
        if ($last && strtotime($last['created_at']) > $now - self::COOLDOWN_SECONDS) {
            return;
        }

        // Invalida enlaces anteriores
        $db->table('password_resets')
            ->where('user_id', $user['user_id'])
            ->where('used_at', null)
            ->update(['used_at' => date('Y-m-d H:i:s', $now)]);

        $token = bin2hex(random_bytes(32));
        $table->insert([
            'user_id'    => $user['user_id'],
            'token_hash' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', $now + self::TTL_MINUTES * 60),
            'used_at'    => null,
            'created_at' => date('Y-m-d H:i:s', $now),
        ]);

        $link = base_url('password/reset/' . $token);

        $sent = (new BrevoMailer())->send(
            (string) $user['email'],
            (string) $user['name'],
            'Restablece tu contraseña de Subastalo',
            $this->emailHtml((string) $user['name'], (string) $user['username'], $link),
            $this->emailText((string) $user['username'], $link)
        );

        if (!$sent) {
            log_message('error', 'PasswordReset: no se pudo enviar el correo al usuario ' . $user['user_id']);
        }
    }

    private function findValidReset(string $token): ?array
    {
        if ($token === '' || !ctype_xdigit($token) || strlen($token) !== 64) {
            return null;
        }

        $row = db_connect()->table('password_resets')
            ->where('token_hash', hash('sha256', $token))
            ->where('used_at', null)
            ->get()
            ->getRowArray();

        if (!$row || strtotime($row['expires_at']) < time()) {
            return null;
        }

        return $row;
    }

    /** Mismas reglas que la app: 8-16, mayúscula, minúscula, número y símbolo. */
    private function passwordError(string $p): ?string
    {
        $len = mb_strlen($p);
        if ($len < 8 || $len > 16) {
            return 'La contraseña debe tener entre 8 y 16 caracteres.';
        }
        if (!preg_match('/[A-Z]/', $p) || !preg_match('/[a-z]/', $p) || !preg_match('/[0-9]/', $p) || !preg_match('/[^A-Za-z0-9]/', $p)) {
            return 'Debe incluir mayúscula, minúscula, número y un símbolo.';
        }
        return null;
    }

    private function render(string $state, string $token, ?string $error = null)
    {
        $this->response->setHeader('Cache-Control', 'no-store');
        $this->response->setHeader('Referrer-Policy', 'no-referrer');

        return $this->response->setBody(view('password_reset_form', [
            'state' => $state,
            'token' => $token,
            'error' => $error,
        ]));
    }

    private function emailHtml(string $name, string $username, string $link): string
    {
        $name     = esc($name);
        $username = esc($username);
        $link     = esc($link);
        $mins     = self::TTL_MINUTES;

        return <<<HTML
<div style="background:#111111;padding:24px;font-family:Arial,Helvetica,sans-serif;">
  <div style="max-width:480px;margin:0 auto;background:#1a1a1a;border-radius:16px;padding:28px;color:#ffffff;">
    <div style="font-size:22px;font-weight:700;color:#FBC702;letter-spacing:2px;">SUBASTALO</div>
    <h2 style="margin:20px 0 8px;font-size:18px;">Hola {$name}</h2>
    <p style="color:#cccccc;line-height:1.5;">Recibimos una solicitud para restablecer la contraseña de tu cuenta.<br>Tu usuario es: <b style="color:#ffffff;">{$username}</b></p>
    <p style="margin:24px 0;">
      <a href="{$link}" style="background:#FBC702;color:#111111;text-decoration:none;font-weight:700;padding:14px 22px;border-radius:12px;display:inline-block;">Crear nueva contraseña</a>
    </p>
    <p style="color:#999999;font-size:13px;line-height:1.5;">El enlace vence en {$mins} minutos y solo se puede usar una vez. Si no fuiste tú, ignora este correo: tu contraseña no cambiará.</p>
    <p style="color:#777777;font-size:12px;word-break:break-all;">Si el botón no funciona, copia este enlace en tu navegador:<br>{$link}</p>
  </div>
</div>
HTML;
    }

    private function emailText(string $username, string $link): string
    {
        return "Recibimos una solicitud para restablecer tu contraseña de Subastalo.\n"
            . "Tu usuario es: {$username}\n\n"
            . "Crea una nueva contraseña aquí (vence en " . self::TTL_MINUTES . " minutos):\n{$link}\n\n"
            . "Si no fuiste tú, ignora este correo.";
    }
}
