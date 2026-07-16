<div class="card mb-3">
    <div class="card-header">
        <?= esc($slot ?? '') ?>
    </div>
    <div class="card-body mt-3" id="famous-quote-body">
        <?php if ($cached && $quote !== null): ?>
            <p class="card-text text-center"><?= esc($quote['text']) ?></p>
            <footer class="blockquote-footer text-center"><em><?= esc($quote['author']) ?></em></footer>
        <?php else: ?>
            <p class="card-text text-center" id="famous-quote-text">
                <em>Loading famous quote…</em>
            </p>
            <footer class="blockquote-footer text-center" id="famous-quote-author">
                <em>&nbsp;</em>
            </footer>
        <?php endif; ?>
    </div>
    <div class="card-footer" style="font-size:10px;">Cached for <?= esc($seconds) ?> seconds</div>
</div>

<?php if (! $cached): ?>
<script>
const fallbackQuote = {
    text: '<?= esc($fallback['text'], 'js') ?>',
    author: '<?= esc($fallback['author'], 'js') ?>',
};

(async function() {
    const quoteTextEl = document.getElementById('famous-quote-text');
    const quoteAuthorEl = document.getElementById('famous-quote-author');

    try {
        const response = await fetch('<?= site_url('famous-quotes/fetch') ?>?seconds=<?= $seconds ?>');

        if (! response.ok) {
            throw new Error('HTTP ' + response.status);
        }

        const data = await response.json();

        quoteTextEl.textContent = data.quote.text;
        quoteAuthorEl.innerHTML = '<em>' + data.quote.author + '</em>';
    } catch (error) {
        // Fall back to the embedded quote when the route or external API
        // is unavailable.
        quoteTextEl.textContent = fallbackQuote.text;
        quoteAuthorEl.innerHTML = '<em>' + fallbackQuote.author + '</em>';
    }
})();
</script>
<?php endif; ?>