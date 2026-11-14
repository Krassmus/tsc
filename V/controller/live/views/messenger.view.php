<h1>Schnellnachrichten</h1>

<div class="messenger">
    <?php if (count($force) > 1) : ?>
    <div class="center" style="margin-bottom: 10px;">
        <label>
            Anwesend als
            <select id="live_onlineas" onChange="TSC.live.change_as_online(this.value);" style="vertical-align: middle;">
                <option value="">niemand</option>
                <?php foreach ($force as $f) : ?>
                <option value="<?= $f ?>"<?= $online_as == $f ? " selected" : "" ?>><?= escape(Forces::id2name($f)) ?></option>
                <?php endforeach ?>
            </select>
        </label>
    </div>
    <?php else : ?>
    <input type="hidden" id="live_onlineas" value="<?= $force[0] ?>">
    <?php endif ?>
    <div id="live_contact_container">
        <div>Anwesend ist:
            <ul id="live_contacts">
            </ul>
        </div>
    </div>
    <div id="live_incoming">Eingang:
        <ul id="live_incoming_list">
        </ul>
    </div>
    <div id="live_conversation_with"></div>
    <div id="live_conversations"></div>
    <div id="live_writer" style="display: none;">
        <input type="hidden" id="live_writer_write_to" value="">
        <ul id="live_writer_forcename"></ul>
        <textarea id="live_message_content" class="autoresize"></textarea>
        <input type="button" value="abschicken" onClick="TSC.live.sendMessage();">
    </div>
</div>