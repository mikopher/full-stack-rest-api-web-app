<?php
// UCID: mrc82
// Date: 2026-08-07
// Summary: Displays character cards in a responsive Bootstrap grid
// and shows a friendly message when no characters match.

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
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
        <?php foreach ($characters as $character): ?>
            <div class="col">
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