<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\App;
use PHPMailer\PHPMailer\PHPMailer;

final class InvoiceMailer
{
    public function __construct(private readonly InvoicePdfService $pdf)
    {
    }

    public function send(array $invoice): void
    {
        $mail=new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host=(string)($_ENV['SMTP_HOST']??'');
        $mail->SMTPAuth=true;
        $mail->Username=(string)($_ENV['SMTP_USERNAME']??'');
        $mail->Password=(string)($_ENV['SMTP_PASSWORD']??'');
        $mail->Port=(int)($_ENV['SMTP_PORT']??465);
        $mail->SMTPSecure=(string)($_ENV['SMTP_ENCRYPTION']??'ssl');
        $mail->CharSet='UTF-8';
        $mail->setFrom($mail->Username,'Udyam Ventures');
        $mail->addAddress((string)$invoice['email'],trim(($invoice['first_name']??'').' '.($invoice['last_name']??'')));
        $mail->isHTML(true);
        $mail->Subject='Payment received - Invoice '.$invoice['invoice_number'];
        $link=App::url().'/invoice/'.rawurlencode((string)$invoice['public_token']);
        $safeLink=htmlspecialchars($link,ENT_QUOTES,'UTF-8');
        $mail->Body='<div style="background:#f4f6fb;padding:32px;font-family:Arial,sans-serif"><div style="max-width:640px;margin:auto;background:#fff;border-radius:18px;overflow:hidden;border:1px solid #e3e8f1"><div style="padding:28px 34px;background:#0b1d48;color:#fff"><small style="color:#e2ae35;letter-spacing:1px">UDYAM VENTURES</small><h1 style="font-size:24px;margin:8px 0">Payment received successfully</h1></div><div style="padding:32px 34px;color:#344054"><p>Hello '.htmlspecialchars((string)$invoice['first_name']).',</p><p style="line-height:1.7">Thank you. Your subscription payment of <strong>₹'.number_format((float)$invoice['total_amount'],2).'</strong> has been received against invoice <strong>'.htmlspecialchars((string)$invoice['invoice_number']).'</strong>.</p><p style="margin:28px 0"><a href="'.$safeLink.'" style="background:#315bd8;color:#fff;padding:13px 22px;border-radius:9px;text-decoration:none;font-weight:bold">Open invoice</a></p><p style="font-size:12px;color:#98a2b3">A PDF copy is also attached for your records.</p></div></div></div>';
        $mail->AltBody="Payment received for invoice {$invoice['invoice_number']}.\nOpen invoice: {$link}";
        $mail->addStringAttachment($this->pdf->render($invoice),'Udyam-Invoice-'.preg_replace('/[^A-Za-z0-9-]/','-',(string)$invoice['invoice_number']).'.pdf','base64','application/pdf');
        $mail->send();
    }
}
