<?php

declare(strict_types=1);

$totalPages = (int) ceil($total / $perPage);

if ($totalPages <= 1) {
    return;
}

?>

<nav class="mt-4">

    <ul class="pagination justify-content-center">

        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">

            <a
                class="page-link"
                href="?page=<?= $page - 1 ?>&search=<?= urlencode($search ?? '') ?>">

                Previous

            </a>

        </li>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>

            <li class="page-item <?= $i === $page ? 'active' : '' ?>">

                <a
                    class="page-link"
                    href="?page=<?= $i ?>&search=<?= urlencode($search ?? '') ?>">

                    <?= $i ?>

                </a>

            </li>

        <?php endfor; ?>

        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">

            <a
                class="page-link"
                href="?page=<?= $page + 1 ?>&search=<?= urlencode($search ?? '') ?>">

                Next

            </a>

        </li>

    </ul>

</nav>