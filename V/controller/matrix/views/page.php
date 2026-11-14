<?= Template::summon(dirname(__file__)."/breadcrumb.php")
        ->with("breadcrumb", $breadcrumb)
        ->with("matrix", $matrix)
        ->render()
?>
<h1><?= escape($page) ?></h1>
<?php if ($artikel) : ?>
    <?php if ($additional_data) : ?>
    <span class="center" style="letter-spacing: 1.0em;">DATENBLATT</span>
    <table class="fine_table" style="width: 100%;">
        <tbody>
            <?php foreach ($additional_data as $data) : ?>
            <tr>
                <td><?= escape($data[0]) ?></td>
                <td><?= $data[1] ?></td>
            </tr>
            <?php endforeach ?>
        </tbody>
    </table>
    <?php endif ?>
    <?php if ($artikel['bild']) :
                $norm = 250;
                $bild = new MatrixImage(Text::getpic($artikel['bild'], $matrix));
            $image = $bild->getSize();
            $image = $image[0] ?? array();
            $width = !empty($image['width']) ? $image['width'] : $norm;
            $height = !empty($image['height']) ? $image['height'] : $norm;
            if ($width > $height) {
                if ($height > $norm) {
                    $width = floor($width * $norm / $height);
                    $height = $norm;
                }
            } else {
                if ($width > $norm) {
                    $height = floor($height * $norm / $width);
                    $width = $norm;
                }
            }

        ?>
        <img src="file.php?module=matrix&type=MatrixImage&file_id=<?= Text::getpic($artikel['bild'], $matrix) ?>" width="<?= $height ?>" width="<?= $height ?>" class="artikel_avatar">
    <?php endif ?>
    <?=
        //Text::wiki_format($artikel['eintrag'], $page, 1, $matrix);
        Text::format($artikel['eintrag'], array("general", "matrix"));
    ?>
<?php else : ?>
Keinen passenden Eintrag gefunden.
<?php endif ?>

<div id="matrix_footer">
    <input type="button" value="Suchen" onClick="TSC.matrix.openArticle('<?= $matrix ?>', 'SUCHMASKE');">
    <input type="button" value="Editieren" onClick="TSC.matrix.editArticle('<?= $matrix ?>', '<?= str_replace("'", '%HOCHKOMMA%', escape($page)) ?>');">
    <select onChange="TSC.matrix.showVersion('<?= $matrix ?>', '<?= str_replace("'", '%HOCHKOMMA%', escape($page)) ?>', this.value);">
        <?php foreach ($versionen as $key => $version) : ?>
        <option value="<?= escape($version['version']) ?>" title="von <?= Forces::id2name($version['autor_force']) ?> am <?= $version['version'] ?>">Version <?= count($versionen)-$key ?></option>
        <?php endforeach ?>
    </select>
    <?php if ($loeschbar) : ?>
    <input type="button" value="Löschen" onClick="TSC.matrix.askForDeleting('<?= $matrix ?>', '<?= str_replace("'", '%HOCHKOMMA%', escape($page)) ?>');">
    <?php endif ?>
    <input type="button" value="Neue Seite" onClick="TSC.matrix.editArticle('<?= $matrix ?>', 'SUCHMASKE');">
</div>