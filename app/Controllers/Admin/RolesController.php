<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\Request;use App\Core\Session;use App\Models\Role;
final class RolesController extends AdminController
{
    public function __construct(private readonly Role $roles,private readonly Request $request){parent::__construct();}
    public function index():void{$this->render('roles/index',['title'=>'Roles & Permissions','roles'=>$this->roles->all()]);}
    public function create():void{$this->form([],'create');}
    public function edit(int$id):void{$role=$this->roles->find($id);if($role===null)$this->abort404();$this->form($role,'edit');}
    public function store():void{$this->csrf();[$data,$errors]=$this->validated();if($errors!==[])$this->fail('/admin/roles/create',$data,$errors);$this->roles->create($data);$this->redirectSuccess('/admin/roles','Role created.');}
    public function update(int$id):void{$this->csrf();if($this->roles->find($id)===null)$this->abort404();[$data,$errors]=$this->validated($id);if($errors!==[])$this->fail('/admin/roles/'.$id.'/edit',$data,$errors);$this->roles->update($id,$data);$this->redirectSuccess('/admin/roles','Role updated.');}
    public function destroy(int$id):void{$this->csrf();$role=$this->roles->find($id);if($role===null)$this->abort404();if((int)$role['is_system']===1)$this->redirectError('/admin/roles','System roles cannot be deleted; they can be edited.');$this->roles->deleteRole($id);$this->redirectSuccess('/admin/roles','Role deleted.');}
    private function form(array$record,string$mode):void{$this->render('roles/form',['title'=>($mode==='edit'?'Edit':'Create').' Role','record'=>$record,'mode'=>$mode,'permissionModules'=>require CONFIG_PATH.'/permissions.php']);}
    private function validated(?int$ignoreId=null):array{$name=$this->request->string('name');$slug=strtolower(trim((string)preg_replace('/[^a-z0-9]+/i','-',$this->request->string('slug')?:$name),'-'));$submitted=$this->request->input('permissions',[]);$permissions=[];if(is_array($submitted)){foreach((require CONFIG_PATH.'/permissions.php')as$module=>$label){$level=(string)($submitted[$module]??'');if($level==='view')$permissions[$module]=['view'];elseif($level==='manage')$permissions[$module]=['view','manage'];}}$data=['name'=>$name,'slug'=>$slug,'description'=>$this->request->string('description'),'permissions'=>json_encode($permissions,JSON_UNESCAPED_SLASHES)];$errors=[];if($name==='')$errors['name'][]='Role name is required.';if($slug==='')$errors['slug'][]='Role slug is required.';elseif($this->roles->slugExists($slug,$ignoreId))$errors['slug'][]='That role slug already exists.';return[$data,$errors];}
    private function fail(string$path,array$old,array$errors):never{Session::set('old',$old);Session::set('errors',$errors);$this->redirect($path);}
    private function csrf():void{if(!csrf_validate()){http_response_code(419);exit('Your session expired.');}}
}
