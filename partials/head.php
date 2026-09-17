<?php
// UCID: mrc82
// Date: 2026-08-02
// Summary: Loads shared page metadata, Bootstrap CSS, and project styles.
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?php echo htmlspecialchars($title ?? "Rick and Morty Project"); ?></title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
    crossorigin="anonymous"
>

<link
    rel="stylesheet"
    href="<?php echo htmlspecialchars(project_url("styles.css")); ?>"
>