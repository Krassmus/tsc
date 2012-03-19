<div class="breadcrumb">
<? foreach ($breadcrumb as $key => $lastPage) : ?>
    <? if ($key !== 0) : ?>
        »
    <? endif ?>
    <a onClick="TSC.matrix.openArticle('<?= $matrix ?>', '<?= str_replace("'", '%HOCHKOMMA%', $lastPage) ?>');"><?= $lastPage !== "SUCHMASKE" ? $lastPage : "Suchseite" ?></a>
<? endforeach ?>
</div>