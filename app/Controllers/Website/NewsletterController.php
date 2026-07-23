<?php

declare(strict_types=1);

namespace App\Controllers\Website;

use App\Core\Controller;
use App\Core\Request;
use App\Models\AdminRecord;

final class NewsletterController extends Controller
{
    public function __construct(
        private readonly Request $request,
        private readonly AdminRecord $records
    ) {
    }

    public function store(): never
    {
        if (!csrf_validate()) {
            $this->redirect('/home?subscription=expired#site-footer');
        }

        $email = strtolower(trim((string) $this->request->post('email', '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
            $this->redirect('/home?subscription=invalid#site-footer');
        }

        $slug = 'subscriber-' . hash('sha256', $email);
        $existing = $this->records->first([
            'module' => 'newsletter',
            'slug' => $slug,
            'deleted_at' => null,
        ]);

        $data = json_encode([
            'title' => '',
            'email' => $email,
            'source' => 'Website footer',
            'subscribed_at' => date('Y-m-d'),
        ], JSON_THROW_ON_ERROR);

        if ($existing) {
            $this->records->update((int) $existing['id'], [
                'status' => 'active',
                'data' => $data,
            ]);
        } else {
            $this->records->create([
                'module' => 'newsletter',
                'title' => $email,
                'slug' => $slug,
                'status' => 'active',
                'sort_order' => 0,
                'data' => $data,
            ]);
        }

        $this->redirect('/home?subscription=success#site-footer');
    }
}
