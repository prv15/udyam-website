<?php

declare(strict_types=1);

namespace App\Controllers\Website;

use App\Core\Controller;
use App\Core\Request;
use App\Models\AdminRecord;
use PHPMailer\PHPMailer\PHPMailer;
use Throwable;

final class ConsultationController extends Controller
{
    public function __construct(
        private readonly Request $request,
        private readonly AdminRecord $records
    ) {
    }

    public function store(): never
    {
        if (!csrf_validate()) {
            $this->json(['ok' => false, 'message' => 'Your session expired. Please refresh and try again.'], 419);
        }

        if (trim((string) $this->request->post('website', '')) !== '') {
            $this->json(['ok' => true, 'message' => 'Thank you. We will contact you shortly.']);
        }

        $lastSubmission = (int) ($_SESSION['_consultation_submitted_at'] ?? 0);
        if ($lastSubmission > time() - 20) {
            $this->json(['ok' => false, 'message' => 'Please wait a moment before submitting again.'], 429);
        }

        $name = trim((string) $this->request->post('name', ''));
        $email = strtolower(trim((string) $this->request->post('email', '')));
        $phone = trim((string) $this->request->post('phone', ''));
        $organization = trim((string) $this->request->post('organization', ''));
        $service = trim((string) $this->request->post('service', ''));
        $budget = trim((string) $this->request->post('budget', ''));
        $preferredContact = trim((string) $this->request->post('preferred_contact', 'Email'));
        $message = trim((string) $this->request->post('message', ''));

        if (
            mb_strlen($name) < 2 || mb_strlen($name) > 100
            || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190
            || mb_strlen($phone) < 7 || mb_strlen($phone) > 25
            || mb_strlen($organization) > 150 || mb_strlen($service) > 100
            || mb_strlen($budget) > 80 || mb_strlen($message) < 10 || mb_strlen($message) > 3000
        ) {
            $this->json(['ok' => false, 'message' => 'Please check the highlighted information and try again.'], 422);
        }

        $details = [
            'title' => $name,
            'email' => $email,
            'phone' => $phone,
            'subject' => 'Consultation request: ' . ($service !== '' ? $service : 'General enquiry'),
            'message' => implode("\n", array_filter([
                $message,
                'Organization: ' . ($organization !== '' ? $organization : 'Not provided'),
                'Service: ' . ($service !== '' ? $service : 'Not selected'),
                'Indicative budget: ' . ($budget !== '' ? $budget : 'Not selected'),
                'Preferred contact: ' . $preferredContact,
            ])),
            'organization' => $organization,
            'service' => $service,
            'budget' => $budget,
            'preferred_contact' => $preferredContact,
            'source' => 'Website consultation modal',
        ];

        $this->records->create([
            'module' => 'contact-messages',
            'title' => $name,
            'slug' => 'consultation-' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)),
            'status' => 'active',
            'sort_order' => 0,
            'data' => json_encode($details, JSON_THROW_ON_ERROR),
        ]);
        $_SESSION['_consultation_submitted_at'] = time();

        try {
            $this->sendEmail($details);
        } catch (Throwable $exception) {
            error_log('Consultation email failed: ' . $exception->getMessage());
            $this->json([
                'ok' => false,
                'message' => 'Your request was saved, but email delivery is temporarily unavailable. Our team can still review it in the admin panel.',
            ], 503);
        }

        $this->json(['ok' => true, 'message' => 'Thank you! Your consultation request has been received.']);
    }

    private function sendEmail(array $details): void
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = (string) ($_ENV['SMTP_HOST'] ?? 'smtp.gmail.com');
        $mail->SMTPAuth = true;
        $mail->Username = (string) ($_ENV['SMTP_USERNAME'] ?? '');
        $mail->Password = (string) ($_ENV['SMTP_PASSWORD'] ?? '');
        $mail->Port = (int) ($_ENV['SMTP_PORT'] ?? 465);
        $mail->SMTPSecure = (string) ($_ENV['SMTP_ENCRYPTION'] ?? PHPMailer::ENCRYPTION_SMTPS);
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($mail->Username, 'Udyam Ventures Website');
        $mail->addAddress((string) ($_ENV['CONSULTATION_TO_EMAIL'] ?? 'projects@udyamventures.com'), 'Udyam Ventures Projects');
        $mail->addReplyTo($details['email'], $details['title']);
        $mail->isHTML(true);
        $mail->Subject = 'New consultation request — ' . ($details['service'] ?: 'General enquiry');

        $escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        $rows = [
            'Name' => $details['title'],
            'Email' => $details['email'],
            'Phone' => $details['phone'],
            'Organization' => $details['organization'] ?: 'Not provided',
            'Service' => $details['service'] ?: 'Not selected',
            'Indicative budget' => $details['budget'] ?: 'Not selected',
            'Preferred contact' => $details['preferred_contact'],
        ];
        $rowHtml = '';
        foreach ($rows as $label => $value) {
            $rowHtml .= '<tr><td style="padding:10px 0;color:#73809d;font-size:13px;width:155px">' . $escape($label) . '</td><td style="padding:10px 0;color:#101e46;font-size:14px;font-weight:600">' . $escape((string) $value) . '</td></tr>';
        }
        $mail->Body = '<div style="margin:0;background:#f2f5fb;padding:34px 14px;font-family:Arial,sans-serif"><div style="max-width:650px;margin:auto;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 18px 48px rgba(7,24,75,.12)"><div style="padding:30px 34px;background:linear-gradient(135deg,#071b51,#3b2abb);color:#fff"><div style="font-size:12px;letter-spacing:2px;color:#f0b743;text-transform:uppercase">Udyam Ventures</div><h1 style="margin:10px 0 5px;font-family:Georgia,serif;font-size:30px">New consultation request</h1><p style="margin:0;color:#dce4ff;font-size:14px">A prospective client submitted the website consultation form.</p></div><div style="padding:26px 34px"><table style="width:100%;border-collapse:collapse">' . $rowHtml . '</table><div style="margin-top:20px;padding:20px;border-radius:14px;background:#f5f7fc;border-left:4px solid #6243d6"><div style="margin-bottom:8px;color:#73809d;font-size:12px;text-transform:uppercase;letter-spacing:1px">Project requirement</div><div style="color:#1d2947;font-size:14px;line-height:1.7">' . nl2br($escape($details['message'])) . '</div></div><p style="margin:24px 0 0;color:#8791a8;font-size:12px">Reply directly to this email to contact ' . $escape($details['title']) . '.</p></div></div></div>';
        $mail->AltBody = "New consultation request\n\n" . strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $details['message']));
        $mail->send();
    }
}
