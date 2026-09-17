<?php
// UCID: mrc82
// Date: 2026-08-06
// Summary: Displays how many filtered records are currently shown
// compared with the total number of matching records.
?>

<p class="text-body-secondary mb-0" aria-live="polite">
    Showing <?php echo (int) ($shown_count ?? 0); ?> of
    <?php echo (int) ($matching_count ?? 0); ?> matching results.
</p>