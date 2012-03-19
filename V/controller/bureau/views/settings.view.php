<h1>Einstellungen</h1>
<h2>Stileinstellungen</h2>
<form name="stileinstellungen">
<table>
    <tr>
        <td>
            Überschriften<br>
            <span style="font-size: 0.8em;">Wirksam erst nach Neuladen.</span>
        </td>
        <td>
            <select name="ueberschrift_farbe" onChange="TSC.bureau.set('headercolor', this);">
            <option value="0"<?= (($stil[0] == 0) ? " selected" : "") ?>>orange</option>
            <option value="1"<?= (($stil[0] == 1) ? " selected" : "") ?>>blau</option>
            <option value="2"<?= (($stil[0] == 2) ? " selected" : "") ?>>grün</option>
            </select>
        </td>
    </tr>
    <tr>
        <td>
            Hintergrund-Bild<br>
            <span style="font-size: 0.8em;">Optimale Bildbreite sind 900px.</span>
        </td>
        <td>
            <select name="backimage" onChange="TSC.bureau.set('backgroundimage', this);">
                <option value="-1">
                    standard
                </option>
                <? foreach ($allebilder as $bild) : ?>
                <option value="<?= $bild['id']?>"<?= (($bild['id'] == $stil['backgroundimage']) ? " selected" : "") ?>>
                    <?= escape($bild['filename']) ?>
                </option>
                <? endforeach ?>
            </select>
        </td>
    </tr>
    <tr>
        <td>Breite</td>
        <td><input type="text" onChange="TSC.bureau.set('width', this);" value="<?= $stil['width'] ?>"></td>
    </tr>
    <tr>
        <td>Über-Schrift-Art<br>
        <span style="font-size: 0.8em;">Wirksam erst nach Neuladen.</span></td><td><input type="Text" onChange="TSC.bureau.set('headerfont', this);" value="<?= $stil[1] ?>">
        <select onChange="TSC.bureau.set('headerfontsize', this);"><option value=8<?= (($stil[3] == 8) ? ' selected': '') ?>>8</option><option value=9<?= (($stil[3] == 9) ? ' selected': '') ?>>9</option><option value=10<?= (($stil[3] == 10) ? ' selected': '') ?>>10</option><option value=11<?= (($stil[3] == 11) ? ' selected': '') ?>>11</option><option value=12<?= (($stil[3] == 12) ? ' selected': '') ?>>12</option><option value=13<?= (($stil[3] == 13) ? ' selected': '') ?>>13</option><option value=14<?= (($stil[3] == 14) ? ' selected': '') ?>>14</option><option value=16<?= ( (($stil[3] == 16) || (!$stil[3]) ) ? ' selected': '') ?>>16</option><option value=18<?= (($stil[3] == 18) ? ' selected': '') ?>>18</option><option value=20<?= (($stil[3] == 20) ? ' selected': '') ?>>20</option><option value=22<?= (($stil[3] == 22) ? ' selected': '') ?>>22</option><option value=24<?= (($stil[3] == 24) ? ' selected': '') ?>>24</option><option value=26<?= (($stil[3] == 26) ? ' selected': '') ?>>26</option><option value=30<?= (($stil[3] == 30) ? ' selected': '') ?>>30</option><option value=35<?= (($stil[3] == 35) ? ' selected': '') ?>>35</option><option value=40<?= (($stil[3] == 40) ? ' selected': '') ?>>40</option></select></td>
    </tr>
    <tr>
        <td>
            Text-Schrift-Art<br>
            <span style="font-size: 0.8em;">Wirksam erst nach Neuladen.</span>
        </td>
        <td>
            <input type="Text" onChange="TSC.bureau.set('font', this);" value="<?= $stil[2] ?>">
            <select onChange="TSC.bureau.set('fontsize', this);" ><option value=8<?= (($stil[4] == 8) ? ' selected': '') ?>>8</option><option value=9<?= (($stil[4] == 9) ? ' selected': '') ?>>9</option><option value=10<?= (($stil[4] == 10) ? ' selected': '') ?>>10</option><option value=11<?= (($stil[4] == 11) ? ' selected': '') ?>>11</option><option value=12<?= ((($stil[4] == 12) || (!$stil[4])) ? ' selected': '') ?>>12</option><option value=13<?= (($stil[4] == 13) ? ' selected': '') ?>>13</option><option value=14<?= (($stil[4] == 14) ? ' selected': '') ?>>14</option><option value=16<?= (($stil[4] == 16) ? ' selected': '') ?>>16</option><option value=18<?= (($stil[4] == 18) ? ' selected': '') ?>>18</option><option value=20<?= (($stil[4] == 20) ? ' selected': '') ?>>20</option><option value=22<?= (($stil[4] == 22) ? ' selected': '') ?>>22</option><option value=24<?= (($stil[4] == 24) ? ' selected': '') ?>>24</option><option value=26<?= (($stil[4] == 26) ? ' selected': '') ?>>26</option><option value=30<?= (($stil[4] == 30) ? ' selected': '') ?>>30</option><option value=35<?= (($stil[4] == 35) ? ' selected': '') ?>>35</option><option value=40<?= (($stil[4] == 40) ? ' selected': '') ?>>40</option></select>
        </td>
    </tr>
</table>
</form>

<? if (count($plugin_settings)) : ?>
<h2>Moduleinstellungen</h2>
<table>
    <tbody>
    <? foreach ($plugin_settings as $setting) : ?>
        <tr>
            <td><?= escape($setting[0]) ?></td>
            <td><?= $setting[1] ?></td>
        </tr>
    <? endforeach ?>
    </tbody>
</table>
<? endif ?>

<h2>Zeichen der Macht</h2>
<form name="setPictures">
<table>
<? foreach ($bilder as $force => $forcepics) : ?>
<tr>
    <td><?= Forces::id2name($force) ?></td>
	<? $bild = Forces::forcespicture($force) ?>
    <td>
        <select name="force_<?= $force ?>_pic" onChange="TSC.bureau.setForcePicture('<?= $force ?>', this.value);">
            <option value="-1">Kein Bild</option>
            <? foreach ($forcepics as $pic) : ?>
            <option value="<?= $pic['id'] ?>" <?= $pic['id'] === $bild['id'] ? " selected" : "" ?>><?= $pic['filename'] ?></option>
            <? endforeach ?>
        </select>
    </td>
	<td>
		<div id="bureau_settings_forcepicture_<?= $force ?>" style="background-image: url(file.php?module=matrix&type=MatrixImage&file_id=<?= $bild['id'] ?>);" class="logo medium">
			<?= escape(Forces::id2name($force)) ?>
		</div>
	</td>
</tr>
<? endforeach ?>
</table>
</form>

<h2>Passwort</h2>
<form name="settings_neuespasswort">
    <input type="Password" name="pass1" id="pass1"> 
    <input type="Password" name="pass2" id="pass2"> 
    <input type=button value="Neues Passwort festlegen." onClick="TSC.bureau.changepassword();">
</form>


<br>
<br>
<br>