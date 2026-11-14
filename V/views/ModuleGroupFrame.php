<div id="<?= $controller ?>_selector" class="selectors">
<?php if (count($group) > 1) : ?>
<?php foreach ($group as $key => $g) : ?>
    <input type="button" class="selector" value="<?= $g['name'] ?>" onClick="TSC.link.activateGroup('<?= $controller ?>', '<?= $g['id'] ?>');">
<?php endforeach ?>
<?php endif ?>
</div>
<?php foreach ($group as $key => $g) : ?>
<div id="<?= $controller ?>_content_<?= $g['id'] ?>" class="<?= $controller ?>_grouped_content"<?= $key > 0 ? ' style="display: none;"' : "" ?>>
<?= ($key === 0) ? $content : "" ?>
</div>
<?php endforeach ?>