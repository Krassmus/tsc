<div id="matrix_image_canvas">

<div id="matrix_image_container">
<? foreach ($images as $image) : ?>
    <?
    $norm = 100;
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
    <a class="matrix_image" 
       id="matrix_images_<?= $image['id'] ?>"
       style="background-image: url('file.php?module=matrix&type=MatrixImage&file_id=<?= $image['id'] ?>');
              -moz-background-size: <?= $width ?>px <?= $height ?>px;
              -webkit-background-size: <?= $width ?>px <?= $height ?>px;
              background-size: <?= $width ?>px <?= $height ?>px;
              background-position: <?= $width > $norm ? "-".floor(($width-$norm) / 2)."px" : "center" ?> <?= $height > $norm ? "-".floor(($height-$norm) / 2)."px" : "center" ?>;
              "
	   onClick="TSC.matrix.showImageDetails('<?= $image['id'] ?>')">
    </a>
<? endforeach ?>
</div>

<div class="matrix_uploader">
    <div id="matrix_fileuploader">       
        <noscript>          
            <p>Please enable JavaScript to use file uploader.</p>
            <!-- or put a simple form for upload here -->
        </noscript>         
    </div>
    <div style="margin-bottom: 10px;">
        <select name="force_id" id="matrix_pictureupload_force_id" onChange="TSC.matrix.initUpload();">
            <? foreach ($force as $f) : ?>
            <option value="<?= $f ?>"><?= Forces::id2name($f) ?></option>
            <? endforeach ?>
        </select>
    </div>
</div>
<script>
$(TSC.matrix.initUpload);
</script>

</div>


<div id="matrix_image_details">

</div>

