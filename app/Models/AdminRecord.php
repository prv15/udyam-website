<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class AdminRecord extends Model
{
    protected string $table = 'admin_records';

    public function tenderTypes(): array
    {
        $statement = $this->db->query("SELECT DISTINCT JSON_UNQUOTE(JSON_EXTRACT(data, '$.type')) AS type FROM admin_records WHERE module = 'tenders' AND deleted_at IS NULL ORDER BY type");
        return array_values(array_filter($statement->fetchAll(PDO::FETCH_COLUMN),
            static fn ($type): bool => is_string($type) && trim($type) !== '' && $type !== 'null'));
    }

    public function paginate(string $module, int $page, int $perPage, string $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $sql = 'SELECT * FROM admin_records WHERE module = :module AND deleted_at IS NULL';
        $params = ['module' => $module];
        if ($search !== '') {
            $sql .= ' AND (title LIKE :title_search OR data LIKE :data_search)';
            $term = '%' . $search . '%';
            $params['title_search'] = $term;
            $params['data_search'] = $term;
        }
        $sql .= ' ORDER BY sort_order ASC, created_at DESC LIMIT :limit OFFSET :offset';
        $statement = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value);
        }
        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return array_map([$this, 'hydrate'], $statement->fetchAll());
    }

    public function total(string $module, string $search = ''): int
    {
        $sql = 'SELECT COUNT(*) FROM admin_records WHERE module = :module AND deleted_at IS NULL';
        $params = ['module' => $module];
        if ($search !== '') {
            $sql .= ' AND (title LIKE :title_search OR data LIKE :data_search)';
            $term = '%' . $search . '%';
            $params['title_search'] = $term;
            $params['data_search'] = $term;
        }
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        return (int) $statement->fetchColumn();
    }

    public function paginateTenders(int $page, int $perPage, array $filters): array
    {
        [$where,$params]=$this->tenderConditions($filters);$offset=($page-1)*$perPage;$sort=$filters['sort']??'latest';
        $date="COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data,'$.closing_date')),'')";
        $order=in_array($sort,['latest','deadline','ongoing'],true)
            ? "{$date} ASC, created_at DESC"
            : "{$date} DESC, created_at DESC";
        $statement=$this->db->prepare("SELECT * FROM admin_records WHERE {$where} ORDER BY {$order} LIMIT :limit OFFSET :offset");
        foreach($params as $key=>$value)$statement->bindValue(':'.$key,$value);$statement->bindValue(':limit',$perPage,PDO::PARAM_INT);$statement->bindValue(':offset',$offset,PDO::PARAM_INT);$statement->execute();
        return array_map([$this,'hydrate'],$statement->fetchAll());
    }

    public function tendersTotal(array $filters): int
    {
        [$where,$params]=$this->tenderConditions($filters);$statement=$this->db->prepare("SELECT COUNT(*) FROM admin_records WHERE {$where}");$statement->execute($params);return (int)$statement->fetchColumn();
    }

    public function tenderFilterOptions(): array
    {
        $statement=$this->db->query("SELECT JSON_UNQUOTE(JSON_EXTRACT(data,'$.region')) AS region, JSON_UNQUOTE(JSON_EXTRACT(data,'$.invited_by')) AS invited_by FROM admin_records WHERE module='tenders' AND deleted_at IS NULL");$regions=[];$organisations=[];
        foreach($statement->fetchAll() as $row){$region=trim((string)($row['region']??''));$organisation=trim((string)($row['invited_by']??''));if($region!=='')$regions[$region]=true;if($organisation!=='')$organisations[$organisation]=true;}ksort($regions,SORT_NATURAL|SORT_FLAG_CASE);ksort($organisations,SORT_NATURAL|SORT_FLAG_CASE);
        return ['regions'=>array_keys($regions),'organisations'=>array_keys($organisations)];
    }

    public function paginatePartners(int $page, int $perPage, array $filters): array
    {
        [$where,$params]=$this->partnerConditions($filters);$offset=($page-1)*$perPage;
        $sql="SELECT ar.*,u.first_name,u.last_name,u.email user_email,u.status user_status,
                     cp.company_name,cp.mobile,cp.state,cp.annual_turnover_range,cp.contact_person
              FROM admin_records ar
              LEFT JOIN customer_profiles cp ON cp.customer_record_id=ar.id
              LEFT JOIN users u ON u.id=cp.user_id AND u.deleted_at IS NULL
              WHERE {$where} ORDER BY COALESCE(NULLIF(cp.company_name,''),ar.title),ar.created_at DESC
              LIMIT :limit OFFSET :offset";
        $statement=$this->db->prepare($sql);
        foreach($params as $key=>$value)$statement->bindValue(':'.$key,$value);
        $statement->bindValue(':limit',$perPage,PDO::PARAM_INT);$statement->bindValue(':offset',$offset,PDO::PARAM_INT);$statement->execute();
        return array_map([$this,'hydrate'],$statement->fetchAll());
    }

    public function partnersTotal(array $filters): int
    {
        [$where,$params]=$this->partnerConditions($filters);
        $statement=$this->db->prepare("SELECT COUNT(*) FROM admin_records ar LEFT JOIN customer_profiles cp ON cp.customer_record_id=ar.id LEFT JOIN users u ON u.id=cp.user_id AND u.deleted_at IS NULL WHERE {$where}");
        $statement->execute($params);return (int)$statement->fetchColumn();
    }

    public function partnerFilterOptions(): array
    {
        $states=$this->db->query("SELECT DISTINCT state FROM customer_profiles WHERE state IS NOT NULL AND TRIM(state)<>'' ORDER BY state")->fetchAll(PDO::FETCH_COLUMN);
        return ['states'=>$states,'turnovers'=>[
            'under_25_lakh'=>'Under ₹25 lakh','25_lakh_1_crore'=>'₹25 lakh – ₹1 crore','1_5_crore'=>'₹1 – ₹5 crore',
            '5_25_crore'=>'₹5 – ₹25 crore','25_100_crore'=>'₹25 – ₹100 crore','above_100_crore'=>'Above ₹100 crore','not_disclosed'=>'Not disclosed',
        ]];
    }

    private function partnerConditions(array $filters): array
    {
        $where="ar.module='customers' AND ar.deleted_at IS NULL";$params=[];
        if(($search=trim((string)($filters['search']??'')))!==''){
            $where.=" AND (ar.title LIKE :search OR ar.data LIKE :data_search OR cp.company_name LIKE :company_search OR cp.mobile LIKE :mobile_search OR u.email LIKE :email_search OR CONCAT_WS(' ',u.first_name,u.last_name) LIKE :name_search)";
            $term='%'.$search.'%';foreach(['search','data_search','company_search','mobile_search','email_search','name_search'] as $key)$params[$key]=$term;
        }
        if(($state=trim((string)($filters['state']??'')))!==''){$where.=' AND cp.state=:state';$params['state']=$state;}
        if(($turnover=trim((string)($filters['turnover']??'')))!==''){$where.=' AND cp.annual_turnover_range=:turnover';$params['turnover']=$turnover;}
        return [$where,$params];
    }

    private function tenderConditions(array $filters): array
    {
        $where="module='tenders' AND deleted_at IS NULL";$params=[];
        if(($search=trim((string)($filters['search']??'')))!==''){$where.=' AND (title LIKE :title_search OR data LIKE :data_search)';$term='%'.$search.'%';$params['title_search']=$term;$params['data_search']=$term;}
        if(($region=trim((string)($filters['region']??'')))!==''){$where.=" AND JSON_UNQUOTE(JSON_EXTRACT(data,'$.region'))=:region";$params['region']=$region;}
        if(($invitedBy=trim((string)($filters['invited_by']??'')))!==''){$where.=" AND JSON_UNQUOTE(JSON_EXTRACT(data,'$.invited_by'))=:invited_by";$params['invited_by']=$invitedBy;}
        $date="COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data,'$.closing_date')),'')";$sort=$filters['sort']??'latest';
        if($sort==='latest')$where.=" AND status IN ('active','published') AND {$date}<>'' AND {$date}>=CURDATE()";
        if($sort==='deadline')$where.=" AND {$date}<>''";
        if($sort==='expired')$where.=" AND {$date}<>'' AND (status='expired' OR {$date}<CURDATE())";
        if($sort==='ongoing')$where.=" AND status IN ('active','published') AND ({$date}='' OR {$date}>=CURDATE())";
        return [$where,$params];
    }

    public function countByStatuses(string $module, array $statuses): int
    {
        if ($statuses === []) return 0;
        $placeholders = implode(',', array_fill(0, count($statuses), '?'));
        $statement = $this->db->prepare(
            "SELECT COUNT(*) FROM admin_records
             WHERE module = ? AND status IN ({$placeholders}) AND deleted_at IS NULL"
        );
        $statement->execute(array_merge([$module], array_values($statuses)));
        return (int) $statement->fetchColumn();
    }

    public function updateStatuses(string $module, array $fromStatuses, string $toStatus): void
    {
        if ($fromStatuses === []) return;
        $placeholders = implode(',', array_fill(0, count($fromStatuses), '?'));
        $statement = $this->db->prepare(
            "UPDATE admin_records SET status = ?, updated_at = CURRENT_TIMESTAMP
             WHERE module = ? AND status IN ({$placeholders}) AND deleted_at IS NULL"
        );
        $statement->execute(array_merge([$toStatus, $module], array_values($fromStatuses)));
    }

    public function findForModule(string $module, int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT * FROM admin_records WHERE id = :id AND module = :module AND deleted_at IS NULL LIMIT 1'
        );
        $statement->execute(['id' => $id, 'module' => $module]);
        $record = $statement->fetch();
        return $record ? $this->hydrate($record) : null;
    }

    public function publishedForModule(string $module, int $limit = 12): array
    {
        $statement = $this->db->prepare(
            "SELECT * FROM admin_records
             WHERE module = :module AND status IN ('published', 'active') AND deleted_at IS NULL
             ORDER BY sort_order, created_at DESC LIMIT :limit"
        );
        $statement->bindValue(':module', $module);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        return array_map([$this, 'hydrate'], $statement->fetchAll());
    }

    public function deleteManyForModule(string $module, array $ids): int
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0)));
        if ($ids === []) return 0;
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $statement = $this->db->prepare("UPDATE admin_records SET deleted_at = NOW() WHERE module = ? AND deleted_at IS NULL AND id IN ({$placeholders})");
        $statement->execute(array_merge([$module], $ids));
        return $statement->rowCount();
    }

    private function hydrate(array $record): array
    {
        $data = json_decode((string) $record['data'], true);
        return array_merge($record, is_array($data) ? $data : []);
    }
}
