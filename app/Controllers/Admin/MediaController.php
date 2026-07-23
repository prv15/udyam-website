<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Services\Media\Exceptions\UploadException;
use App\Services\Media\MediaService;
use Throwable;

final class MediaController extends AdminController
{
    public function __construct(private readonly MediaService $mediaService, private readonly Request $request)
    {
        parent::__construct();
    }

    public function index(): void
    {
        $page = max(1, $this->request->integer('page', 1));
        $search = $this->request->string('search');
        $result = $this->mediaService->paginate($page, 24, $search);

        $this->render('media/index', [
            'title' => 'Media Library',
            'media' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => 24,
            'search' => $search,
        ]);
    }

    public function upload(): void
    {
        $this->ensureValidCsrf();
        $file = $this->request->file('media');

        if ($file === null) {
            Session::flash('error', 'Please choose a file to upload.');
            $this->redirect('/admin/media');
        }

        try {
            $this->mediaService->upload($file, $this->request->only(['title', 'alt_text', 'caption', 'description']), (int) Session::get('user')['id']);
            Session::flash('success', 'Media uploaded successfully.');
        } catch (UploadException $exception) {
            Session::flash('error', $exception->getMessage());
        } catch (Throwable $exception) {
            error_log('Media upload failed: ' . $exception->getMessage());
            Session::flash('error', 'The upload could not be completed.');
        }

        $this->redirect('/admin/media');
    }

    public function delete(int $id): void
    {
        $this->ensureValidCsrf();
        $this->mediaService->delete($id);
        Session::flash('success', 'Media moved to trash.');
        $this->redirect('/admin/media');
    }

    private function ensureValidCsrf(): void
    {
        if (csrf_validate()) {
            return;
        }

        http_response_code(419);
        exit('Your session expired. Please refresh the page and try again.');
    }
}
