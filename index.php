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
<script type="text/javascript" src="V/media/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="V/media/jquery-ui-1.8.5.custom.min.js"></script>
<style>
#loginwindow {
	opacity: .8; filter: alpha(opacity=80); 
	background-image: linear-gradient(top, #000000 0%, #222222 100%);
	background-image: -moz-linear-gradient(top, #000000 0%, #222222 100%);
	background-image: -o-linear-gradient(top, #000000 0%, #222222 100%);
	background-image: -webkit-linear-gradient(top, #000000 0%, #222222 100%);
	padding: 12px;
	border-radius: 8px;
	box-shadow: 0px 0px 50px #ffaaaa;
	position: absolute; 
	top: 300px; 
	left: 330px;
	text-align: center;
}
#loginwindow input {
	background-color: #050505;
	border: 1px solid #555555;
	margin: 3px;
}
</style>
</head>
<body onload="document.forms.logindata.login.focus()" style="background: black url(bilder/fractal_orb_by_aziznatour-d116ezt.jpg) no-repeat;"><!- Breite=900 ->

	<div id="loginwindow" style="display: none;">
		<?php
		if ($fehler)
		  print "Bitte noch einmal eingeben.";
		?>
		<form method="post" action="V/index.php" name="logindata">
		<input type="Text" name="login" id="login" placeholder="Nutzername"><br>
		<input type="Password" name="pass"><br>
		<input type="submit" value="einloggen">
		</form>
	</div>
	<script>
	$(function () {
		window.setTimeout(function () {
			$("#loginwindow").draggable().fadeIn(1500).find("#login").focus();
		}, 400);
	});
	</script>
	
	<? if (file_exists(dirname(__file__)."/impressum.php")) : ?>
	<div style="position: absolute; font-size: 0.7em; color: #666666; top: 800px; left: 0px; padding-top: 20px; padding-bottom: 20px; text-align: center; background-image: -moz-linear-gradient(top, #0c0c0c 0%, #1d1d1d 100%); width: 100%">
		<? include dirname(__file__)."/impressum.php" ?>
	</div>
	<? endif ?>
</body>
</html>