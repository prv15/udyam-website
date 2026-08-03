<?php

declare(strict_types=1);

namespace App\Controllers\Website;

use App\Core\Controller;
use App\Repositories\CustomerPortalRepository;
use App\Services\InvoicePdfService;

final class InvoiceController extends Controller
{
    public function __construct(
        private readonly CustomerPortalRepository $portal,
        private readonly InvoicePdfService $pdf
    ){
    }

    public function show(string $token): void
    {
        $invoice=$this->portal->publicInvoice($token);
        if(!$invoice)$this->abort404();
        require VIEW_PATH.'/website/invoice-public.php';
    }

    public function pdf(string $token): never
    {
        $invoice=$this->portal->publicInvoice($token);
        if(!$invoice)$this->abort404();
        $filename='Udyam-Invoice-'.preg_replace('/[^A-Za-z0-9-]/','-',(string)$invoice['invoice_number']).'.pdf';
        $content=$this->pdf->render($invoice);
        header('Content-Type: application/pdf');
        header('Content-Length: '.strlen($content));
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Cache-Control: private, no-store, max-age=0');
        echo $content;
        exit;
    }
}
