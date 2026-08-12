<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Models\AdminRecord;
use App\Repositories\CustomerPortalRepository;

final class ModulesController extends AdminController
{
    private array $modules;

    public function __construct(private readonly AdminRecord $records, private readonly Request $request, private readonly CustomerPortalRepository $customerPortal)
    {
        parent::__construct();
        $this->modules = require CONFIG_PATH . '/modules.php';
    }

    public function index(string $module): void
    {
        $definition = $this->definition($module);
        if ($module === 'contact-messages') {
            $this->records->updateStatuses('contact-messages', ['new', 'active'], 'in_progress');
            $this->shared['unreadContactCount'] = 0;
        }
        $page = max(1, $this->request->integer('page', 1));
        $perPage = $this->perPage();
        $search = $this->request->string('search');
        $tenderFilters=['search'=>$search,'region'=>$this->request->string('region'),'invited_by'=>$this->request->string('invited_by'),'sort'=>in_array($this->request->string('sort','latest'),['latest','deadline','expired','ongoing'],true)?$this->request->string('sort','latest'):'latest'];
        $partnerFilters=['search'=>$search,'state'=>$this->request->string('state'),'turnover'=>$this->request->string('turnover')];
        $total=$module==='tenders'?$this->records->tendersTotal($tenderFilters):($module==='customers'?$this->records->partnersTotal($partnerFilters):$this->records->total($module,$search));
        $pages=max(1,(int)ceil($total/$perPage));$page=min($page,$pages);
        $this->render('modules/index', [
            'title' => $definition['title'], 'module' => $module, 'definition' => $definition,
            'records' => $module==='tenders'?$this->records->paginateTenders($page,$perPage,$tenderFilters):($module==='customers'?$this->records->paginatePartners($page,$perPage,$partnerFilters):$this->records->paginate($module,$page,$perPage,$search)),
            'total' => $total, 'page' => $page, 'perPage' => $perPage, 'search' => $search,
            'tenderFilters'=>$tenderFilters,'tenderFilterOptions'=>$module==='tenders'?$this->records->tenderFilterOptions():[],
            'partnerFilters'=>$partnerFilters,'partnerFilterOptions'=>$module==='customers'?$this->records->partnerFilterOptions():[],
        ]);
    }

    public function create(string $module): void
    {
        $definition = $this->definition($module);
        $this->render('modules/form', [
            'title' => 'Create ' . $definition['singular'], 'module' => $module,
            'definition' => $definition, 'record' => [], 'mode' => 'create',
            'statusOptions' => $this->statusOptions($module),
        ]);
    }

    public function importForm(): void
    {
        $module = 'tenders';
        $definition = $this->definition($module);
        $this->render('modules/tender-import', [
            'title' => 'Bulk Import Tenders & Notices', 'module' => $module, 'definition' => $definition,
        ]);
    }

    public function import(): void
    {
        $module = 'tenders';
        $this->definition($module);
        $this->csrf();
        $upload = $this->request->file('import_file');
        if (!is_array($upload) || ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file((string) ($upload['tmp_name'] ?? ''))) {
            Session::set('import_errors', ['Choose a valid CSV file to import.']);
            $this->redirect('/admin/tenders/import');
        }
        if (($upload['size'] ?? 0) > 5 * 1024 * 1024 || strtolower(pathinfo((string) ($upload['name'] ?? ''), PATHINFO_EXTENSION)) !== 'csv') {
            Session::set('import_errors', ['Only CSV files up to 5 MB can be imported.']);
            $this->redirect('/admin/tenders/import');
        }
        $handle = fopen((string) $upload['tmp_name'], 'rb');
        $header = $handle ? fgetcsv($handle, 0, ',', '"', '\\') : false;
        $normalize = static fn (string $value): string => strtolower(trim(preg_replace('/[^a-z0-9]+/i', '_', $value), '_'));
        $headers = is_array($header) ? array_map(static fn ($value): string => $normalize((string) $value), $header) : [];
        $required = ['state_region', 'invited_by', 'tender_project_details', 'last_date', 'time', 'submission_mode'];
        if ($handle === false || array_diff($required, $headers) !== []) {
            if ($handle !== false) fclose($handle);
            Session::set('import_errors', ['The CSV header must include: State / Region, Invited By, Tender / Project Details, Last Date, Time, Submission Mode.']);
            $this->redirect('/admin/tenders/import');
        }
        $index = array_flip($headers);
        $value = static fn (array $row, string $key): string => isset($index[$key]) ? trim((string) ($row[$index[$key]] ?? '')) : '';
        $created = 0;
        $skipped = [];
        $rowNumber = 1;
        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            $rowNumber++;
            if ($row === [null] || $row === []) continue;
            $title = $value($row, 'tender_project_details');
            if ($title === '') {
                $skipped[] = 'Row ' . $rowNumber . ': Tender / Project Details is required.';
                continue;
            }
            $deadline = $value($row, 'last_date');
            $date = $this->importDate($deadline);
            $type = strtolower($value($row, 'record_type'));
            if (!in_array($type, ['tender', 'notice', 'corrigendum'], true)) $type = 'tender';
            $data = [
                'type' => $type, 'region' => $value($row, 'state_region'), 'invited_by' => $value($row, 'invited_by'),
                'department' => $value($row, 'invited_by'), 'title' => $title, 'deadline_label' => $deadline,
                'closing_date' => $date, 'submission_time' => $value($row, 'time'),
                'submission_mode' => $value($row, 'submission_mode'), 'document_url' => $value($row, 'document_url'),
                'description' => $value($row, 'additional_notes'),
            ];
            $this->records->create([
                'module' => 'tenders', 'title' => $title, 'slug' => null, 'status' => 'published',
                'sort_order' => 0, 'data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'created_by' => (int) (Session::get('user')['id'] ?? 0), 'updated_by' => (int) (Session::get('user')['id'] ?? 0),
            ]);
            $created++;
        }
        fclose($handle);
        Session::set('success', $created . ' tender/notice record' . ($created === 1 ? '' : 's') . ' imported successfully.');
        if ($skipped !== []) Session::set('import_errors', array_slice($skipped, 0, 10));
        $this->redirect('/admin/tenders');
    }

    public function importTemplate(): void
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="udyam-tenders-notices-import-template.csv"');
        $output = fopen('php://output', 'wb');
        fputcsv($output, ['Record Type', 'State / Region', 'Invited By', 'Tender / Project Details', 'Last Date', 'Time', 'Submission Mode', 'Document URL', 'Additional Notes']);
        fputcsv($output, ['Tender', 'PAN INDIA', 'Example Ministry / Organisation', 'Brief tender or project details', '31 August 2026', '05:00 PM', 'Online', 'https://example.gov.in/tender', 'Optional internal note']);
        fputcsv($output, ['Notice', 'Odisha', 'Example Directorate', 'Open empanelment / notice details', 'Open Throughout Year', '–', 'Online', '', '']);
        fclose($output);
        exit;
    }

    public function store(string $module): void
    {
        $definition = $this->definition($module);
        $this->csrf();
        [$data, $errors] = $this->validatedData($definition);
        if ($module === 'tenders') $data = $this->normalizeTenderData($data);
        if ($errors !== []) {
            Session::set('errors', $errors);
            Session::set('old', $data);
            $this->redirect('/admin/' . $module . '/create');
        }
        $recordId = $this->records->create([
            'module' => $module, 'title' => (string) ($data['title'] ?? reset($data)),
            'slug' => ($data['slug'] ?? null) ?: null, 'status' => $this->validStatus($module),
            'sort_order' => max(0, $this->request->integer('sort_order')),
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_by' => (int) Session::get('user')['id'], 'updated_by' => (int) Session::get('user')['id'],
        ]);
        if (in_array($module, ['applications','documents','notifications'], true)) {
            $this->customerPortal->bridgeLegacyRecord($module,$recordId,$data,$this->validStatus($module));
        }
        $this->redirectSuccess('/admin/' . $module, $definition['singular'] . ' created successfully.');
    }

    public function edit(string $module, int $id): void
    {
        $definition = $this->definition($module);
        $record = $this->records->findForModule($module, $id);
        if ($record === null) {
            $this->abort404();
        }
        $this->render('modules/form', [
            'title' => 'Edit ' . $definition['singular'], 'module' => $module,
            'definition' => $definition, 'record' => $record, 'mode' => 'edit',
            'statusOptions' => $this->statusOptions($module),
        ]);
    }

    public function update(string $module, int $id): void
    {
        $definition = $this->definition($module);
        $this->csrf();
        if ($this->records->findForModule($module, $id) === null) {
            $this->abort404();
        }
        [$data, $errors] = $this->validatedData($definition);
        if ($module === 'tenders') $data = $this->normalizeTenderData($data);
        if ($errors !== []) {
            Session::set('errors', $errors);
            Session::set('old', $data);
            $this->redirect('/admin/' . $module . '/edit/' . $id);
        }
        $this->records->update($id, [
            'title' => (string) ($data['title'] ?? reset($data)), 'slug' => ($data['slug'] ?? null) ?: null,
            'status' => $this->validStatus($module),
            'sort_order' => max(0, $this->request->integer('sort_order')),
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'updated_by' => (int) Session::get('user')['id'],
        ]);
        $this->customerPortal->syncLegacyRecord($module,$id,$data,$this->validStatus($module),(int)Session::get('user')['id']);
        $this->redirectSuccess('/admin/' . $module, $definition['singular'] . ' updated successfully.');
    }

    public function destroy(string $module, int $id): void
    {
        $this->definition($module);
        $this->csrf();
        if($module==='customers'){
            $this->customerPortal->deleteCustomerRecords([$id]);
        }elseif ($this->records->findForModule($module, $id) !== null) {
            $this->records->delete($id);
        }
        $this->redirectSuccess('/admin/' . $module, 'Record deleted successfully.');
    }

    public function bulkDelete(string $module): void
    {
        $definition = $this->definition($module);
        $this->csrf();
        $ids = $this->request->input('record_ids', []);
        $selected=is_array($ids)?$ids:[];
        $deleted=$module==='customers'
            ?$this->customerPortal->deleteCustomerRecords($selected)
            :$this->records->deleteManyForModule($module,$selected);
        if ($deleted === 0) {
            $this->redirectSuccess('/admin/' . $module, 'No records were selected.');
        }
        $this->redirectSuccess('/admin/' . $module, $deleted . ' ' . strtolower($definition['singular']) . ($deleted === 1 ? ' was' : 's were') . ' deleted.');
    }

    private function definition(string $module): array
    {
        if (!isset($this->modules[$module])) {
            $this->abort404();
        }
        return $this->modules[$module];
    }

    private function validatedData(array $definition): array
    {
        $data = [];
        $errors = [];
        foreach ($definition['fields'] as $name => $field) {
            $value = $this->request->string($name);
            $data[$name] = $value;
            if (($field['required'] ?? false) && $value === '') {
                $errors[$name][] = $field['label'] . ' is required.';
            }
            if (($field['type'] ?? '') === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$name][] = 'Please enter a valid email address.';
            }
            if (isset($field['maxlength']) && mb_strlen($value) > $field['maxlength']) {
                $errors[$name][] = $field['label'] . ' is too long.';
            }
            if (isset($field['options']) && $value !== '' && !array_key_exists($value, $field['options'])) {
                $errors[$name][] = $field['label'] . ' contains an invalid value.';
            }
        }
        return [$data, $errors];
    }

    private function csrf(): void
    {
        if (!csrf_validate()) {
            http_response_code(419);
            exit('Your session expired. Please refresh the page and try again.');
        }
    }

    private function validStatus(string $module): string
    {
        $options = $this->statusOptions($module);
        $status = $this->request->string('status', (string) array_key_first($options));
        if ($module === 'tenders' && $status === 'custom') {
            $custom = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', $this->request->string('custom_status')), '-'));
            return $custom !== '' ? substr($custom, 0, 60) : 'active';
        }
        return array_key_exists($status, $options) ? $status : (string) array_key_first($options);
    }

    private function statusOptions(string $module): array
    {
        return match ($module) {
            'applications' => [
                'submitted' => 'Submitted', 'under_review' => 'Under Review',
                'information_requested' => 'Information Requested', 'approved' => 'Approved',
                'in_progress' => 'In Progress', 'completed' => 'Completed',
                'rejected' => 'Rejected', 'closed' => 'Closed',
            ],
            'documents' => [
                'pending' => 'Pending Verification', 'verified' => 'Verified',
                'rejected' => 'Rejected', 'expired' => 'Expired',
            ],
            'notifications' => [
                'draft' => 'Draft', 'scheduled' => 'Scheduled', 'sent' => 'Sent', 'failed' => 'Failed',
            ],
            'contact-messages' => [
                'new' => 'New', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'spam' => 'Spam',
            ],
            'newsletter' => [
                'active' => 'Subscribed', 'unsubscribed' => 'Unsubscribed', 'bounced' => 'Bounced',
            ],
            'tenders' => ['active' => 'Active', 'expired' => 'Expired', 'cancelled' => 'Cancelled'],
            'services', 'focus-areas', 'blog', 'testimonials', 'faqs', 'team', 'partners', 'menu', 'seo' => [
                'draft' => 'Draft', 'published' => 'Published', 'inactive' => 'Inactive',
            ],
            default => ['active' => 'Active', 'inactive' => 'Inactive'],
        };
    }

    private function perPage(): int
    {
        $value=$this->request->integer('per_page',10);
        return in_array($value,[10,50,100],true)?$value:10;
    }

    private function importDate(string $value): string
    {
        if ($value === '' || stripos($value, 'open throughout') !== false) return '';
        $timestamp = strtotime($value);
        return $timestamp === false ? '' : date('Y-m-d', $timestamp);
    }

    private function normalizeTenderData(array $data): array
    {
        $data['department'] = $data['invited_by'] ?? '';
        if (($data['closing_date'] ?? '') === '' && ($data['deadline_label'] ?? '') !== '') {
            $data['closing_date'] = $data['deadline_label'];
        }
        if (($data['deadline_label'] ?? '') === '' && ($data['closing_date'] ?? '') !== '') {
            $data['deadline_label'] = $data['closing_date'];
        }
        return $data;
    }
}
