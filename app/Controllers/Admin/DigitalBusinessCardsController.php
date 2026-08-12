<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Models\DigitalBusinessCard;
use App\Models\Staff;
use App\Services\DigitalCardService;
use App\Services\QrCodeService;

final class DigitalBusinessCardsController extends AdminController
{
    public function __construct(private readonly DigitalBusinessCard $cards, private readonly Staff $staff, private readonly DigitalCardService $cardService, private readonly QrCodeService $qr, private readonly Request $request) { parent::__construct(); }

    public function index(): void
    {
        $page = max(1, $this->request->integer('page', 1)); $search = $this->request->string('search'); $status = $this->request->string('status');
        $this->render('digital-cards/index', ['title' => 'Digital Business Cards', 'cards' => $this->cards->paginate($page, 20, $search, $status), 'total' => $this->cards->total($search, $status), 'page' => $page, 'search' => $search, 'status' => $status, 'cardService' => $this->cardService]);
    }

    public function edit(int $id): void
    {
        $record = $this->staff->profile($id); if ($record === null || !$record['card_id']) { $this->abort404(); }
        $this->render('digital-cards/edit', ['title' => 'Card Settings · ' . $record['display_name'], 'record' => $record, 'analytics' => $this->cards->analytics((int) $record['card_id']), 'cardUrl' => $this->cardService->publicUrl($record)]);
    }

    public function update(int $id): void
    {
        $this->csrf(); $record = $this->staff->profile($id); if ($record === null || !$record['card_id']) { $this->abort404(); }
        $slug = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', $this->request->string('public_slug')), '-'));
        if ($slug === '' || strlen($slug) < 4) { $this->redirectError('/admin/digital-business-cards/' . $id . '/edit', 'Use a card slug of at least four letters or numbers.'); }
        if ($this->cards->slugExists($slug, (int) $record['card_id'])) { $this->redirectError('/admin/digital-business-cards/' . $id . '/edit', 'That public card URL is already in use.'); }
        $changed = $slug !== $record['public_slug'];
        $updates = ['public_slug' => $slug, 'theme' => $this->request->string('theme', 'udyam-premium'), 'status' => $this->request->string('status') === 'disabled' ? 'disabled' : 'enabled', 'show_mobile' => $this->request->boolean('show_mobile') ? 1 : 0, 'show_whatsapp' => $this->request->boolean('show_whatsapp') ? 1 : 0, 'show_email' => $this->request->boolean('show_email') ? 1 : 0, 'show_address' => $this->request->boolean('show_address') ? 1 : 0, 'show_linkedin' => $this->request->boolean('show_linkedin') ? 1 : 0, 'show_bio' => $this->request->boolean('show_bio') ? 1 : 0];
        if ($changed) { $updates['qr_generated_at'] = date('Y-m-d H:i:s'); $updates['qr_version'] = (int) $record['qr_version'] + 1; }
        $this->cards->update((int) $record['card_id'], $updates);
        $this->staff->log($id, (int) (Session::get('user')['id'] ?? 0) ?: null, 'card_settings_updated', 'Digital Business Card settings updated.' . ($changed ? ' Public URL changed and QR metadata refreshed.' : ''));
        $this->redirectSuccess('/admin/digital-business-cards/' . $id . '/edit', 'Card settings updated.');
    }

    public function toggle(int $id): void
    {
        $this->csrf(); $record = $this->staff->profile($id); if ($record === null || !$record['card_id']) { $this->abort404(); }
        $new = $record['card_status'] === 'enabled' ? 'disabled' : 'enabled'; $this->cards->update((int) $record['card_id'], ['status' => $new]);
        $this->staff->log($id, (int) (Session::get('user')['id'] ?? 0) ?: null, 'card_status_changed', 'Digital Business Card ' . $new . '.');
        $this->redirectSuccess($this->request->string('return_to', '/admin/digital-business-cards'), 'Card ' . $new . '.');
    }

    public function regenerate(int $id): void
    {
        $this->csrf(); $record = $this->staff->profile($id); if ($record === null || !$record['card_id']) { $this->abort404(); }
        $this->cards->update((int) $record['card_id'], ['qr_version' => (int) ($record['qr_version'] ?? 1) + 1, 'qr_generated_at' => date('Y-m-d H:i:s')]);
        $this->staff->log($id, (int) (Session::get('user')['id'] ?? 0) ?: null, 'qr_regenerated', 'QR code metadata regenerated; the stable card URL was retained.');
        $this->redirectSuccess('/admin/digital-business-cards/' . $id . '/edit', 'QR code regenerated without changing the public URL.');
    }

    public function qr(int $id, string $format = 'png'): never
    {
        $record = $this->staff->profile($id); if ($record === null || !$record['card_id']) { $this->abort404(); }
        $format = $this->request->string('format', $format) === 'svg' ? 'svg' : 'png'; $download = $this->request->boolean('download');
        header('Content-Type: ' . ($format === 'svg' ? 'image/svg+xml' : 'image/png')); header('Cache-Control: private, max-age=3600');
        if ($download) { header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-z0-9-]/i', '-', $record['public_slug']) . '-qr.' . $format . '"'); }
        echo $this->qr->render($this->cardService->publicUrl($record, true), $format); exit;
    }

    public function vcard(int $id): never
    {
        $record = $this->staff->profile($id); if ($record === null || !$record['card_id']) { $this->abort404(); }
        header('Content-Type: text/vcard; charset=UTF-8'); header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-z0-9-]/i', '-', strtolower($record['display_name'])) . '.vcf"'); echo $this->cardService->vcard($record); exit;
    }

    private function csrf(): void { if (!csrf_validate()) { http_response_code(419); exit('Your session expired.'); } }
}
