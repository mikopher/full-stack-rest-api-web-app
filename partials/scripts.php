 <?php
// UCID: mrc82
// Date: 2026-08-02
// Summary: Loads shared Bootstrap and project JavaScript before the closing body tag.
?>
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"
></script>

<script src="<?php echo htmlspecialchars(project_url("helpers.js")); ?>"></script>

<!-- Temporary course utility used while collecting milestone evidence. -->
<script src="https://matttoegel.github.io/IT202-Utils/submission-utils.js"></script>