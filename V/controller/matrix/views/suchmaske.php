<?= Template::summon(dirname(__file__)."/breadcrumb.php")
        ->with("breadcrumb", $breadcrumb)
        ->with("matrix", $matrix)
        ->render()
?>

<div style="width: 100%; text-align: center">

    <h1>Suchen</h1>

    <form action="" onSubmit="TSC.matrix.searchArticle('<?= $matrix ?>', $('#matrix_suchwort').val()); return false;">
            <input id="matrix_suchwort" type="text" style="box-shadow:inset 2px 0px 4px #222222; margin: 0px; height: 30px; padding-left: 5px; width: 200px; border-top-left-radius: 5px; border-bottom-left-radius: 5px;"
            ><input type="submit" value="suchen" style="margin: 0px; height: 32px; border-top-right-radius: 5px; border-bottom-right-radius: 5px;">
    </form>

    <style>
    #matrix_suchmaske_buchstaben a {
        font-size: 1.4em;
    }
    </style>

    <table id="matrix_suchmaske_buchstaben" style="width: 100%;" class="fine_table">
        <tr>
            <? foreach (array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "#") as $buchstabe) : ?>
            <td class="lightable"><?= $buchstabe ?></td>
            <? endforeach ?>
        </tr>
    </table>
    <script>
    $(function () {
        $("#matrix_suchmaske_buchstaben > tbody > tr > td").bind("click", function () {
            TSC.matrix.searchLetter('<?= $matrix ?>', $(this).text());
        });
    });
    </script>
</div>

<div id="matrix_suchmaske_body">
    <div id="matrix_suchmaske_sidebox">
    Letzte Änderungen:
    <ul>
    <? foreach ($lastChanges as $key => $artikel) : ?>
        <li>
            <a onClick="TSC.matrix.openArticle('<?= $matrix ?>', '<?= str_replace("'", '%HOCHKOMMA%', escape($artikel)) ?>');">
                <?= $artikel ?>
            </a>
        </li>
    <? endforeach ?>
    </ul>
    </div>

    <div id="matrix_suchmaske_results">
    <? if ($results) : ?>
        Suchergebnisse:
        <ul>
        <? foreach ($results as $artikel) : ?>
            <li>
                <a onClick="TSC.matrix.openArticle('<?= $matrix ?>', '<?= str_replace("'", '%HOCHKOMMA%', escape($artikel)) ?>');">
                    <?= escape($artikel) ?>
                </a>
            </li>
        <? endforeach ?>
        </ul>
    <? endif ?>
    </div>
</div>

<div style="clear:both;"></div>