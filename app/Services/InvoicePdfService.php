<?php

declare(strict_types=1);

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

final class InvoicePdfService
{
    public function render(array $invoice): string
    {
        $options=new Options();
        $options->set('isRemoteEnabled',false);
        $options->set('defaultFont','DejaVu Sans');
        $dompdf=new Dompdf($options);
        $dompdf->loadHtml($this->html($invoice),'UTF-8');
        $dompdf->setPaper('A4','portrait');
        $dompdf->render();
        return $dompdf->output();
    }

    private function html(array $invoice): string
    {
        $e=static fn(mixed $value):string=>htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');
        $money=static fn(mixed $value):string=>'₹'.number_format((float)$value,2);
        $logoPath=BASE_PATH.'/uploads/media/original/home/udyam-ventures-logo-cropped.png';
        $logo=is_file($logoPath)?'data:image/png;base64,'.base64_encode((string)file_get_contents($logoPath)):'';
        $rows='';
        foreach($invoice['items'] as $item){
            $rows.='<tr><td>'.$e($item['description']).'</td><td>'.$e($item['quantity']).'</td><td>'.$money($item['unit_price']).'</td><td>'.$e($item['gst_rate']).'%</td><td class="right">'.$money($item['line_total']).'</td></tr>';
        }
        $address=array_filter([$invoice['address_line_1']??'',$invoice['address_line_2']??'',$invoice['city']??'',$invoice['state']??'',$invoice['postal_code']??'',$invoice['country']??'']);
        $status=strtoupper((string)$invoice['status']);
        return '<!doctype html><html><head><meta charset="utf-8"><style>
        @page{margin:28px}*{box-sizing:border-box}body{font-family:"DejaVu Sans",sans-serif;color:#14213d;font-size:11px;margin:0}
        .top{height:9px;background:#101f5b}.head{padding:28px 32px;background:#f6f8fd;border:1px solid #e4e8f1;border-top:0}
        .head table,.meta,.parties,.items,.totals{width:100%;border-collapse:collapse}.logo{width:210px;max-height:70px}.invoice-title{text-align:right}
        .invoice-title small{color:#d99d21;letter-spacing:2px;font-weight:bold}.invoice-title h1{font-size:28px;margin:5px 0;color:#101f5b}
        .pill{display:inline-block;padding:6px 10px;border-radius:20px;background:#eaf7ef;color:#16794b;font-size:9px;font-weight:bold}
        .meta{margin:20px 0}.meta td{padding:8px 0;border-bottom:1px solid #edf0f5}.meta td:nth-child(2){text-align:right;font-weight:bold}
        .parties{margin:28px 0}.parties td{width:50%;vertical-align:top;padding:18px;border:1px solid #e3e8f1}.parties small{color:#7b879e;text-transform:uppercase;letter-spacing:1px}.parties strong{display:block;font-size:14px;margin:8px 0}
        .items th{padding:12px 10px;background:#101f5b;color:white;text-align:left;font-size:9px}.items td{padding:13px 10px;border-bottom:1px solid #e7ebf2}.right{text-align:right!important}
        .totals{width:310px;margin:20px 0 0 auto}.totals td{padding:7px 5px}.totals tr.total td{border-top:2px solid #d99d21;padding-top:11px;font-size:14px;font-weight:bold}
        .note{margin-top:28px;padding:16px 18px;background:#f7f8fc;border-left:4px solid #d99d21;color:#58647a;line-height:1.6}
        .footer{margin-top:35px;padding-top:16px;border-top:1px solid #e3e8f1;color:#7b879e;text-align:center;font-size:9px}
        </style></head><body><div class="top"></div><section class="head"><table><tr><td>'.($logo?'<img class="logo" src="'.$logo.'">':'<strong>UDYAM VENTURES</strong>').'</td><td class="invoice-title"><small>TAX INVOICE</small><h1>'.$e($invoice['invoice_number']).'</h1><span class="pill">'.$e($status).'</span></td></tr></table>
        <table class="meta"><tr><td>Issue date</td><td>'.$e($invoice['issue_date']).'</td></tr><tr><td>Due date</td><td>'.$e($invoice['due_date']).'</td></tr><tr><td>Invoice type</td><td>'.$e(ucfirst((string)$invoice['invoice_type'])).'</td></tr></table></section>
        <table class="parties"><tr><td><small>Billed to</small><strong>'.$e(trim(($invoice['first_name']??'').' '.($invoice['last_name']??''))).'</strong><div>'.$e($invoice['company_name']??'').'<br>'.$e(implode(', ',$address)).'<br>'.$e($invoice['email']??'').'<br>GST: '.$e($invoice['gst_number']??'Not provided').'</div></td><td><small>Issued by</small><strong>Udyam Ventures</strong><div>1/22, Workspace, 2nd Floor<br>Asaf Ali Road, New Delhi - 110002<br>projects@udyamventures.com<br>udyamventures.com</div></td></tr></table>
        <table class="items"><thead><tr><th>Description</th><th>Qty</th><th>Taxable</th><th>GST</th><th class="right">Amount</th></tr></thead><tbody>'.$rows.'</tbody></table>
        <table class="totals"><tr><td>Taxable amount</td><td class="right">'.$money($invoice['taxable_amount']).'</td></tr><tr><td>CGST</td><td class="right">'.$money($invoice['cgst']).'</td></tr><tr><td>SGST</td><td class="right">'.$money($invoice['sgst']).'</td></tr><tr class="total"><td>Total</td><td class="right">'.$money($invoice['total_amount']).'</td></tr><tr><td>Paid</td><td class="right">'.$money($invoice['paid_amount']).'</td></tr></table>
        <div class="note"><strong>Notes</strong><br>'.$e($invoice['notes']??'Thank you for partnering with Udyam Ventures.').'</div>
        <div class="footer">This is a system-generated invoice. Udyam Ventures does not guarantee project sanction. Submission charges are separate from subscription fees.</div></body></html>';
    }
}
