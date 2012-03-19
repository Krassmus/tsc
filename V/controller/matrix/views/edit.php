<?= Template::summon(dirname(__file__)."/breadcrumb.php")
        ->with("breadcrumb", $breadcrumb)
        ->with("matrix", $matrix)
        ->render()
?>
<form name="matrix_edit" style="text-align: center; margin: 20px;">

<div style="margin-left: auto; margin-right: auto; text-align: center">
<? if ($page === "SUCHMASKE" OR !$page) : ?>
<input type="text"
       name="matrix_title"
       id="matrix_title" 
       value=""
       style="width: 300px; font-size: 1.6em; text-align: center;"
       class="colored">
<? else : ?>
<h1><?= escape($page) ?></h1>
<input type="hidden" id="matrix_title" name="matrix_title" value="<?= escape($page) ?>">
<? endif ?>
</div>

<select name="bild" id="matrix_bild">
    <option value="">Kein Bild</option>
    <? foreach ($pictures as $pic) : ?>
    <option value="<?= escape($pic['filename']) ?>"<?= $artikel['bild'] === $pic['filename'] ? " selected" : "" ?>><?= escape($pic['filename']) ?></option>
    <? endforeach ?>
</select><br>

<textarea name="matrix_content" id="matrix_content" style="width: 100%; height: 300px;" class="">
<?= escape($artikel['eintrag']) ?>
</textarea>

<? if (count($force) > 1) : ?>
Schreiben als <select name="autor" id="matrix_autor">
    <? foreach ($force as $f) : ?>
    <option value="<?= $f ?>"><?= escape(Forces::id2name($f)) ?></option>
    <? endforeach ?>
</select>
<? else : ?>
<input type="hidden" name="autor" id="matrix_autor" value="<?= $force[0] ?>">
<? endif ?>
<input type="hidden" name="matrix_group" id="matrix_group" value="<?= $matrix ?>">

<script>
//$("textarea.autoresize").autoResize();
</script>
<br>

<input type="button" value="absenden" onClick="TSC.matrix.saveArticle();">

</form>