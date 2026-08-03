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
        $search = $this->request->string('search');
        $this->render('modules/index', [
            'title' => $definition['title'], 'module' => $module, 'definition' => $definition,
            'records' => $this->records->paginate($module, $page, 20, $search),
            'total' => $this->records->total($module, $search), 'page' => $page,
            'perPage' => 20, 'search' => $search,
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

    public function store(string $module): void
    {
        $definition = $this->definition($module);
        $this->csrf();
        [$data, $errors] = $this->validatedData($definition);
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
        if ($this->records->findForModule($module, $id) !== null) {
            $this->records->delete($id);
        }
        $this->redirectSuccess('/admin/' . $module, 'Record deleted successfully.');
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
            'services', 'focus-areas', 'blog', 'tenders', 'testimonials', 'faqs', 'team', 'partners', 'menu', 'seo' => [
                'draft' => 'Draft', 'published' => 'Published', 'inactive' => 'Inactive',
            ],
            default => ['active' => 'Active', 'inactive' => 'Inactive'],
        };
    }
}
