<?php

print Text::general_format($mail['content'], $depth, $group);

?>
<? if ($mail['my_message']) : ?>
<hr>
<div class="actionbar">
    <table>
        <tbody>
            <? if ($mail['my_message']) : ?>
            <tr>
                <td><label for="news_message_flag">Markiert</label></td>
                <td><input type="checkbox" name="flag" id="news_message_flag" value="1" onChange="TSC.news.setFlag('<?= $mail['id'] ?>', this);"<?= $mail['flag'] ? " checked" : "" ?>></td>
            </tr>
            <? endif ?>
        </tbody>
    </table>

</div>
<? endif ?>