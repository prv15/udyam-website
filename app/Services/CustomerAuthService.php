<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\App;
use App\Repositories\CustomerPortalRepository;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailerException;

final class CustomerAuthService
{
    public function __construct(private readonly CustomerPortalRepository $customers)
    {
    }

    public function sendVerification(int $userId, string $email, string $name): void
    {
        $token = bin2hex(random_bytes(32));
        $this->customers->createToken($userId, 'verify_email', $token, new \DateTimeImmutable('+24 hours'));
        $this->sendMail(
            $email,
            $name,
            'Verify your email — Udyam Ventures',
            'Confirm your email address',
            'Thanks for creating a Udyam Ventures customer account. Please confirm this is your email address to activate your account and sign in to your customer workspace.',
            $this->absoluteUrl('/customer/verify-email?token=' . rawurlencode($token)),
            'Verify email address',
            'This link expires in 24 hours. If you did not create this account, you can safely ignore this email.'
        );
    }

    public function sendPasswordReset(int $userId, string $email, string $name): void
    {
        $token = bin2hex(random_bytes(32));
        $this->customers->createToken($userId, 'reset_password', $token, new \DateTimeImmutable('+60 minutes'));
        $this->sendMail(
            $email,
            $name,
            'Reset your password — Udyam Ventures',
            'Reset your password',
            'We received a request to reset the password on your Udyam Ventures customer account. Click the button below to choose a new password.',
            $this->absoluteUrl('/customer/reset-password?token=' . rawurlencode($token)),
            'Reset password',
            'This link expires in 60 minutes. If you did not request this, no action is required — your password will remain unchanged.'
        );
    }

    /**
     * Sends a branded transactional email. Tries authenticated SMTP first; if the SMTP
     * connection fails (common on shared hosting where outbound SMTP ports are blocked),
     * falls back to the server's native mail transport so the customer still receives the
     * email rather than being silently stuck. Mirrors the pattern used for consultation
     * request emails elsewhere in the app.
     */
    private function sendMail(string $email, string $name, string $subject, string $heading, string $copy, string $link, string $button, string $footnote = ''): void
    {
        $mail = $this->buildMessage($email, $name, $subject, $heading, $copy, $link, $button, $footnote);
        try {
            $mail->send();
        } catch (MailerException $smtpException) {
            if (!filter_var($_ENV['SMTP_FALLBACK_MAIL'] ?? true, FILTER_VALIDATE_BOOLEAN)) {
                throw $smtpException;
            }
            error_log('Customer auth SMTP send failed; trying native mail transport: ' . $smtpException->getMessage());
            $fallback = $this->buildMessage($email, $name, $subject, $heading, $copy, $link, $button, $footnote);
            $fallback->isMail();
            $fallback->SMTPAuth = false;
            $fallback->send();
        }
    }

    private function buildMessage(string $email, string $name, string $subject, string $heading, string $copy, string $link, string $button, string $footnote): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = (string) ($_ENV['SMTP_HOST'] ?? '');
        $mail->SMTPAuth = true;
        $mail->Username = (string) ($_ENV['SMTP_USERNAME'] ?? '');
        $mail->Password = (string) ($_ENV['SMTP_PASSWORD'] ?? '');
        $mail->Port = (int) ($_ENV['SMTP_PORT'] ?? 465);
        $mail->SMTPSecure = (string) ($_ENV['SMTP_ENCRYPTION'] ?? 'ssl');
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($mail->Username ?: 'noreply@udyamventures.com', 'Udyam Ventures');
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $this->renderTemplate($name, $heading, $copy, $link, $button, $footnote);
        $mail->AltBody = "{$heading}\n\n{$copy}\n\n{$button}: {$link}\n\n{$footnote}";
        return $mail;
    }

    private function renderTemplate(string $name, string $heading, string $copy, string $link, string $button, string $footnote): string
    {
        $logo = htmlspecialchars($this->absoluteUrl('/uploads/media/original/home/udyam-ventures-logo-cropped.png'), ENT_QUOTES, 'UTF-8');
        $safeLink = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
        $safeName = htmlspecialchars($name !== '' ? $name : 'there', ENT_QUOTES, 'UTF-8');
        $year = date('Y');

        return '<div style="margin:0;background:#f2f5fb;padding:34px 14px;font-family:Arial,Helvetica,sans-serif">'
            . '<div style="max-width:600px;margin:auto;background:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 18px 48px rgba(7,24,75,.12)">'
            . '<div style="padding:30px 34px;background:linear-gradient(135deg,#071b51,#3b2abb);text-align:left">'
            . '<img src="' . $logo . '" alt="Udyam Ventures" style="height:38px;margin-bottom:18px;display:block">'
            . '<div style="font-size:12px;letter-spacing:2px;color:#f0b743;text-transform:uppercase;font-weight:700">Udyam Ventures</div>'
            . '<h1 style="margin:10px 0 0;font-family:Georgia,serif;font-size:26px;color:#ffffff;font-weight:600">' . htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') . '</h1>'
            . '</div>'
            . '<div style="padding:32px 34px 8px;color:#344054">'
            . '<p style="font-size:15px;margin:0 0 16px">Hello ' . $safeName . ',</p>'
            . '<p style="line-height:1.7;font-size:15px;margin:0 0 26px">' . htmlspecialchars($copy, ENT_QUOTES, 'UTF-8') . '</p>'
            . '<div style="text-align:center;margin:0 0 26px">'
            . '<a href="' . $safeLink . '" style="background:#315bd8;color:#ffffff;padding:14px 34px;border-radius:10px;text-decoration:none;font-weight:700;font-size:15px;display:inline-block">' . htmlspecialchars($button, ENT_QUOTES, 'UTF-8') . '</a>'
            . '</div>'
            . '<p style="font-size:13px;color:#73809d;line-height:1.6;margin:0 0 26px">If the button above does not work, copy and paste this link into your browser:<br>'
            . '<a href="' . $safeLink . '" style="color:#315bd8;word-break:break-all">' . $safeLink . '</a></p>'
            . ($footnote !== '' ? '<div style="margin:0 0 20px;padding:16px 18px;border-radius:12px;background:#f5f7fc;border-left:4px solid #6243d6;font-size:13px;color:#4a5578;line-height:1.6">' . htmlspecialchars($footnote, ENT_QUOTES, 'UTF-8') . '</div>' : '')
            . '</div>'
            . '<div style="padding:20px 34px 30px;border-top:1px solid #eef1f8">'
            . '<p style="margin:0;font-size:12px;color:#98a2b3">Empowering Ideas, Building Futures</p>'
            . '<p style="margin:6px 0 0;font-size:12px;color:#98a2b3">&copy; ' . $year . ' Udyam Ventures. All rights reserved.</p>'
            . '</div>'
            . '</div>'
            . '</div>';
    }

    private function absoluteUrl(string $path): string
    {
        return App::url() . '/' . ltrim($path, '/');
    }
}
