<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class DigitalBusinessCard extends Model
{
    protected string $table='digital_business_cards';

    public function findBySlug(string $slug,bool $onlyEnabled=true): ?array
    {
        $sql='SELECT c.*,u.employee_code,u.first_name,u.last_name,u.display_name,u.designation,u.department,u.email,u.mobile,u.alternate_mobile,u.office_extension,u.office_location,u.address,u.bio,u.linkedin_url,u.website_url,u.whatsapp_number,m.folder AS photo_folder,m.filename AS photo_filename,m.alt_text AS photo_alt FROM digital_business_cards c JOIN users u ON u.id=c.user_id AND u.user_type=\'staff\' AND u.deleted_at IS NULL LEFT JOIN media m ON m.id=u.profile_photo_media_id AND m.deleted_at IS NULL WHERE c.public_slug=:slug';if($onlyEnabled){$sql.=" AND c.status='enabled' AND u.employment_status NOT IN ('resigned','suspended')";}$statement=$this->db->prepare($sql.' LIMIT 1');$statement->execute(['slug'=>$slug]);return$statement->fetch(PDO::FETCH_ASSOC)?:null;
    }

    public function paginate(int $page,int $perPage,string $search='',string $status=''): array
    {
        $where=["u.deleted_at IS NULL","u.user_type='staff'"];$params=[];if($search!==''){$where[]='(u.display_name LIKE :search OR u.employee_code LIKE :search OR u.designation LIKE :search OR c.public_slug LIKE :search)';$params['search']='%'.$search.'%';}if($status!==''){$where[]='c.status=:status';$params['status']=$status;}$statement=$this->db->prepare('SELECT c.*,u.display_name,u.employee_code,u.designation,u.department,m.folder AS photo_folder,m.filename AS photo_filename FROM digital_business_cards c JOIN users u ON u.id=c.user_id LEFT JOIN media m ON m.id=u.profile_photo_media_id AND m.deleted_at IS NULL WHERE '.implode(' AND ',$where).' ORDER BY c.created_at DESC LIMIT :limit OFFSET :offset');foreach($params as$key=>$value){$statement->bindValue(':'.$key,$value);}$statement->bindValue(':limit',$perPage,PDO::PARAM_INT);$statement->bindValue(':offset',($page-1)*$perPage,PDO::PARAM_INT);$statement->execute();return$statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function total(string $search='',string $status=''): int
    {
        $where=["u.deleted_at IS NULL","u.user_type='staff'"];$params=[];if($search!==''){$where[]='(u.display_name LIKE :search OR u.employee_code LIKE :search OR u.designation LIKE :search OR c.public_slug LIKE :search)';$params['search']='%'.$search.'%';}if($status!==''){$where[]='c.status=:status';$params['status']=$status;}$statement=$this->db->prepare('SELECT COUNT(*) FROM digital_business_cards c JOIN users u ON u.id=c.user_id WHERE '.implode(' AND ',$where));$statement->execute($params);return(int)$statement->fetchColumn();
    }

    public function analytics(int $cardId): array { $statement=$this->db->prepare('SELECT event_type,COUNT(*) AS total FROM digital_card_events WHERE card_id=:id GROUP BY event_type');$statement->execute(['id'=>$cardId]);$data=[];foreach($statement->fetchAll(PDO::FETCH_ASSOC)as$row){$data[$row['event_type']]=(int)$row['total'];}return$data; }
    public function recordEvent(int $cardId,string $eventType,?string $visitorHash=null): void
    {
        $allowed=['view','qr_scan','save_contact','whatsapp','call','email','website','linkedin','share','copy_link','wallet'];if(!in_array($eventType,$allowed,true)){return;}$this->db->prepare('INSERT INTO digital_card_events (card_id,event_type,visitor_hash) VALUES (:card,:event,:visitor)')->execute(['card'=>$cardId,'event'=>$eventType,'visitor'=>$visitorHash]);if($eventType==='view'){$q=$this->db->prepare("SELECT COUNT(*) FROM digital_card_events WHERE card_id=:card AND event_type='view' AND visitor_hash=:visitor");$q->execute(['card'=>$cardId,'visitor'=>$visitorHash]);$this->db->prepare('UPDATE digital_business_cards SET total_views=total_views+1,unique_views=unique_views+:unique,last_viewed_at=NOW() WHERE id=:id')->execute(['unique'=>(int)$q->fetchColumn()===1?1:0,'id'=>$cardId]);}elseif($eventType==='qr_scan'){$this->db->prepare('UPDATE digital_business_cards SET qr_scans=qr_scans+1 WHERE id=:id')->execute(['id'=>$cardId]);}
    }
    public function slugExists(string $slug,?int $ignoreCardId=null): bool { $sql='SELECT 1 FROM digital_business_cards WHERE public_slug=:slug';$params=['slug'=>$slug];if($ignoreCardId!==null){$sql.=' AND id<>:id';$params['id']=$ignoreCardId;}$statement=$this->db->prepare($sql.' LIMIT 1');$statement->execute($params);return(bool)$statement->fetchColumn(); }
}
