<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Models\Media;
use App\Models\Page;
use App\Models\PageSection;
use App\Services\Media\Exceptions\UploadException;
use App\Services\Media\MediaService;
use Throwable;

final class PageSectionsController extends AdminController
{
    private array $templates;

    public function __construct(
        private readonly Page $pages,
        private readonly PageSection $sections,
        private readonly Media $media,
        private readonly MediaService $mediaService,
        private readonly Request $request
    ) {
        parent::__construct();
        $this->templates = require CONFIG_PATH . '/page-templates.php';
    }

    public function index(int $pageId): void
    {
        [$page, $template] = $this->pageTemplate($pageId);
        $this->sections->seed($pageId, $template['sections']);
        $this->render('pages/sections/index', [
            'title' => $page['title'] . ' Sections', 'page' => $page,
            'template' => $template, 'sections' => $this->sections->forPage($pageId),
        ]);
    }

    public function edit(int $pageId, string $sectionKey): void
    {
        [$page, $template] = $this->pageTemplate($pageId);
        if (!isset($template['sections'][$sectionKey])) {
            $this->abort404();
        }
        $this->sections->seed($pageId, $template['sections']);
        $section = $this->sections->findForPage($pageId, $sectionKey);
        $images = array_values(array_filter(
            $this->media->paginate(1, 300, ''),
            static fn (array $item): bool => str_starts_with((string) $item['mime_type'], 'image/')
        ));
        $this->render('pages/sections/edit', [
            'title' => 'Edit ' . $template['sections'][$sectionKey]['label'],
            'page' => $page, 'sectionKey' => $sectionKey,
            'definition' => $template['sections'][$sectionKey],
            'section' => $section, 'mediaImages' => $images,
        ]);
    }

    public function update(int $pageId, string $sectionKey): void
    {
        $this->csrf();
        [, $template] = $this->pageTemplate($pageId);
        if (!isset($template['sections'][$sectionKey])) {
            $this->abort404();
        }
        $data = $this->extractData($template['sections'][$sectionKey]['fields']);
        $this->sections->saveSection($pageId, $sectionKey, $data, $this->request->boolean('is_enabled'));
        $this->redirectSuccess('/admin/pages/sections/' . $pageId, 'Section updated successfully.');
    }

    public function upload(int $pageId, string $sectionKey): void
    {
        $this->csrf();
        [, $template] = $this->pageTemplate($pageId);
        $fieldName = $this->request->string('upload_field');
        $field = $template['sections'][$sectionKey]['fields'][$fieldName] ?? null;

        if (!is_array($field) || ($field['type'] ?? '') !== 'media') {
            $this->redirectError(
                '/admin/pages/sections/' . $pageId . '/edit/' . $sectionKey,
                'Please select a valid image field.'
            );
        }

        $file = $this->request->file('upload_' . $fieldName);
        if ($file === null || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            $this->redirectError(
                '/admin/pages/sections/' . $pageId . '/edit/' . $sectionKey,
                'Choose an image before clicking Upload & Use.'
            );
        }

        try {
            $mediaId = $this->mediaService->upload(
                $file,
                [
                    'title' => $field['label'] . ' - ' . basename((string) ($file['name'] ?? 'Image')),
                    'alt_text' => $field['label'],
                ],
                (int) Session::get('user')['id']
            );
            $section = $this->sections->findForPage($pageId, $sectionKey);
            $data = is_array($section['data'] ?? null) ? $section['data'] : [];
            $data[$fieldName] = $mediaId;
            $this->sections->saveSection($pageId, $sectionKey, $data, (bool) ($section['is_enabled'] ?? true));
            $this->redirectSuccess(
                '/admin/pages/sections/' . $pageId . '/edit/' . $sectionKey,
                'Image uploaded and selected successfully.'
            );
        } catch (UploadException $exception) {
            $this->redirectError(
                '/admin/pages/sections/' . $pageId . '/edit/' . $sectionKey,
                $exception->getMessage()
            );
        } catch (Throwable $exception) {
            error_log('Section image upload failed: ' . $exception->getMessage());
            $this->redirectError(
                '/admin/pages/sections/' . $pageId . '/edit/' . $sectionKey,
                'The image could not be uploaded. Please try again.'
            );
        }
    }

    public function reorder(int $pageId): void
    {
        $this->csrf();
        [, $template] = $this->pageTemplate($pageId);
        $keys = $_POST['section_order'] ?? [];
        $allowed = array_keys($template['sections']);
        $keys = array_values(array_filter(
            is_array($keys) ? $keys : [],
            static fn (mixed $key): bool => is_string($key) && in_array($key, $allowed, true)
        ));
        if (count($keys) === count($allowed)) {
            $this->sections->reorder($pageId, $keys);
        }
        $this->redirectSuccess('/admin/pages/sections/' . $pageId, 'Section order updated.');
    }

    private function pageTemplate(int $pageId): array
    {
        $page = $this->pages->find($pageId);
        if ($page === null) {
            $this->abort404();
        }
        $templateKey = (string) ($page['template'] ?? 'default');
        $template = $this->templates[$templateKey] ?? null;
        if ($template === null || $template['sections'] === []) {
            $this->redirectError('/admin/pages/edit/' . $pageId, 'Select a structured page template first.');
        }
        return [$page, $template];
    }

    private function extractData(array $fields): array
    {
        $data = [];
        foreach ($fields as $name => $field) {
            if ($field['type'] === 'repeater') {
                $input = $_POST[$name] ?? [];
                $rows = [];
                if (is_array($input)) {
                    $rowCount = max(array_map(
                        static fn (mixed $values): int => is_array($values) ? count($values) : 0,
                        $input
                    ) ?: [0]);
                    for ($index = 0; $index < $rowCount; $index++) {
                        $row = [];
                        foreach ($field['fields'] as $subName => $label) {
                            $row[$subName] = trim((string) ($input[$subName][$index] ?? ''));
                        }
                        if (array_filter($row, static fn (string $value): bool => $value !== '') !== []) {
                            $rows[] = $row;
                        }
                    }
                }
                $data[$name] = $rows;
            } elseif ($field['type'] === 'media') {
                $id = $this->request->integer($name);
                $data[$name] = $id > 0 && $this->media->find($id) !== null ? $id : null;
            } elseif ($field['type'] === 'number') {
                $data[$name] = max(0, $this->request->integer($name));
            } else {
                $data[$name] = $this->request->string($name);
            }
        }
        return $data;
    }

    private function csrf(): void
    {
        if (!csrf_validate()) {
            http_response_code(419);
            exit('Your session expired. Please refresh and try again.');
        }
    }
}
