<?= Template::summon(dirname(__file__)."/breadcrumb.php")
        ->with("breadcrumb", $breadcrumb)
        ->with("matrix", $matrix)
        ->render()
?>
<br>
<select onChange="TSC.matrix.showVersion('<?= $matrix ?>', '<?= escape($page) ?>', this.value);">
    <? foreach ($versionen as $key => $version) : ?>
    <option value="<?= escape($version['version']) ?>" title="von <?= Forces::id2name($version['autor_force']) ?> am <?= $version['version'] ?>"<?= $version['version'] == $artikel['version'] ? " selected" : "" ?>>Version <?= count($versionen)-$key ?></option>
    <? endforeach ?>
</select>
<h1><?= escape($page) ?></h1>
<? if ($artikel) : ?>
    <? if ($additional_data) : ?>
    <span class="center" style="letter-spacing: 1.0em;">DATENBLATT</span>
    <table class="fine_table" style="width: 100%;">
        <tbody>
            <? foreach ($additional_data as $data) : ?>
            <tr>
                <td><?= escape($data[0]) ?></td>
                <td><?= $data[1] ?></td>
            </tr>
            <? endforeach ?>
        </tbody>
    </table>
    <? endif ?>
    <? if ($artikel['bild']) :
                $norm = 250;
                $bild = new MatrixImage(Text::getpic($artikel['bild'], $matrix));
            $image = $bild->getSize();
                $width = $image['width'] ? $image['width'] : $norm;
            $height = $image['height'] ? $image['height'] : $norm;
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
    <? endif ?>
    <?=
        nl2br($diff);
    ?>
<? else : ?>
Keinen passenden Eintrag gefunden.
<? endif ?>
