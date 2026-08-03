<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\DashboardService;

class DashboardController extends AdminController
{
    public function __construct(private readonly DashboardService $dashboard)
    {
        parent::__construct();
    }

    public function index(): void
    {
        $this->render(
            'dashboard/index',
            [
                'title' => 'Dashboard',
                'stats' => $this->dashboard->summary(),
                'business' => $this->dashboard->businessInsights(),
                'revenueTrend' => $this->dashboard->revenueTrend(),
                'subscriptionMix' => $this->dashboard->subscriptionMix(),
                'applicationPipeline' => $this->dashboard->applicationPipeline(),
                'latestCustomers' => $this->dashboard->latestCustomers(),
                'distribution' => $this->dashboard->moduleDistribution(),
                'activities' => $this->dashboard->recentActivity(),
                'systemStatus' => $this->dashboard->systemStatus(),
            ]
        );
    }
}
