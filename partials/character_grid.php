<?php
// UCID: mrc82
// Date: 2026-08-07
// Summary: Displays character cards in a responsive centered Bootstrap grid
// with a comfortable maximum card width.

$card_options = $card_options ?? [];
$characters = $characters ?? [];
$empty_message = $empty_message
    ?? "No characters matched the selected filters.";
?>

<?php if (empty($characters)): ?>
    <div class="alert alert-info" role="status">
        <?php echo htmlspecialchars($empty_message); ?>
    </div>
<?php else: ?>
    <div class="row g-4 justify-content-center">
        <?php foreach ($characters as $character): ?>
            <div class="col-12 col-md-6 col-xl-4 character-grid-item">
                <?php
                render_character_card(
                    $character,
                    $card_options
                );
                ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>