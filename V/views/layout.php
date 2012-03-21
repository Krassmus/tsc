<!DOCTYPE html>
<html>
<head>
    <title>TSC</title>
    <meta http-quiv="content-type" content="text/html; charset=utf-8">
    <link rel="icon" href="media/images/favicon.png" type="image/png">
    <script src="media/jquery-1.4.2.min.js" type="text/javascript"></script>
    <script src="media/jquery-ui-1.8.5.custom.min.js" type="text/javascript"></script>
    <script src="media/autoresize.jquery.min.js" type="text/javascript"></script>
    <script src="media/jquery.scrollTo-min.js" type="text/javascript"></script>
    <script src="media/qqUpload/fileuploader.js" type="text/javascript"></script>
    <link rel="stylesheet" href="media/qqUpload/fileuploader.css" type="text/css">
    <script src="media/tsc.js" type="text/javascript"></script>
    <link rel="stylesheet" href="media/jquery-ui-1.8.5.custom.css" type="text/css">
    <link rel="stylesheet" href="media/V.css" type="text/css">
<?= FileInclude::getHeaderFiles() ?>
    <style>
        <? if (!$stil['font']) : ?>
        @font-face {
            font-family: OCR A Extended;
            local: OCR A Extended;
            src: url(./media/ocraext.ttf);  
        }
        <? endif ?>
        <? if (!$stil['headerfont']) : ?>
        @font-face {
            font-family: Aero;
            local: Aero;
            src: url(./media/aeaswfte.ttf);  
        }
        <? endif ?>
        body {
            background-image: url('<?= $stil['backgroundimage'] > 0 ? "file.php?module=matrix&type=MatrixImage&file_id=".$stil['backgroundimage'] : "media/images/Homecoming_by_keepwalking07.jpg" ?>');
        }
        body, input, select, button {
            font-family: <?= $stil['font'] ? escape($stil['font']) : "OCR A Extended, Andale Mono, Lucida Sans Typewriter, Silom, Fixedsys, MONOSPACE" ?>;
            font-size: <?= $stil['fontsize'] ? escape($stil['fontsize']) : "12" ?>px;
        }
        #main_frame {
            width: <?= floor($width/(count($modules)))*count($modules) ?>px;
        }
        div.header {
            width: <?= floor($width/(count($modules))) ?>px;
            max-width: <?= floor($width/(count($modules))) ?>px;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: <?= $stil['headerfont'] ? escape($stil['headerfont']) : "Aero" ?>;
            font-size: <?= $stil['headerfontsize'] ? escape($stil['headerfontsize']) : "16" ?>px;
        }
        h1 {
            font-size: <?= $stil['headerfontsize'] ? escape($stil['headerfontsize']+2) : "18" ?>px;
        }
        h3, h4, h5, h6 {
            font-size: <?= $stil['headerfontsize'] ? escape($stil['headerfontsize']-2) : "14" ?>px;
        }
        h1, h2, h3, .colored {
            color: <?
                if ($stil['headercolor'] === "0") print "#FFAD00";
                if ($stil['headercolor'] === "1") print "#8BEDFF";
                if ($stil['headercolor'] === "2") print "#5CFF58";
            ?>;
        }
    </style>
    <script>
        TSC.stil = {
            headercolor: '<?= $stil['headercolor'] ?>'
        };
    </script>
</head>
<body>
    <div id="main_frame" style="display: none;">
        <div id="headline">
            <? foreach ($modules as $mod) : ?>
            <div class="header" id="header_<?= get_class($mod) ?>" onClick="return TSC.link.show('<?= get_class($mod) ?>')">
                <? $title = $mod->getTitle() ?>
                <div class="lightable">
                    <span class="space"></span>
                    <h2><?= escape($title) ?></h2>
                    <span class="space"></span>
                </div>
            </div>
            <? endforeach ?>
        </div><!-- headline Ende -->
        <div id="bodies">
        <? $count = 0 ?>
        <? foreach ($modules as $mod) : ?>
        <div id="body_<?= get_class($mod)?>">
            <div class="header bridge" style="margin-left: <?= floor($width/(count($modules)))*$count ?>px">
                <div class="lightable">
                    <ul>
                    <? foreach ($mod->getNavigation() as $action => $link) : ?>
                        <li><a href="" onClick="TSC.link.show('<?= get_class($mod) ?>', '<?= $action ?>'); return false;">
                                <?= htmlentities($link['title']) ?>
                        </a></li>
                    <? endforeach ?>
                    </ul>
                </div>
            </div>
            <? $first = true ?>
            <? foreach ($mod->getNavigation() as $action => $link) : ?>
            <div class="content" id="content_<?= get_class($mod) ?>_<?= $action ?>"<? 
                if (($mod->defaultAction() && $mod->defaultAction() !== $action) 
                        OR (!$mod->defaultAction() && !$first)) {
                    print ' style="display: none;">'; 
                } else {
                    print ">";
                    $mod->activateAction($action);
                    $first = false;
                } ?>
            </div>
            <? endforeach ?>
            
        </div>
            <? $count++ ?>
        <? endforeach; ?>
        </div><!-- bodies Ende -->
    </div><!-- main_frame Ende -->
    <script>
        $(function () { $('#main_frame').fadeIn(1000); } );
    </script>

</body>
</html>