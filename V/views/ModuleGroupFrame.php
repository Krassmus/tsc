<div id="<?= $controller ?>_selector" class="selectors">
<? if (count($group) > 1) : ?>
<? foreach ($group as $key => $g) : ?>
    <input type="button" class="selector" value="<?= $g['name'] ?>" onClick="TSC.link.activateGroup('<?= $controller ?>', '<?= $g['id'] ?>');">
<? endforeach ?>
<? endif ?>
</div>
<? foreach ($group as $key => $g) : ?>
<div id="<?= $controller ?>_content_<?= $g['id'] ?>" class="<?= $controller ?>_grouped_content"<?= $key > 0 ? ' style="display: none;"' : "" ?>>
<?= ($key === 0) ? $content : "" ?>
</div>
<? endforeach ?>