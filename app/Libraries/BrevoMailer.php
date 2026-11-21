<?php

namespace App\Libraries;

/**
 * Envío de correos por la API HTTP de Resend (puerto 443).
 * Mantiene el nombre de clase y variables de entorno (BREVO_API_KEY) para compatibilidad.
 *
 * Variables de entorno:
 *   BREVO_API_KEY    clave de API de Resend (re_...)
 *   MAIL_FROM_EMAIL  remitente (ej. onboarding@resend.dev o correo en dominio verificado)
 *   MAIL_FROM_NAME   nombre visible (opcional)
 */
class BrevoMailer
{
    public function send(string $toEmail, string $toName, string $subject, string $html, string $text = ''): bool
    {
        $apiKey    = env('BREVO_API_KEY');
        $fromEmail = env('MAIL_FROM_EMAIL');
        $fromName  = env('MAIL_FROM_NAME') ?: 'Subastalo';

        if (empty($apiKey) || empty($fromEmail)) {
            log_message('error', 'BrevoMailer (Resend): faltan BREVO_API_KEY o MAIL_FROM_EMAIL');
            return false;
        }

        // Resend espera el formato "Nombre <correo@dominio.com>"
        $from = $fromName !== '' ? sprintf('%s <%s>', $fromName, $fromEmail) : $fromEmail;
        $to   = $toName !== '' ? sprintf('%s <%s>', $toName, $toEmail) : $toEmail;

        $payload = [
            'from'    => $from,
            'to'      => [$to],
            'subject' => $subject,
            'html'    => $html,
        ];

        if ($text !== '') {
            $payload['text'] = $text;
        }

        try {
            $client   = \Config\Services::curlrequest(['timeout' => 15, 'http_errors' => false]);
            $response = $client->post('https://api.resend.com/emails', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                ],
                'json' => $payload,
            ]);

            $code = $response->getStatusCode();
            if ($code >= 200 && $code < 300) {
                return true;
            }

            log_message('error', 'BrevoMailer (Resend): respuesta ' . $code . ' - ' . substr((string) $response->getBody(), 0, 300));
            return false;
        } catch (\Throwable $e) {
            log_message('error', 'BrevoMailer (Resend): ' . $e->getMessage());
            return false;
        }
    }
}