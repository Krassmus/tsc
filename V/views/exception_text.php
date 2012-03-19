<strong>
<?= $exception->getMessage() ?>
</strong>
<div style="margin: 10px;">
    <?= $exception->getFile() ?> : line <?= $exception->getLine() ?>
</div>
<div style="margin: 10px; font-style: italic;">
<?= nl2br($exception->getTraceAsString()) ?>
</div>