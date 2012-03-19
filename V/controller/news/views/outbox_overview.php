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
        <?= date("c", $row['date']) ?>
    </td>
	<td>
		<? if ($row['topicture']) : ?>
		<div style="background-image: url(file.php?module=matrix&type=MatrixImage&file_id=<?= $row['topicture'] ?>);" class="logo medium">
		<? endif ?>
        <? print Forces::id2name($row['toforce']) ?>
		<? if ($row['topicture']) : ?>
		</div>
		<? endif ?>
	</td>
</tr>