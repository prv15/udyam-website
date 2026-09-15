<?php

declare(strict_types=1);

namespace App\Controllers\Website;

use App\Core\Controller;
use App\Core\Request;
use App\Models\DigitalBusinessCard;
use App\Services\DigitalCardService;

final class DigitalBusinessCardController extends Controller
{
    public function __construct(private readonly DigitalBusinessCard $cards, private readonly DigitalCardService $service, private readonly Request $request) {}

    public function show(string $slug): void
    {
        $card = $this->cards->findBySlug($slug); if ($card === null) { $this->abort404(); }
        $visitor = $this->service->visitorHash($this->request->ip(), $this->request->userAgent());
        $this->cards->recordEvent((int) $card['id'], 'view', $visitor);
        if ($this->request->string('source') === 'qr') { $this->cards->recordEvent((int) $card['id'], 'qr_scan', $visitor); }
        $cardUrl = $this->service->publicUrl($card);
        $vcardUrl = url('/card/' . rawurlencode($slug) . '/contact.vcf');
        require VIEW_PATH . '/website/digital-card.php';
    }

    public function vcard(string $slug): never
    {
        $card = $this->cards->findBySlug($slug); if ($card === null) { $this->abort404(); }
        $this->cards->recordEvent((int) $card['id'], 'save_contact', $this->service->visitorHash($this->request->ip(), $this->request->userAgent()));
        header('Content-Type: text/vcard; charset=UTF-8'); header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-z0-9-]/i', '-', strtolower($card['display_name'])) . '.vcf"'); echo $this->service->vcard($card); exit;
    }

    public function event(string $slug): never
    {
        $card = $this->cards->findBySlug($slug); if ($card === null) { $this->json(['ok' => false], 404); }
        $event = $this->request->string('event'); $this->cards->recordEvent((int) $card['id'], $event, $this->service->visitorHash($this->request->ip(), $this->request->userAgent())); $this->json(['ok' => true]);
    }
}
