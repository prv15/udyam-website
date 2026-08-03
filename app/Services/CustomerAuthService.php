<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\App;
use App\Repositories\CustomerPortalRepository;
use PHPMailer\PHPMailer\PHPMailer;

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
            'Verify your Udyam customer account',
            'Verify your email',
            'Confirm your email address to activate every customer portal feature.',
            $this->absoluteUrl('/customer/verify-email?token=' . rawurlencode($token)),
            'Verify email'
        );
    }

    public function sendPasswordReset(int $userId, string $email, string $name): void
    {
        $token = bin2hex(random_bytes(32));
        $this->customers->createToken($userId, 'reset_password', $token, new \DateTimeImmutable('+60 minutes'));
        $this->sendMail(
            $email,
            $name,
            'Reset your Udyam customer password',
            'Reset your password',
            'This secure password reset link expires in 60 minutes.',
            $this->absoluteUrl('/customer/reset-password?token=' . rawurlencode($token)),
            'Reset password'
        );
    }

    private function sendMail(string $email, string $name, string $subject, string $heading, string $copy, string $link, string $button): void
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
        $mail->setFrom($mail->Username, 'Udyam Ventures');
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $safeLink = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
        $mail->Body = '<div style="background:#f4f7fb;padding:32px;font-family:Arial,sans-serif"><div style="max-width:620px;margin:auto;background:#fff;border-radius:18px;overflow:hidden"><div style="padding:28px 34px;background:#0b1d48;color:#fff"><small style="color:#e2ae35">UDYAM VENTURES</small><h1 style="font-size:25px">' . htmlspecialchars($heading) . '</h1></div><div style="padding:32px 34px;color:#344054"><p>Hello ' . htmlspecialchars($name) . ',</p><p style="line-height:1.7">' . htmlspecialchars($copy) . '</p><p style="margin:28px 0"><a href="' . $safeLink . '" style="background:#315bd8;color:#fff;padding:13px 22px;border-radius:9px;text-decoration:none;font-weight:bold">' . htmlspecialchars($button) . '</a></p><p style="font-size:12px;color:#98a2b3">If you did not request this, no action is required.</p></div></div></div>';
        $mail->AltBody = "{$heading}\n\n{$copy}\n{$link}";
        $mail->send();
    }

    private function absoluteUrl(string $path): string
    {
        return App::url() . '/' . ltrim($path, '/');
    }
}
