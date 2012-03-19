<h1><?= escape($image['filename']) ?></h1>
<?
$norm = 600;
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
<img src="file.php?module=matrix&type=MatrixImage&file_id=<?= $image['id'] ?>" width="<?= $width ?>" height="<?= $height ?>" onClick="$('#matrix_image_details').slideUp(function () {$('#matrix_image_canvas').slideDown();});" style="cursor: pointer;">
<br>

<div>
    <br><br>
    <span class="center" style="letter-spacing: 1.0em;">DATENBLATT</span>
    <table class="fine_table">
        <tbody>
            <tr>
                <td>Veröffentlichen mit</td>
                <td>
                    <pre>[img]<?= $image['filename'] ?></pre>
                    oder
                    <pre>[img]<?= $image['id'] ?></pre>
                </td>
            </tr>
			<? if (is_array($matrixseiten) && count($matrixseiten)) : ?>
			<tr>
				<td>Verwendet von</td>
				<td><ul>
					<? foreach ($matrixseiten as $page) : ?>
					<li><a onClick="TSC.matrix.openArticle('<?= $page['gruppe'] ?>', '<?= Text::escape(str_replace("'", "%HOCHKOMMA%", str_replace(" ", "%20", $page['name']))) ?>');"><?= Text::escape($page['name']) ?></a></li>
					<? endforeach ?>
				</ul></td>
			</tr>
			<? endif ?>
            <tr>
                <td>Aktionen</td>
                <td>
                    <input type="button" value="löschen" onClick="">
                </td>
            </tr>
        </tbody>
    </table>
</div>

<? if ($masterof->has($image['matrix']) OR $force->has($image['autor_force_id'])) : ?>
<div class="matrix_uploader" id="matrix_image_refileuploader">
    <div id="matrix_image_refileuploader">
        <noscript>
            <p>Please enable JavaScript to use file uploader.</p>
            <!-- or put a simple form for upload here -->
        </noscript>         
    </div>
    <div style="margin-bottom: 10px;">
        <select name="force_id" id="matrix_pictureupload_force_id" onChange="TSC.matrix.initReUpload();">
            <? foreach ($force as $f) : ?>
            <option value="<?= $f ?>"><?= Forces::id2name($f) ?></option>
            <? endforeach ?>
        </select>
    </div>
</div>
<script>
TSC.matrix.initReUpload = function () {
    var uploader = new qq.FileUploader({
        element: $('#matrix_image_refileuploader')[0],
        // path to server-side upload script
        action: 'ajax.php',
        params: {
            controller: "matrix",
            action: "reupload_picture",
            group: '<?= $matrix ?>',
            file_id: '<?= $image['id'] ?>',
            force_id: $("#matrix_pictureupload_force_id").val()
        },
        debug: false
    });
};
$(TSC.matrix.initReUpload);
</script>
<? endif ?>