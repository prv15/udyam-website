<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Services\GlobalSearchService;

final class GlobalSearchController extends AdminController
{
    public function __construct(
        private readonly GlobalSearchService $search,
        private readonly Request $request
    ) {
        parent::__construct();
    }

    public function index(): never
    {
        $query = mb_substr($this->request->string('q'), 0, 120);

        $this->json([
            'query' => $query,
            'results' => $this->search->search($query),
        ]);
    }
}
