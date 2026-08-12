<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/** Staff identities are canonical users; employee fields live on the users row. */
final class Staff extends Model
{
    protected string $table = 'users';

    public function paginate(int $page, int $perPage, string $search = '', string $status = '', string $department = ''): array
    {
        $where = ["u.deleted_at IS NULL", "u.user_type = 'staff'"]; $params = [];
        if ($search !== '') { $where[] = '(u.employee_code LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search OR u.display_name LIKE :search OR u.email LIKE :search OR u.mobile LIKE :search)'; $params['search'] = '%' . $search . '%'; }
        if ($status !== '') { $where[] = 'u.employment_status = :status'; $params['status'] = $status; }
        if ($department !== '') { $where[] = 'u.department = :department'; $params['department'] = $department; }
        $sql = 'SELECT u.*, u.employment_status AS status, u.status AS account_status, m.folder AS photo_folder, m.filename AS photo_filename, manager.display_name AS manager_name, c.public_slug, c.status AS card_status, c.total_views, c.qr_scans '
            . 'FROM users u LEFT JOIN media m ON m.id=u.profile_photo_media_id AND m.deleted_at IS NULL LEFT JOIN users manager ON manager.id=u.reporting_manager_id AND manager.deleted_at IS NULL '
            . 'LEFT JOIN digital_business_cards c ON c.user_id=u.id WHERE ' . implode(' AND ', $where) . ' ORDER BY u.created_at DESC LIMIT :limit OFFSET :offset';
        $statement=$this->db->prepare($sql); foreach($params as $key=>$value){$statement->bindValue(':'.$key,$value);} $statement->bindValue(':limit',$perPage,PDO::PARAM_INT); $statement->bindValue(':offset',max(0,($page-1)*$perPage),PDO::PARAM_INT); $statement->execute(); return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function total(string $search = '', string $status = '', string $department = ''): int
    {
        $where=["deleted_at IS NULL","user_type='staff'"]; $params=[];
        if($search!==''){$where[]='(employee_code LIKE :search OR first_name LIKE :search OR last_name LIKE :search OR display_name LIKE :search OR email LIKE :search OR mobile LIKE :search)';$params['search']='%'.$search.'%';}
        if($status!==''){$where[]='employment_status=:status';$params['status']=$status;} if($department!==''){$where[]='department=:department';$params['department']=$department;}
        $statement=$this->db->prepare('SELECT COUNT(*) FROM users WHERE '.implode(' AND ',$where));$statement->execute($params);return (int)$statement->fetchColumn();
    }

    public function profile(int $id): ?array
    {
        $statement=$this->db->prepare('SELECT u.*, u.employment_status AS status, u.status AS account_status, m.folder AS photo_folder, m.filename AS photo_filename, m.alt_text AS photo_alt, manager.display_name AS manager_name, c.id AS card_id, c.public_slug, c.theme, c.status AS card_status, c.show_mobile, c.show_whatsapp, c.show_email, c.show_address, c.show_linkedin, c.show_bio, c.total_views, c.unique_views, c.qr_scans, c.last_viewed_at, c.qr_version, c.qr_generated_at, c.created_at AS card_created_at, c.updated_at AS card_updated_at FROM users u LEFT JOIN media m ON m.id=u.profile_photo_media_id AND m.deleted_at IS NULL LEFT JOIN users manager ON manager.id=u.reporting_manager_id AND manager.deleted_at IS NULL LEFT JOIN digital_business_cards c ON c.user_id=u.id WHERE u.id=:id AND u.user_type=\'staff\' AND u.deleted_at IS NULL LIMIT 1');
        $statement->execute(['id'=>$id]); return $statement->fetch(PDO::FETCH_ASSOC)?:null;
    }

    public function managerOptions(?int $excludeId=null): array
    {
        $sql="SELECT id,display_name,designation FROM users WHERE deleted_at IS NULL AND user_type='staff' AND employment_status IN ('active','on_leave')";$params=[];if($excludeId!==null){$sql.=' AND id<>:id';$params['id']=$excludeId;}$statement=$this->db->prepare($sql.' ORDER BY display_name');$statement->execute($params);return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function departments(): array { return $this->db->query("SELECT DISTINCT department FROM users WHERE deleted_at IS NULL AND user_type='staff' AND department<>'' ORDER BY department")->fetchAll(PDO::FETCH_COLUMN); }
    public function mediaImages(): array { return $this->db->query("SELECT id,title,original_name,folder,filename FROM media WHERE deleted_at IS NULL AND mime_type LIKE 'image/%' ORDER BY created_at DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC); }

    public function existsBy(string $column,string $value,?int $ignoreId=null): bool
    {
        if(!in_array($column,['employee_code','email'],true)){throw new \InvalidArgumentException('Unsupported unique field.');}$sql="SELECT 1 FROM users WHERE {$column}=:value AND deleted_at IS NULL";$params=['value'=>$value];if($ignoreId!==null){$sql.=' AND id<>:id';$params['id']=$ignoreId;}$statement=$this->db->prepare($sql.' LIMIT 1');$statement->execute($params);return(bool)$statement->fetchColumn();
    }

    public function nextEmployeeCode(): string
    {
        $prefix='UV-'.date('Y').'-';$statement=$this->db->prepare("SELECT employee_code FROM users WHERE user_type='staff' AND employee_code LIKE :prefix ORDER BY id DESC LIMIT 1");$statement->execute(['prefix'=>$prefix.'%']);$last=(string)($statement->fetchColumn()?:'');return $prefix.str_pad((string)((int)substr($last,strlen($prefix))+1),4,'0',STR_PAD_LEFT);
    }

    public function activities(int $staffId,int $limit=50): array
    {
        $statement=$this->db->prepare('SELECT a.*,CONCAT_WS(" ",u.first_name,u.last_name) AS actor_name FROM user_activity_logs a LEFT JOIN users u ON u.id=a.actor_user_id WHERE a.staff_user_id=:id ORDER BY a.created_at DESC LIMIT :limit');$statement->bindValue(':id',$staffId,PDO::PARAM_INT);$statement->bindValue(':limit',$limit,PDO::PARAM_INT);$statement->execute();return$statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function log(int $staffId,?int $actorId,string $action,string $description,array $metadata=[]): void
    {
        $statement=$this->db->prepare('INSERT INTO user_activity_logs (staff_user_id,actor_user_id,action,description,metadata) VALUES (:staff,:actor,:action,:description,:metadata)');$statement->execute(['staff'=>$staffId,'actor'=>$actorId,'action'=>$action,'description'=>$description,'metadata'=>$metadata===[]?null:json_encode($metadata)]);
    }

    public function allForExport(string $search='',string $status='',string $department=''): array { return $this->paginate(1,100000,$search,$status,$department); }
}
