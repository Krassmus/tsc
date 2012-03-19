<div id="bureau_admin_<?= $gruppe ?>_overview">
<input type="hidden" value="<?= $gruppe ?>" name="gruppe">

<h1>Gruppenverwaltung <?= Groups::id2name($gruppe) ?></h1>

<table class="fine_table">
    <tr>
        <td>Jahr <?= $jahr[$gruppe] ? $jahr[$gruppe] : 0 ?></td>
        <td><input type="button" value="Neues Jahr starten" onClick="TSC.bureau.adminHappynewyear('<?= $gruppe ?>');"></td>
    </tr>
    <tr>
        <td>Mächte der Sternengruppe</td>
        <td>
        <? foreach($maechte[$gruppe] as $macht) : ?>
            <a onClick="TSC.bureau.adminMachtanzeige('<?= $gruppe ?>', '<?= $macht['id'] ?>');">
                <?= $macht['name'] ?> (<?= implode(" ", $macht['players']) ?>)
            </a><br>
        <? endforeach ?>
        </td>
    </tr>
    <tr>
        <td>Neue Spieler aufnehmen</td>
        <td> 
            <table border=0>
                <tr>
                    <td>Loginname des neuen Spielers: </td>
                    <td><input type="text" id="bureau_admin_<?= $gruppe ?>_spielername"></td>
                </tr>
                <tr>
                    <td>Name der neuen Macht</td>
                    <td><input type="text" id="bureau_admin_<?= $gruppe ?>_spielermacht"></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="button" value="senden" onClick="TSC.bureau.adminNeuerSpielerUndNameFuerGruppe('<?= $gruppe ?>');"></td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td>Neue Macht ohne Spieler</td>
        <td> 
            <table border=0>
                <tr>
                    <td>Name der neuen Macht</td>
                    <td><input type="text" id="bureau_admin_<?= $gruppe ?>_neuemacht"></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="button" value="senden" onClick="TSC.bureau.adminNeueMachtFuerGruppe('<?= $gruppe ?>');"></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            Module
            <p style="font-size: 0.8em;">
                Änderungen werden erst nach Neuladen sichtbar
            </p>
        </td>
        <td>
            <table>
                <tbody>
                    <tr>
                        <td>Aktiv</td>
                        <td>Auf Halde</td>
                    </tr>
                    <tr group="<?= $gruppe ?>">
                        <td>
                            <ul id="bureau_<?= $gruppe ?>_modules" style="min-width: 200px; border: 1px rgba(255, 255, 255, 0.4) solid;">
                            <? foreach ($activated_modules[$gruppe] as $modul) : ?>
                                <li class="module" style="cursor: move;">
                                    <?= escape($modul['modulename']) ?>
                                </li>
                            <? endforeach ?>
                            </ul>
                        </td>
                        <td>
                            <ul id="bureau_<?= $gruppe ?>_possiblemodules" style="min-width: 200px; border: 1px rgba(255, 255, 255, 0.4) solid;">
                            <? foreach (array_merge($controller, $plugins) as $modul) : ?>
                                <? if (!isset($activated_modules[$gruppe][$modul])) : ?>
                                <li class="module" style="cursor: move;">
                                    <?= escape($modul) ?>
                                </li>
                                <? endif ?>
                            <? endforeach ?>
                            </ul>
                        </td>
                    </tr>
                </tbody>
            </table>
            <script>
            $("#bureau_<?= $gruppe ?>_modules").sortable({ 
                connectWith: "#bureau_<?= $gruppe ?>_possiblemodules",
                revert: 150,
                update: TSC.bureau.adminModules
            });
            $("#bureau_<?= $gruppe ?>_possiblemodules").sortable({
                connectWith: "#bureau_<?= $gruppe ?>_modules",
                revert: 150
            });
            </script>
        </td>
    </tr>
    <tr>
        <td>Gruppeneigenschaften</td>
        <td>
            Matrix publizieren <input name="publizieren" type="checkbox" onChange="function ('<?= $gruppe ?>') {}"><br>
        </td>
    </tr>
    <tr>
        <td>Statistik</td>
        <td>
            <?= $statistic[$gruppe] ? $statistic[$gruppe] : "Keine Daten vorhanden" ?>
        </td>
    </tr>
    <tr>
        <td>Matrix</td>
        <td>
            <a href="ajax.php?controller=matrix&action=get_encyclopedia&group=<?= $gruppe ?>">Enzyklopädie runterladen</a>
        </td>
    </tr>
</table>
</div>

<div id="bureau_admin_<?= $gruppe ?>_macht" style="display: none;">
</div>
