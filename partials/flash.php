<?php
// mrc82 - 2026-07-17
// Displays queued flash messages once and safely escapes their contents.
?>

<div class="container" id="flash">
    <?php $messages = get_messages(); ?>

    <?php if (!empty($messages)): ?>
        <?php foreach ($messages as $msg): ?>
            <?php
            $color = htmlspecialchars($msg["color"] ?? "info");
            $text = htmlspecialchars($msg["text"] ?? "");
            ?>

            <div class="row justify-content-center">
                <div class="alert alert-<?php echo $color; ?>" role="alert">
                    (php) <?php echo $text; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<style>
    #flash {
        left: 50%;
        transform: translateX(-50%);
        width: auto;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
        opacity: 0.9;
        z-index: 1000;
        position: fixed;
        top: 1rem;
    }

    #flash:empty,
    #flash:blank,
    #flash:not(:has(*)):not(:empty) {
        display: none;
    }
</style>

<script>
    (() => {
        // 30 seconds helps when gathering milestone evidence.
        let flash_message_delay = 30000;

        function process_flash_messages() {
            const now = Date.now();

            document.querySelectorAll("#flash .row").forEach((message) => {
                if (!message.dataset.removeAt) {
                    message.dataset.removeAt =
                        now + flash_message_delay;
                }

                const remove_at = Number(message.dataset.removeAt);

                if (remove_at <= now) {
                    message.remove();
                }
            });
        }

        setInterval(process_flash_messages, 100);
    })();
</script>