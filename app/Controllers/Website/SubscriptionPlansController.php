<?php
declare(strict_types=1);
namespace App\Controllers\Website;
use App\Core\Controller;
use App\Repositories\CustomerPortalRepository;
use App\Services\HomeService;
final class SubscriptionPlansController extends Controller {
    public function __construct(private readonly CustomerPortalRepository $portal, private readonly HomeService $home) {}
    public function index(): void {
        $content=$this->home->content() ?? ['sections'=>[]];
        $this->view('website/subscription-plans', ['title'=>'Subscription Plans | Udyam Ventures','bodyClass'=>'udyam-home','plans'=>$this->portal->plans(),'header'=>$content['sections']['header']??[],'footer'=>$content['sections']['footer']??[]]);
    }
}
