<button
    style="color: white; background-color: red; border-color: red;"
    <?= isset($onclick) ? 'onclick="' . $onclick . '"' : '' ?>
    type="<?= $type ?? 'submit' ?>"
>
    <?= $slot ?>
</button>
