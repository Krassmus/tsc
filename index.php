<?php

session_start();
session_destroy();

if (!file_exists(dirname(__file__)."/config.php")) {
	header("Location: make.php");
}

?>
<html>
<head>
<title>TacticalSpaceCommunity</title>
<link rel="stylesheet" href="tsc.css" type="text/css">
<link rel="SHORTCUT ICON" href="V/media/images/favicon.ico">
</head>
<body onload="document.forms.logindata.login.focus()" style="background: black url(bilder/diffus.svg) no-repeat;"><!- Breite=900 ->
<table width=900>
<tr height=600><td width=70></td><td valign=top><center>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>

<img src="bilder/TSC.png"><br>
<br>
<div style="opacity: .6; filter: alpha(opacity=60);">
<?php
if ($fehler)
  print "Bitte noch einmal eingeben.<br>";
  else
  print "<br>";
?>
<form method="post" action="V/index.php" name="logindata">
<input type="Text" name="login"><br>
<input type="Password" name="pass"><br>
<input type="submit" value="einloggen">
</form>

<br>
<br>
<a href="Anzeichen eines Imperiums.mp3" target="_blank">Anzeichen eines<br>Imperiums</a><br>
<!--
<a href="lexikon.php">Enzyklopädie</a>
-->

</center></div></td></tr>
<tr><td colspan=3><center><span style="font-size: 8"><a href="antique/index.php" class="downers">Die Alte Welt</a></span></center></td><td width=500></td></tr>
</table>


</body>
</html>