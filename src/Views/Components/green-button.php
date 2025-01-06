<button
    style="color: #ffffff; background-color: #28a745; border-color: #28a745;"
    <?= isset($onclick) ? 'onclick="' . $onclick . '"' : '' ?>
    type="<?= $type ?? 'submit' ?>"
>
    <?= $slot ?>
</button>
