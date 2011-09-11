<?php
/*
 * Created on 17.05.2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require_once dirname(__file__)."/V/lib/DBManager.class.php";

$msg = array();
$empty_db = true;

//Config-Datei schreiben:
if (!file_exists(dirname(__file__)."/config.php") && $_REQUEST['db_type']) {
	$db = DBManager::create($_REQUEST['db_type'], $_REQUEST['db_name'], $_REQUEST['db_host'], $_REQUEST['db_user'], $_REQUEST['db_pass']);
  
  if ($db) {
		$text = '<?php

//database setup:
$DB_TYPE = "'.$_REQUEST['db_type'].'";       //only mysql works yet
$DB_HOST = "'.$_REQUEST['db_host'].'";
$DB_NAME = "'.$_REQUEST['db_name'].'";
$DB_USER = "'.$_REQUEST['db_user'].'";
$DB_PASSWORD = "'.$_REQUEST['db_pass'].'";

';
    $handler = fopen(dirname(__file__)."/config.php", "w");
    fwrite($handler, $text);
    fclose($handler);
    
    $empty_db = false;
    try {
      $result = $db->query("SELECT * FROM spieler")->fetch();
      $result = $db->query("SELECT * FROM forces")->fetch();
      $result = $db->query("SELECT * FROM gruppe")->fetch();
    } catch (Exception $e) {
    	$empty_db = true;
    }
  }
  
  if ($db && $empty_db) {
    $db->query(file_get_contents(dirname(__file__)."/V/config/db_structure.sql"));
  }
}

if (file_exists(dirname(__file__)."/config.php") && !$empty_db) {
	header("Location: index.php");
}

?>
<html>
<head>
<title>Initialisieren: TSC V Webmodul</title>
</head>
<body>
<?php
foreach($msg as $message) {
	print '<div class="msg">'.$message.'</div>';
}


if (!file_exists(dirname(__file__)."/config.php")) :
?>
<h1>Vernk&uuml;pfen Sie TSC mit der Datenbank</h1>

<form action="" method="post">
<table>
<tr>
  <td>Server / Host - Adresse</td><td><input type="text" name="db_host"></td>
</tr>
<tr>
  <td>Name der Datenbank f&uuml;r TSC</td><td><input type="text" name="db_name"></td>
</tr>
<tr>
  <td>Datenbanknutzer</td><td><input type="text" name="db_user"></td>
</tr>
<tr>
  <td>Passwort des Datenbanknutzers</td><td><input type="password" name="db_pass"></td>
</tr>
<tr>
  <td></td><td><input type="submit" value="Angaben speichern"></td>
</tr>
</table>
</form>

<?php else : ?>

<h1>Erste Daten</h1>

Ihre Datenbank ist zwar erkannt, aber noch leer. Vermutlich wollen Sie eine erste 
Gruppe gründen und einen Account als Spielleiter dieser Gruppe einrichten. 
Aber auf jeden Fall sollte die Datenbank ein Gerüst bekommen.

<form action="" method="post">
<table>
<tr>
  <td>Datenbankstruktur initialisieren</td><td><input type="checkbox" checked name="init"></td>
</tr>
<tr>
  <td>Spielleiteraccount</td><td><input type="text" name="init_master"></td>
</tr>
<tr>
  <td>Erste Gruppe</td><td><input type="text" name="init_gruppe"></td>
</tr>
<tr>
  <td></td><td><input type="submit" value="Datenbank initialisieren"></td>
</tr>
</table>
</form>

<? endif; ?>

</body>
</html>