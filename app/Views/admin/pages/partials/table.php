<div class="table-card">

    <table class="custom-table">

        <thead>

            <tr>

                <th width="40">

                    <input type="checkbox">

                </th>

                <th>Title</th>

                <th>Status</th>

                <th>Author</th>

                <th>Updated</th>

                <th width="60"></th>

            </tr>

        </thead>

        <tbody>

        <?php foreach($pages as $page): ?>

            <tr>

                <td>

                    <input type="checkbox">

                </td>

                <td>

                    <strong>

                        <?= htmlspecialchars($page['title']) ?>

                    </strong>

                    <br>

                    <small>

                        <?= htmlspecialchars($page['slug']) ?>

                    </small>

                </td>

                <td>

                    <span class="badge <?= strtolower($page['status'])=='published' ? 'badge-success':'badge-warning' ?>">

                        <?= htmlspecialchars($page['status']) ?>

                    </span>

                </td>

                <td>

                    <?= htmlspecialchars($page['author'] ?: 'System') ?>

                </td>

                <td>

                    <?= htmlspecialchars($page['updated_at'] ?? '') ?>

                </td>

                <td>

                    <a class="icon-button" href="<?= htmlspecialchars(url('/admin/pages/edit/' . $page['id'])) ?>" title="Edit page">
                        <i data-lucide="pencil"></i>
                    </a>
                    <form method="post" action="<?= htmlspecialchars(url('/admin/pages/delete/' . $page['id'])) ?>" class="d-inline" onsubmit="return confirm('Delete this page?');">
                        <?= csrf_field() ?>
                        <button class="icon-button" type="submit" title="Delete page"><i data-lucide="trash-2"></i></button>
                    </form>

                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

</div>
