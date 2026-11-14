<div class="breadcrumb">
<?php foreach ($breadcrumb as $key => $lastPage) : ?>
    <?php if ($key !== 0) : ?>
        »
    <?php endif ?>
    <a onClick="TSC.matrix.openArticle('<?= $matrix ?>', '<?= str_replace("'", '%HOCHKOMMA%', $lastPage) ?>');"><?= $lastPage !== "SUCHMASKE" ? $lastPage : "Suchseite" ?></a>
<?php endforeach ?>
</div>