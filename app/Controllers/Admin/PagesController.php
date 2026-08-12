<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Models\Media;
use App\Models\Page;
use App\Services\PageService;

final class PagesController extends AdminController
{
    public function __construct(
        private readonly Page $pages,
        private readonly Media $media,
        private readonly Request $request,
        private readonly PageService $pageService
    )
    {
        parent::__construct();
    }

    public function index(): void
    {
        $currentPage = max(1, $this->request->integer('page', 1));
        $perPage = $this->perPage();
        $search = $this->request->string('search');
        $status = $this->request->string('status');
        if (!in_array($status, ['', 'draft', 'published', 'archived'], true)) {
            $status = '';
        }

        $this->render('pages/index', [
            'title' => 'Pages',
            'pages' => $this->pages->paginate($currentPage, $perPage, $search, $status),
            'search' => $search,
            'status' => $status,
            'currentPage' => $currentPage,
            'perPage' => $perPage,
            'total' => $this->pages->total($search, $status),
        ]);
    }

    public function create(): void
    {
        $this->renderForm([], 'create');
    }

    private function perPage(): int
    {
        $value=$this->request->integer('per_page',10);return in_array($value,[10,50,100],true)?$value:10;
    }

    public function store(): void
    {
        $this->ensureValidCsrf();
        [$data, $errors] = $this->validatedInput();
        if ($this->pages->slugExists($data['slug'])) {
            $errors['slug'][] = 'This URL slug is already used by another page.';
        }
        if ($errors !== []) {
            $this->redirectWithErrors('/admin/pages/create', $data, $errors);
        }

        $userId = (int) Session::get('user')['id'];
        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;
        $id = $this->pages->create($data);

        $this->redirectSuccess(
            '/admin/pages/edit/' . $id,
            $data['status'] === 'published' ? 'Page published successfully.' : 'Page saved successfully.'
        );
    }

    public function edit(int $id): void
    {
        $page = $this->pages->find($id);
        if ($page === null) {
            $this->abort404();
        }
        $this->renderForm($page, 'edit');
    }

    public function update(int $id): void
    {
        $this->ensureValidCsrf();
        if ($this->pages->find($id) === null) {
            $this->abort404();
        }
        [$data, $errors] = $this->validatedInput();
        if ($this->pages->slugExists($data['slug'], $id)) {
            $errors['slug'][] = 'This URL slug is already used by another page.';
        }
        if ($errors !== []) {
            $this->redirectWithErrors('/admin/pages/edit/' . $id, $data, $errors);
        }

        $data['updated_by'] = (int) Session::get('user')['id'];
        $this->pages->update($id, $data);
        $this->redirectSuccess(
            '/admin/pages/edit/' . $id,
            $data['status'] === 'published' ? 'Page updated and published.' : 'Page updated successfully.'
        );
    }

    public function preview(int $id): void
    {
        $page = $this->pages->find($id);
        if ($page === null) {
            $this->abort404();
        }
        $chrome = $this->pageService->siteChrome();
        $view = ($page['template'] ?? 'default') === 'sectioned' ? 'website/page-sectioned' : 'website/page';
        $this->view($view, [
            'title' => $page['seo_title'] ?: $page['title'],
            'metaDescription' => $page['seo_description'] ?: $page['excerpt'],
            'metaKeywords' => $page['seo_keywords'],
            'page' => $this->withFeaturedImage($page),
            'sections' => $this->pageService->sections((int) $page['id']),
            'header' => $chrome['header'],
            'footer' => $chrome['footer'],
            'preview' => true,
            'bodyClass' => 'udyam-home inner-website-page',
        ]);
    }

    public function destroy(int $id): void
    {
        $this->ensureValidCsrf();
        if ($this->pages->find($id) !== null) {
            $this->pages->delete($id);
        }
        $this->redirectSuccess('/admin/pages', 'Page moved to trash.');
    }

    private function renderForm(array $page, string $mode): void
    {
        $this->render('pages/form', [
            'title' => $mode === 'edit' ? 'Edit Page' : 'Create Page',
            'mode' => $mode,
            'page' => $page,
            'errors' => Session::get('errors', []),
            'old' => Session::get('old', []),
            'mediaImages' => array_values(array_filter(
                $this->media->paginate(1, 200, ''),
                static fn (array $item): bool => str_starts_with((string) $item['mime_type'], 'image/')
            )),
        ]);
        Session::forget('errors');
        Session::forget('old');
    }

    private function validatedInput(): array
    {
        $title = $this->request->string('title');
        $rawSlug = $this->request->string('slug');
        $slug = slugify($rawSlug !== '' ? $rawSlug : $title);
        $status = $this->request->string('status', 'draft');
        $data = [
            'title' => $title,
            'slug' => $slug,
            'template' => $this->request->string('template', 'default'),
            'excerpt' => $this->request->string('excerpt'),
            'content' => trim((string) $this->request->post('content', '')),
            'featured_image' => $this->request->integer('featured_image') ?: null,
            'seo_title' => $this->request->string('seo_title'),
            'seo_description' => $this->request->string('seo_description'),
            'seo_keywords' => $this->request->string('seo_keywords'),
            'status' => $status,
        ];
        $errors = [];
        if ($title === '') {
            $errors['title'][] = 'Page title is required.';
        } elseif (mb_strlen($title) > 255) {
            $errors['title'][] = 'Page title may not exceed 255 characters.';
        }
        if ($slug === '') {
            $errors['slug'][] = 'Enter a valid URL slug.';
        } elseif (mb_strlen($slug) > 255) {
            $errors['slug'][] = 'URL slug may not exceed 255 characters.';
        }
        if (!in_array($status, ['draft', 'published', 'archived'], true)) {
            $errors['status'][] = 'Select a valid page status.';
        }
        $templates = require CONFIG_PATH . '/page-templates.php';
        if (!array_key_exists($data['template'], $templates)) {
            $errors['template'][] = 'Select a valid page template.';
        }
        if (mb_strlen($data['excerpt']) > 500) {
            $errors['excerpt'][] = 'Short description may not exceed 500 characters.';
        }
        if (mb_strlen($data['seo_description']) > 320) {
            $errors['seo_description'][] = 'SEO description may not exceed 320 characters.';
        }
        if ($data['featured_image'] !== null && $this->media->find($data['featured_image']) === null) {
            $errors['featured_image'][] = 'Select a valid image from the media library.';
        }
        return [$data, $errors];
    }

    private function redirectWithErrors(string $path, array $old, array $errors): never
    {
        Session::set('old', $old);
        Session::set('errors', $errors);
        $this->redirect($path);
    }

    private function ensureValidCsrf(): void
    {
        if (!csrf_validate()) {
            http_response_code(419);
            exit('Your session expired. Please refresh the page and try again.');
        }
    }

    private function withFeaturedImage(array $page): array
    {
        $page['featured_image_url'] = null;
        if (!empty($page['featured_image'])) {
            $image = $this->media->find((int) $page['featured_image']);
            if ($image !== null) {
                $page['featured_image_url'] = media_url($image);
            }
        }
        return $page;
    }
}
