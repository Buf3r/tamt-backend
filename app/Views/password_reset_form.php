<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Subastalo · Restablecer contraseña</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: #111; color: #fff; font-family: Arial, Helvetica, sans-serif; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card { width: 100%; max-width: 420px; background: #1a1a1a; border: 1px solid rgba(255,255,255,.08); border-radius: 22px; padding: 28px; }
        .brand { color: #FBC702; font-weight: 700; letter-spacing: 3px; font-size: 20px; }
        h1 { font-size: 20px; margin: 18px 0 6px; }
        p { color: #bbb; line-height: 1.5; font-size: 14px; margin: 0 0 16px; }
        label { display: block; font-size: 12px; color: #999; margin: 14px 0 6px; font-weight: 700; }
        .field { position: relative; }
        input[type=password], input[type=text] { width: 100%; background: #262626; border: 1px solid rgba(255,255,255,.1); border-radius: 12px; color: #fff; padding: 14px 44px 14px 14px; font-size: 15px; outline: none; }
        input:focus { border-color: #FBC702; }
        .eye { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: 0; color: #888; cursor: pointer; font-size: 13px; padding: 6px; }
        button.primary { width: 100%; margin-top: 22px; background: #FBC702; color: #111; border: 0; border-radius: 12px; padding: 15px; font-size: 15px; font-weight: 700; cursor: pointer; }
        .error { background: rgba(229,57,53,.12); border: 1px solid rgba(229,57,53,.4); color: #ff7b78; padding: 10px 12px; border-radius: 10px; font-size: 13px; margin-bottom: 8px; }
        .ok { text-align: center; }
        .badge { width: 64px; height: 64px; border-radius: 50%; background: rgba(67,160,71,.15); color: #43A047; display: flex; align-items: center; justify-content: center; font-size: 32px; margin: 8px auto 12px; }
        .badge.bad { background: rgba(229,57,53,.15); color: #E53935; }
        small { color: #777; display: block; margin-top: 10px; font-size: 12px; }
    </style>
</head>
<body>
<div class="card">
    <div class="brand">SUBASTALO</div>

    <?php if ($state === 'form'): ?>
        <h1>Crea tu nueva contraseña</h1>
        <p>Debe tener entre 8 y 16 caracteres, con mayúscula, minúscula, número y un símbolo.</p>

        <?php if (!empty($error)): ?>
            <div class="error"><?= esc($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= esc(base_url('password/reset')) ?>" autocomplete="off">
            <input type="hidden" name="token" value="<?= esc($token) ?>">

            <label for="password">Nueva contraseña</label>
            <div class="field">
                <input type="password" id="password" name="password" required minlength="8" maxlength="16" autocomplete="new-password">
                <button type="button" class="eye" onclick="toggle('password', this)">Ver</button>
            </div>

            <label for="password_confirm">Repite la contraseña</label>
            <div class="field">
                <input type="password" id="password_confirm" name="password_confirm" required minlength="8" maxlength="16" autocomplete="new-password">
                <button type="button" class="eye" onclick="toggle('password_confirm', this)">Ver</button>
            </div>

            <button type="submit" class="primary">Guardar contraseña</button>
        </form>

    <?php elseif ($state === 'done'): ?>
        <div class="ok">
            <div class="badge">✓</div>
            <h1>¡Contraseña actualizada!</h1>
            <p>Ya puedes volver a la app de Subastalo e iniciar sesión con tu nueva contraseña.</p>
            <small>Si tenías activado el acceso con huella, vuelve a activarlo después de iniciar sesión.</small>
        </div>

    <?php else: ?>
        <div class="ok">
            <div class="badge bad">!</div>
            <h1>Enlace no válido</h1>
            <p>Este enlace venció o ya fue usado. Vuelve a la app y solicita uno nuevo desde “¿Olvidaste tu contraseña?”.</p>
        </div>
    <?php endif; ?>
</div>

<script>
    function toggle(id, btn) {
        var el = document.getElementById(id);
        var show = el.type === 'password';
        el.type = show ? 'text' : 'password';
        btn.textContent = show ? 'Ocultar' : 'Ver';
    }
</script>
</body>
</html>
