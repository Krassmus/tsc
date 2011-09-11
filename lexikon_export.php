<?php
#Anmeldung an der Datenbank
$config = parse_ini_file("V/config/.TSC.ini", true);

#Anmeldung an der Datenbank
$db_old = mysql_connect($config['db']['host'], $config['db']['user'], $config['db']['password'])
    or die("Keine Verbindung möglich: " . mysql_error());
mysql_select_db($config['db']['dbname'], $db_old) or die("Auswahl der Datenbank fehlgeschlagen");

#Zeichensatz von PHP und der Datenbank einstellen
//header("Content-Type: text/html; charset=UTF-8");
mysql_query("SET NAMES utf8");
setlocale(LC_TIME, 'de_DE.UTF8');

#Diese Seite wird mit Variablen
$artikel;
$matrix;
$buchstabe;
#aufgerufen.
session_register("wherecameifrom");
session_register("frommatrix");

//Eventuell alte Datei wegräumen:
if (file_exists("Enzyklopaedie.odt")) {
	unlink("Enzyklopaedie.odt");
}

if (!file_exists('export')) {
	mkdir ("export", 0777);
  $cleanup = true;
}
mkdir ("export/META-INF", 0777);
mkdir ("export/Thumbnails", 0777);
mkdir ("export/Pictures", 0777);

$manifest = fopen("export/META-INF/manifest.xml", "w");
fwrite($manifest, '<?xml version="1.0" encoding="UTF-8"?>
<manifest xmlns="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0">
  <file-entry media-type="application/vnd.oasis.opendocument.text" full-path="/"/>
  <file-entry media-type="application/vnd.sun.xml.ui.configuration" full-path="Configurations2/"/>
  <file-entry media-type="image/png" full-path="Pictures/10000000000001E800000118B5A37F3F.png"/>
  <file-entry media-type="" full-path="Pictures/"/>
  <file-entry media-type="text/xml" full-path="content.xml"/>
  <file-entry media-type="text/xml" full-path="styles.xml"/>
  <file-entry media-type="text/xml" full-path="meta.xml"/>
  <file-entry media-type="" full-path="Thumbnails/thumbnail.png"/>
  <file-entry media-type="" full-path="Thumbnails/"/>
  <file-entry media-type="text/xml" full-path="settings.xml"/>
</manifest>');
fclose($manifest);

$mimetype = fopen("export/mimetype", "w");
fwrite($mimetype, "application/vnd.oasis.opendocument.text");
fclose($mimetype);

$meta = fopen("export/meta.xml", "w");
fwrite($meta, '<?xml version="1.0" encoding="UTF-8"?>
<office:document-meta
    xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"
    xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:xlink="http://www.w3.org/1999/xlink">
  <office:meta>
    <meta:generator>OpenOffice.org/1.9.118$Win32 OpenOffice.org_project/680m118$Build-8936</meta:generator>
    <meta:initial-creator>Vorname Nachname</meta:initial-creator>
    <meta:creation-date>2005-09-27T16:53:48</meta:creation-date>
    <dc:creator>Vorname Nachname</dc:creator>
    <dc:date>2005-09-29T18:12:57</dc:date>
    <meta:printed-by>Vorname Nachname</meta:printed-by>
    <meta:print-date>2005-09-29T17:57:42</meta:print-date>
    <dc:language>de-DE</dc:language>
    <meta:editing-cycles>11</meta:editing-cycles>
    <meta:editing-duration>PT6H11M44S</meta:editing-duration>
    <meta:user-defined meta:name="Info 1"/>
    <meta:user-defined meta:name="Info 2"/>
    <meta:user-defined meta:name="Info 3"/>
    <meta:user-defined meta:name="Info 4"/>
    <meta:document-statistic
      meta:table-count="0"
      meta:image-count="4"
      meta:object-count="0"
      meta:page-count="5"
      meta:paragraph-count="92"
      meta:word-count="1460"
      meta:character-count="10405"/>
  </office:meta>
</office:document-meta>');
fclose($meta);

//zippen:
exec("zip ./export/ -R -o Enzyklopaedie.odt");

//Und wieder aufräumen:
rmdir("export/META-INF");
rmdir("export/Thumbnails");
rmdir("export/Pictures");
if ($cleanup) {
	rmdir("export/META-INF");
}

header("Location: Enzyklopaedie.odt");
//gelöscht werden muss beim nächsten Skriptaufruf.
?>