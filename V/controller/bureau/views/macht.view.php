<? //Bild der Macht ?>
<div style="text-align: center; width: 100%;">
    <input type="button" value="up" onClick="TSC.bureau.adminUebersicht('<?= $gruppe ?>');">
</div>
<h2>Macht: <?= Forces::id2name($macht) ?></h2>
<table class="fine_table">
<tr>
<td>Kontakte: </td>
<td>
<?php
foreach($nachbarn as $nachbar)
  {
  print force2name($nachbar).' <input type="checkbox" ';
  print 'name="macht_'.$_REQUEST['macht'].'_'.$nachbar.'" ';
  print 'onChange="TSC.bureau.admin_setcontact('."'".$gruppe."'".', '."'".$macht."'".', '."'".$nachbar."'".', this)"';
  if (in_array($nachbar, $kontakte))
    print ' checked';
  print '><br>';
  }
?>
</td>
</tr>

<tr>
<td>Spieler: </td>
<td>
<?php
foreach($spieler as $sp) {
    print escape($sp).' <input type="button" value="entlassen" onClick=""><br>';
}
?>
</td>
</tr>

<tr>
<td>Macht übertragen an: </td>
<td>
<select id="bureau_admin_<?= $gruppe ?>_verwalten_machtgeben1">
<?php
foreach($allespieler as $sp) {
    print '<option value="'.$sp.'">'.$sp.'</option>';
}
?>
</select> <input type="button" value="hinzufügen" onClick="TSC.bureau.admin_give_force_to('<?= $gruppe ?>', '<?= $macht ?>', $('#bureau_admin_<?= $gruppe ?>_verwalten_machtgeben1').val());">
</td>
</tr>

<tr>
<td>Fremden Spieler</td>
<td> <input type="text" id="bureau_admin_<?= $gruppe ?>_verwalten_machtgeben2"> <input type="button" value="zur Macht hinzufügen" onClick="TSC.bureau.admin_give_force_to('<?= $gruppe ?>', '<?= $macht ?>', $('#bureau_admin_<?= $gruppe ?>_verwalten_machtgeben2').val());"> </td>
</tr>

</table>