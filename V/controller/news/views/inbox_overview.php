<tr id="news_inbox_<?= $id ?>" class="lightable<?= $row['yet_read'] ? "" : " neu" ?><?= $row['pdf'] ? " pdf" : "" ?><?= $row['flag'] ? " marked" : "" ?>" timestamp="<?= $row['date'] ?>">
    <td>
    	<? if ($row['frompicture']) : ?>
        <div style="background-image: url(file.php?module=matrix&type=MatrixImage&file_id=<?= $row['frompicture'] ?>);" class="logo medium">
        <? endif ?>
        <? print escape(Forces::id2name($row[1])) ?>
        <? if ($row['frompicture']) : ?>
        </div>
        <? endif ?>
	</td>
    <td>
        <div class="flag" title="markiert">&nbsp;&nbsp;</div>
        <?
        if ($row['pdf']) {
            $datei = substr(strstr($row[3], "_"), 1);
            $datei = substr($datei, 0, strrpos($datei, "."));
            print '<table width=100% border=0><tr><td width=130><img style="float:left" title="Datei-Nachricht" src="media/images/PDF.png"></td><td style="text-align:left'.( !$row['yet_read'] ? '; color: #FFFE7E' : '').'">'.Text::autowrap($datei).'</td></tr></table>';
        } else {
            print Text::shortened_format($row[3], 0);
        }
        ?>
    </td>
    <td>
        <?= date("Y:n:j:G:i:s", $row['date']) ?>
    </td>
</tr>