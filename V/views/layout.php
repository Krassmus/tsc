<!DOCTYPE html>
<html>
<head>
    <title>TSC</title>
    <meta http-quiv="content-type" content="text/html; charset=utf-8">
    <link rel="icon" href="media/images/favicon.png" type="image/png">
<?= FileInclude::getHeaderFiles() ?>
    <style>
        <? if ($stil['backgroundimage'] > 0) : ?>
        body {
            background-image: url('file.php?module=matrix&type=MatrixImage&file_id=<?= $stil['backgroundimage'] ?>');
        }
        <? endif ?>
        body, input, select, button {
            <? if ($stil['font']) : ?>
            font-family: <?= escape($stil['font']) ?>;
            <? endif ?>
            <? if ($stil['fontsize']) : ?>
            font-size: <?= escape($stil['fontsize']) ?>px;
            <? endif ?>
        }
        #main_frame {
            width: <?= floor($width/(count($modules)))*count($modules) ?>px;
        }
        div.header {
            width: <?= floor($width/(count($modules))) ?>px;
            max-width: <?= floor($width/(count($modules))) ?>px;
        }
        h1, h2, h3, h4, h5, h6 {
            <? if ($stil['headerfont']) : ?>
            font-family: <?= escape($stil['headerfont']) ?>;
            <? endif ?>
            <? if ($stil['headerfontsize']) : ?>
            font-size: <?= escape($stil['headerfontsize']) ?>px;
            <? endif ?>
        }
        <? if ($stil['headerfontsize']) : ?>
        h1 {
            font-size: <?= escape($stil['headerfontsize']+2) ?>px;
        }
        h3, h4, h5, h6 {
            font-size: <?= escape($stil['headerfontsize']-2) ?>px;
        }
        <? endif ?>
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