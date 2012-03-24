<div id="news_selector">
    <div>
        <input type="button" value="Suchen" onClick="TSC.news.showSearch();">
    </div>
	<div>
        <input type="button" value="Eingang" onClick="TSC.news.showInbox();">
    </div>
	<div>
        <input type="button" value="Ausgang" onClick="TSC.news.showOutbox();">
    </div>
	<div>
        <select id="news_year_selected" onChange="TSC.news.cleanBoxes(); $('#news_eingang').is(':visible') ? TSC.news.showInbox() : TSC.news.showOutbox();">
            <? 
            for ($i = 0; $i <= $maximum - $minimum; $i++) {
                $schongeschrieben = false;
                print '<option value="'.$i.'"'.(($jahresauswahl == $i) ? " selected" : "").'>';
                for ($j=0; $j < count($gruppe); $j++) {
                    if (!$schongeschrieben) print "Jahr ";
                    if ($schongeschrieben) print " / ";
                    print $group_year[$j]['jahreszahl']-$i;
                    $schongeschrieben = true;
                }
                print '</option>';
            }
            ?>
        </select>
    </div>
</div>

<div id="news_search" style="display: none;">
    <div id="news_search_form">
        <input type="text" id="news_search_text">
        <input type="button" value="suchen" onClick="TSC.news.searchFor();">
    </div>
    <table id="news_search_table" class="fine_table">
        <thead>
            <tr>
                <th>Von</th>
                <th>Nachricht</th>
                <th>Datum</th>
                <th>Nach</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
    
<table id="news_eingang" class="fine_table">
    <thead>
        <th>Von</th>
        <th>Nachricht</th>
        <th>Datum</th>
        <? if (count($force) > 1) : ?>
        <th>Nach</th>
        <? endif ?>
    </thead>
    <tbody>
    </tbody>
</table>

<table id="news_ausgang" class="fine_table" style="display: none;">
    <thead>
        <? if (count($force) > 1) : ?>
        <th>Von</th>
        <? endif ?>
        <th>Nachricht</th>
        <th>Datum</th>
        <th>Nach</th>
    </thead>
    <tbody>
    </tbody>
</table>

<div style="display: none;" id="news_singlemessage">
</div>
