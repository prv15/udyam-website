<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TestModel;

class HomeService extends Service
{
    private TestModel $model;

    public function __construct()
    {
        $this->model = new TestModel();
    }

    public function getHomeData(): array
    {
        return [
            'serverTime' => $this->model->getTime()
        ];
    }
}