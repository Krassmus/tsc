<div style="margin-top: 20px; margin-bottom:20px; text-align: center;">

<? if (count($forces) > 1) : ?>
<select id="sent_by_force" onChange="$('div[id^=news_composer_adressees_]:visible').fadeOut(function () { $('#news_composer_adressees_'+$('#sent_by_force').val()).fadeIn(); }); TSC.news.initUpload();">
<? foreach ($forces as $force) : ?>
	<option value="<?= $force ?>"><?= Forces::id2name($force) ?></option>
<? endforeach ?>
</select>
<? else : ?>
<input type="hidden" id="sent_by_force" value="<?= $forces[0] ?>">
<? endif ?>

<? foreach ($forces as $key => $force) : ?>
<div style="<?= $key > 0 ? "display: none; " : "" ?>" id="news_composer_adressees_<?= $force ?>">
	<select multiple="multiple" id="news_composer_adressee_of_<?= $force ?>" name="adressee[<?= $force ?>][]" style="width: 50%; height: 100px;">
	<? foreach ($adressees[$force] as $adressee) : ?>
		<option value="<?= $adressee ?>"><?= Forces::id2name($adressee) ?></option>
	<? endforeach ?>
	</select>
</div>
<? endforeach ?>

<input type="hidden" id="news_composer_message_id" name="message_id" value="<?= $old_news['id'] ?>">

<div style="margin: 20px;">
	<div id="news_composer_pdfswitch">
		<input type="radio" id="news_composer_pdfswitch_text"<?= !$old_news['pdf'] ? ' checked="checked" ' : "" ?>onClick="$('#news_composer_pdfswitch_pdf').removeAttr('checked'); $('#news_composer_pdfswitch_text').attr('checked', 'checked'); $('div#news_composer_content_pdf:visible').fadeOut(function () { $('div#news_composer_content_text').fadeIn(); });"><label for="news_composer_pdfswitch_text">Text</label>
		<input type="radio" id="news_composer_pdfswitch_pdf"<?= $old_news['pdf'] ? ' checked="checked" ' : "" ?>onClick="$('#news_composer_pdfswitch_text').removeAttr('checked'); $('#news_composer_pdfswitch_pdf').attr('checked', 'checked'); $('div#news_composer_content_text:visible').fadeOut(function () { $('div#news_composer_content_pdf').fadeIn(); });"><label for="news_composer_pdfswitch_pdf">Dokument</label>
		<script>
		$(function () {
			$("#news_composer_pdfswitch").buttonset();
		});
		</script>
	</div>
</div>
	
<div style="margin: 20px;">
	<div id="news_composer_content_text">
		<textarea style="width: 100%; height: 200px; display:block;" id="news_composer_message" onChange="TSC.news.saveNews();"><?= escape($old_news['content']) ?></textarea>
	</div>
	
	<div id="news_composer_content_pdf" style="display: none;" class="matrix_uploader">
		<noscript>
			<p>Please enable JavaScript to use file uploader.</p>
			<!-- or put a simple form for upload here -->
		</noscript>
	</div>
	<script>
	$(TSC.news.initUpload);
	</script>
</div>


<input type="button" value="absenden" onClick="TSC.news.sendNews();">

</div>