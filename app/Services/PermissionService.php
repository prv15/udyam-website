<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Models\Role;

final class PermissionService
{
    public function __construct(private readonly Role $roles) {}

    public function can(array $user,string $module,string $action='view'): bool
    {
        if(($user['user_type']??'')==='admin'){return true;} if(($user['user_type']??'')!=='staff'){return false;} if($module==='account'){return true;}
        foreach($this->roles->forUser((int)$user['id'])as$role){$permissions=json_decode((string)$role['permissions'],true)?:[];$actions=$permissions[$module]??[];if(in_array('*',$actions,true)||in_array($action,$actions,true)||($action==='view'&&in_array('manage',$actions,true))){return true;}}
        return false;
    }

    public function authorizeCurrentRequest(): void
    {
        $user=Session::get('user'); if(!is_array($user)||($user['user_type']??'')==='admin'){return;}
        $path=(string)(parse_url($_SERVER['REQUEST_URI']??'/admin',PHP_URL_PATH)?:'/admin');$module=$this->moduleForPath($path);$action=$this->actionForRequest($path,$_SERVER['REQUEST_METHOD']??'GET');
        if(!$this->can($user,$module,$action)){http_response_code(403);exit('403 - Your staff role does not permit this action.');}
    }

    public function filterMenu(array $menu,array $user): array
    {
        if(($user['user_type']??'')==='admin'){return$menu;}$filtered=[];
        foreach($menu as$heading=>$items){foreach($items as$item){if(($item['enabled']??true)===false){continue;}$children=[];foreach(($item['children']??[])as$child){if($this->can($user,$this->moduleForPath((string)$child['route']),'view')){$children[]=$child;}}$allowed=$this->can($user,$this->moduleForPath((string)$item['route']),'view');if($children!==[]){$item['children']=$children;$allowed=true;}if($allowed){$filtered[$heading][]=$item;}}}
        return$filtered;
    }

    public function canPath(array $user,string $path,string $action='view'): bool
    {
        return $this->can($user,$this->moduleForPath($path),$action);
    }

    public function landingPath(array $user): string
    {
        if(($user['user_type']??'')==='admin')return'/admin/dashboard';
        foreach(['/admin/dashboard','/admin/staff','/admin/digital-business-cards','/admin/pages','/admin/media','/admin/customers','/admin/contact-messages']as$path){if($this->canPath($user,$path,'view'))return$path;}
        return'/admin/profile';
    }

    private function actionForRequest(string $path,string $method): string
    {
        if(strtoupper($method)!=='GET'||preg_match('#/(?:create|edit|export)(?:/|$)#',$path)){return'manage';}return'view';
    }

    private function moduleForPath(string $path): string
    {
        $path=strtolower((string)(parse_url($path,PHP_URL_PATH)?:$path));
        if(str_contains($path,'/admin/profile')||str_contains($path,'/admin/change-password'))return'account';
        if(str_starts_with($path,'/admin/dashboard')||$path==='/admin')return'dashboard';
        if(str_starts_with($path,'/admin/staff'))return'staff';
        if(str_starts_with($path,'/admin/digital-business-cards'))return'cards';
        if(str_starts_with($path,'/admin/media'))return'media';
        if(str_starts_with($path,'/admin/projects'))return'projects';
        if(str_starts_with($path,'/admin/tasks'))return'tasks';
        if(preg_match('#/admin/(customers|applications|documents|subscription|billing|notification)#',$path))return'partners';
        if(preg_match('#/admin/(contact-messages|newsletter)#',$path))return'enquiries';
        if(preg_match('#/admin/(pages|services|focus-areas|blog|tenders|testimonials|faqs|team|partners|menu)#',$path))return'website';
        return'administration';
    }
}
