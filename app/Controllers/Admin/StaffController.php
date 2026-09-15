<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Models\DigitalBusinessCard;
use App\Models\Staff;
use App\Models\Role;
use App\Services\DigitalCardService;
use App\Services\Media\MediaService;
use PDO;
use Throwable;

final class StaffController extends AdminController
{
    public function __construct(
        private readonly Staff $staff,
        private readonly DigitalBusinessCard $cards,
        private readonly DigitalCardService $cardService,
        private readonly MediaService $mediaService,
        private readonly Request $request,
        private readonly PDO $db,
        private readonly Role $roles
    ) { parent::__construct(); }

    public function index(): void
    {
        $page = max(1, $this->request->integer('page', 1));
        $search = $this->request->string('search'); $status = $this->request->string('status'); $department = $this->request->string('department');
        $this->render('staff/index', ['title' => 'Staff Management', 'staff' => $this->staff->paginate($page, 20, $search, $status, $department), 'total' => $this->staff->total($search, $status, $department), 'page' => $page, 'search' => $search, 'status' => $status, 'department' => $department, 'departments' => $this->staff->departments()]);
    }

    public function create(): void { $this->renderForm([], 'create'); }

    public function store(): void
    {
        $this->csrf(); [$data, $errors] = $this->validated();
        if ($errors !== []) { $this->formError('/admin/staff/create', $data, $errors); }
        try { $data['profile_photo_media_id'] = $this->photoId($data['profile_photo_media_id'] ?? null, $data['display_name']); }
        catch (Throwable $e) { $this->formError('/admin/staff/create', $data, ['profile_photo' => [$e->getMessage()]]); }
        $actor = (int) (Session::get('user')['id'] ?? 0);
        $roleIds = $this->roleIds();
        $portalAccess = $this->request->boolean('portal_access');
        $password = $this->request->rawString('password');
        if ($portalAccess && mb_strlen($password) < 8) { $this->formError('/admin/staff/create', $data, ['password' => ['A temporary password of at least 8 characters is required when portal access is enabled.']]); }
        $data['password'] = password_hash($portalAccess ? $password : bin2hex(random_bytes(24)), PASSWORD_DEFAULT);
        $data['user_type'] = 'staff';
        $data['status'] = $portalAccess ? 'active' : 'inactive';
        $this->db->beginTransaction();
        try {
            $id = $this->staff->create($data);
            $this->roles->syncUser($id, $roleIds);
            $this->cardService->createForStaff($id, $data['display_name']);
            $this->staff->log($id, $actor ?: null, 'created', 'Staff profile and Digital Business Card created.');
            $this->db->commit();
        } catch (Throwable $e) { $this->db->rollBack(); throw $e; }
        $this->redirectSuccess('/admin/staff/' . $id, 'Staff member created with a Digital Business Card and QR code.');
    }

    public function show(int $id): void
    {
        $record = $this->staff->profile($id); if ($record === null) { $this->abort404(); }
        $this->render('staff/show', ['title' => $record['display_name'] . ' · Staff Profile', 'record' => $record, 'activities' => $this->staff->activities($id), 'analytics' => $record['card_id'] ? $this->cards->analytics((int) $record['card_id']) : [], 'cardUrl' => $record['public_slug'] ? $this->cardService->publicUrl($record) : '', 'assignedRoles' => $this->roles->forUser($id)]);
    }

    public function edit(int $id): void
    {
        $record = $this->staff->profile($id); if ($record === null) { $this->abort404(); } $this->renderForm($record, 'edit');
    }

    public function update(int $id): void
    {
        $this->csrf(); $existing = $this->staff->profile($id); if ($existing === null) { $this->abort404(); }
        [$data, $errors] = $this->validated($id); if ($errors !== []) { $this->formError('/admin/staff/' . $id . '/edit', $data, $errors); }
        try { $data['profile_photo_media_id'] = $this->photoId($data['profile_photo_media_id'] ?? $existing['profile_photo_media_id'], $data['display_name']); }
        catch (Throwable $e) { $this->formError('/admin/staff/' . $id . '/edit', $data, ['profile_photo' => [$e->getMessage()]]); }
        $actor = (int) (Session::get('user')['id'] ?? 0);
        $portalAccess = $this->request->boolean('portal_access');
        $password = $this->request->rawString('password');
        if ($portalAccess && ($existing['account_status'] ?? 'inactive') !== 'active' && mb_strlen($password) < 8) { $this->formError('/admin/staff/' . $id . '/edit', $data, ['password' => ['Set a password of at least 8 characters when enabling portal access.']]); }
        if ($password !== '') { if (mb_strlen($password) < 8) { $this->formError('/admin/staff/' . $id . '/edit', $data, ['password' => ['Password must be at least 8 characters.']]); } $data['password'] = password_hash($password, PASSWORD_DEFAULT); }
        $data['status'] = $portalAccess ? 'active' : 'inactive';
        $this->staff->update($id, $data);
        $this->roles->syncUser($id, $this->roleIds());
        $this->staff->log($id, $actor ?: null, 'updated', 'Staff profile details and access roles updated.', ['employment_status' => $data['employment_status'], 'portal_access' => $portalAccess]);
        $this->redirectSuccess('/admin/staff/' . $id, 'Staff profile updated.');
    }

    public function status(int $id): void
    {
        $this->csrf(); $record = $this->staff->profile($id); if ($record === null) { $this->abort404(); }
        $status = $this->request->string('status'); if (!in_array($status, self::statuses(), true)) { $this->redirectError('/admin/staff/' . $id, 'Invalid staff status.'); }
        $this->staff->update($id, ['employment_status' => $status]);
        $this->staff->log($id, (int) (Session::get('user')['id'] ?? 0) ?: null, 'status_changed', 'Staff status changed from ' . $record['status'] . ' to ' . $status . '.');
        $this->redirectSuccess('/admin/staff/' . $id, 'Staff status updated.');
    }

    public function export(): never
    {
        $rows = $this->staff->allForExport($this->request->string('search'), $this->request->string('status'), $this->request->string('department'));
        header('Content-Type: text/csv; charset=UTF-8'); header('Content-Disposition: attachment; filename="udyam-staff-' . date('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'wb'); fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Employee Code','Display Name','First Name','Last Name','Designation','Department','Email','Mobile','Employment Type','Manager','Office Location','Status','Joining Date','Card URL']);
        foreach ($rows as $row) { fputcsv($out, [$row['employee_code'],$row['display_name'],$row['first_name'],$row['last_name'],$row['designation'],$row['department'],$row['email'],$row['mobile'],$row['employment_type'],$row['manager_name'],$row['office_location'],$row['status'],$row['date_of_joining'],$row['public_slug'] ? $this->cardService->publicUrl($row) : '']); }
        fclose($out); exit;
    }

    private function renderForm(array $record, string $mode): void
    {
        $this->render('staff/form', ['title' => ($mode === 'edit' ? 'Edit' : 'Add') . ' Staff Member', 'record' => $record, 'mode' => $mode, 'managers' => $this->staff->managerOptions(isset($record['id']) ? (int) $record['id'] : null), 'mediaImages' => $this->staff->mediaImages(), 'suggestedCode' => $this->staff->nextEmployeeCode(), 'statuses' => self::statuses(), 'roles' => $this->roles->all(), 'selectedRoleIds' => isset($record['id']) ? $this->roles->roleIdsForUser((int)$record['id']) : []]);
    }

    private function validated(?int $ignoreId = null): array
    {
        $fields = ['employee_code','first_name','last_name','display_name','designation','department','email','mobile','alternate_mobile','office_extension','date_of_joining','employment_type','office_location','address','bio','skills','linkedin_url','website_url','whatsapp_number','emergency_contact','internal_notes'];
        $data = []; foreach ($fields as $field) { $data[$field] = $this->request->string($field); }
        $data['employee_code'] = strtoupper($data['employee_code']); $data['email'] = strtolower($data['email']);
        $data['reporting_manager_id'] = $this->request->integer('reporting_manager_id') ?: null;
        $data['profile_photo_media_id'] = $this->request->integer('profile_photo_media_id') ?: null;
        $data['employment_status'] = $this->request->string('status');
        if ($data['display_name'] === '') { $data['display_name'] = trim($data['first_name'] . ' ' . $data['last_name']); }
        $errors = [];
        foreach (['employee_code','first_name','last_name','display_name','designation','department','email','mobile','date_of_joining','employment_type','employment_status'] as $field) { if ($data[$field] === '') { $errors[$field === 'employment_status' ? 'status' : $field][] = ucfirst(str_replace('_', ' ', $field)) . ' is required.'; } }
        if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) { $errors['email'][] = 'Enter a valid email address.'; }
        elseif ($data['email'] !== '' && $this->staff->existsBy('email', $data['email'], $ignoreId)) { $errors['email'][] = 'This email already belongs to another staff member.'; }
        if ($data['employee_code'] !== '' && $this->staff->existsBy('employee_code', $data['employee_code'], $ignoreId)) { $errors['employee_code'][] = 'This employee code is already in use.'; }
        if (!in_array($data['employment_status'], self::statuses(), true)) { $errors['status'][] = 'Select a valid status.'; }
        if (!in_array($data['employment_type'], ['full_time','part_time','contract','intern','consultant'], true)) { $errors['employment_type'][] = 'Select a valid employment type.'; }
        foreach (['linkedin_url','website_url'] as $field) { if ($data[$field] !== '' && !filter_var($data[$field], FILTER_VALIDATE_URL)) { $errors[$field][] = 'Enter a complete URL including https://.'; } }
        if ($ignoreId !== null && $data['reporting_manager_id'] === $ignoreId) { $errors['reporting_manager_id'][] = 'A staff member cannot report to themselves.'; }
        $data['status'] = $data['employment_status'];
        return [$data, $errors];
    }

    private function photoId(?int $selected, string $name): ?int
    {
        $file = $this->request->file('profile_photo');
        if ($file !== null && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            return $this->mediaService->upload($file, ['title' => $name . ' profile photo', 'alt_text' => $name], (int) (Session::get('user')['id'] ?? 0));
        }
        return $selected ?: null;
    }

    private function roleIds(): array
    {
        $roles = $this->request->input('role_ids', []);
        return is_array($roles) ? array_values(array_filter(array_map('intval', $roles))) : [];
    }

    private function formError(string $path, array $old, array $errors): never { $old['role_ids'] = $this->roleIds(); $old['portal_access'] = $this->request->boolean('portal_access') ? 1 : 0; Session::set('old', $old); Session::set('errors', $errors); $this->redirect($path); }
    private function csrf(): void { if (!csrf_validate()) { http_response_code(419); exit('Your session expired.'); } }
    private static function statuses(): array { return ['active','inactive','on_leave','resigned','suspended']; }
}
