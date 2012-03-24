<tr id="news_outbox_<?= $id ?>" class="lightable<?= $row['pdf'] ? " pdf" : "" ?>" timestamp="<?= $row['date'] ?>">
    <? if (false) : ?>
	<td>
        <? print Forces::id2name($row[1]) ?>
    </td>
	<? endif ?>
    <td>
        <?
        if ($row['pdf']) {
            $datei = substr(strstr($row['content'], "_"), 1);
            $datei = substr($datei, 0, strrpos($datei, "."));
            print '<table width=100% border=0><tr><td width=130><img style="float:left" title="Datei-Nachricht" src="media/images/PDF.png"></td><td style="text-align:left'.( !$row['yet_read'] ? '; color: #FFFE7E' : '').'">'.Text::autowrap($datei).'</td></tr></table>';
        } else {
            print Text::shortened_format($row['content'], 0);
        }
        ?>
    </td>
    <td>
        <?= date("Y:n:j:G:i:s", $row['date']) ?>
    </td>
	<td>
        <? $toforces = explode('_', $row['toforces']) ?>
        <div style="background-image: <?= (count($toforces) < 2 && $row['topicture'] > 0) ? "url('file.php?module=matrix&type=MatrixImage&file_id=".$row['topicture']."');" : "linear-gradient(right bottom, #444444, #111111); background-image: -o-linear-gradient(right bottom, #444444, #111111); background-image: -moz-linear-gradient(right bottom, #444444, #111111); background-image: -webkit-linear-gradient(right bottom, #444444, #111111); background-image: -ms-linear-gradient(right bottom, #444444, #111111);" ?>" class="logo medium">
        <? foreach ($toforces as $key => $toforce) : ?>
            <?= $key > 0 ? "<br>" : "" ?>
            <?= Forces::id2name($toforce) ?>
        <? endforeach ?>
        </div>
        </td>
</tr>