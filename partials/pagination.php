<?php
// UCID: mrc82
// Date: 2026-08-07
// Summary: Renders reusable pagination controls while preserving
// the active filters, sorting choices, and result limit.

if ($total_pages <= 1) {
    return;
}

if (!isset($page)) {
    $page = 1;
}

if (!isset($query_params)) {
    $query_params = [];
}

$start_page = max(
    1,
    $page - 2
);

$end_page = min(
    $total_pages,
    $page + 2
);

/*
 * Fill missing page-number positions when near
 * the beginning of the result set.
 */
if ($start_page === 1) {
    $end_page = min(
        $total_pages,
        5
    );
}

/*
 * Fill missing page-number positions when near
 * the end of the result set.
 */
if ($end_page === $total_pages) {
    $start_page = max(
        1,
        $total_pages - 4
    );
}

$previous_url = pagination_url(
    $query_params,
    max(1, $page - 1)
);

$next_url = pagination_url(
    $query_params,
    min($total_pages, $page + 1)
);
?>

<nav
    aria-label="Result pages"
    class="mt-4 d-flex justify-content-center"
>
    <ul class="pagination flex-wrap mb-0">

        <li
            class="page-item <?php
                if ($page <= 1) {
                    echo "disabled";
                }
            ?>"
        >
            <?php if ($page <= 1): ?>
                <span
                    class="page-link"
                    aria-disabled="true"
                >
                    Previous
                </span>
            <?php else: ?>
                <a
                    class="page-link"
                    href="<?php
                        echo htmlspecialchars(
                            $previous_url
                        );
                    ?>"
                >
                    Previous
                </a>
            <?php endif; ?>
        </li>

        <?php
        for (
            $number = $start_page;
            $number <= $end_page;
            $number++
        ):
        ?>
            <li
                class="page-item <?php
                    if ($number === $page) {
                        echo "active";
                    }
                ?>"
            >
                <a
                    class="page-link"
                    <?php if ($number === $page): ?>
                        aria-current="page"
                    <?php endif; ?>
                    href="<?php
                        echo htmlspecialchars(
                            pagination_url(
                                $query_params,
                                $number
                            )
                        );
                    ?>"
                >
                    <?php echo $number; ?>
                </a>
            </li>
        <?php endfor; ?>

        <li
            class="page-item <?php
                if ($page >= $total_pages) {
                    echo "disabled";
                }
            ?>"
        >
            <?php if ($page >= $total_pages): ?>
                <span
                    class="page-link"
                    aria-disabled="true"
                >
                    Next
                </span>
            <?php else: ?>
                <a
                    class="page-link"
                    href="<?php
                        echo htmlspecialchars(
                            $next_url
                        );
                    ?>"
                >
                    Next
                </a>
            <?php endif; ?>
        </li>

    </ul>
</nav>