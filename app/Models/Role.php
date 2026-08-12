<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Role extends Model
{
    protected string $table='roles';

    public function all(): array { return $this->db->query('SELECT r.*,(SELECT COUNT(*) FROM user_roles ur WHERE ur.role_id=r.id) AS users_count FROM roles r ORDER BY r.name')->fetchAll(PDO::FETCH_ASSOC); }
    public function slugExists(string $slug,?int $ignoreId=null): bool { $sql='SELECT 1 FROM roles WHERE slug=:slug';$params=['slug'=>$slug];if($ignoreId!==null){$sql.=' AND id<>:id';$params['id']=$ignoreId;}$statement=$this->db->prepare($sql.' LIMIT 1');$statement->execute($params);return(bool)$statement->fetchColumn(); }
    public function forUser(int $userId): array { $statement=$this->db->prepare('SELECT r.* FROM roles r JOIN user_roles ur ON ur.role_id=r.id WHERE ur.user_id=:id ORDER BY r.name');$statement->execute(['id'=>$userId]);return$statement->fetchAll(PDO::FETCH_ASSOC); }
    public function roleIdsForUser(int $userId): array { return array_map('intval',array_column($this->forUser($userId),'id')); }
    public function syncUser(int $userId,array $roleIds): void { $this->db->prepare('DELETE FROM user_roles WHERE user_id=:id')->execute(['id'=>$userId]);$insert=$this->db->prepare('INSERT IGNORE INTO user_roles (user_id,role_id) VALUES (:user,:role)');foreach(array_unique(array_map('intval',$roleIds))as$roleId){if($roleId>0){$insert->execute(['user'=>$userId,'role'=>$roleId]);}} }
    public function deleteRole(int $id): bool { return $this->delete($id); }
}
