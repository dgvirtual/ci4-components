<button
    style="color: white; background-color: green; border-color: green;"
    <?= isset($onclick) ? 'onclick="' . $onclick . '"' : '' ?>
    type="<?= $type ?? 'submit' ?>"
>
    <?= $slot ?>
</button>
