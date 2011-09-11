<?php
#Anmeldung an der Datenbank
$config = parse_ini_file("V/config/.TSC.ini", true);

#Anmeldung an der Datenbank
$db_old = mysql_connect($config['db']['host'], $config['db']['user'], $config['db']['password'])
    or die("Keine Verbindung möglich: " . mysql_error());
mysql_select_db($config['db']['dbname'], $db_old) or die("Auswahl der Datenbank fehlgeschlagen");

#Zeichensatz von PHP und der Datenbank einstellen
header("Content-Type: text/html; charset=UTF-8");
mysql_query("SET NAMES utf8");
setlocale(LC_TIME, 'de_DE.UTF8');

#Diese Seite wird mit Variablen
$artikel;
$matrix;
$buchstabe;
#aufgerufen.
session_register("wherecameifrom");
session_register("frommatrix");
?>
<html>
<head>
<title>TSC - Enzyklop&auml;die</title>
<link rel="stylesheet" href="tsc.css" type="text/css">
<style type="text/css">
<!--
h3
 {
 font-family: Aero, Arial;
 font-weight: normal;
 color: #FFAD00;
 font-size: 14;
 }
h2
 {
 font-family: Aero, Arial;
 font-weight: normal;
 color: #FFAD00;
 font-size: 16;
 }
h1
 {
 font-family: Aero, Arial;
 font-weight: normal;
 color: #FFAD00;
 font-size: 18;
 }
body
 {
 font-family: OCR A Extended, Andale Mono, Lucida Sans Typewriter, Silom, Fixedsys, MONOSPACE;
 font-size: 12;
 }
input
 {
 font-family: OCR A Extended, Andale Mono, Lucida Sans Typewriter, Silom, Fixedsys, MONOSPACE;
 }
select
 {
 font-family: OCR A Extended, Andale Mono, Lucida Sans Typewriter, Silom, Fixedsys, MONOSPACE;
 }
textarea
 {
 font-family: OCR A Extended, Andale Mono, Lucida Sans Typewriter, Silom, Fixedsys, MONOSPACE;
 font-size: 12;
 }
td
 {
 font-size: 12;
 }
th
 {
 font-size: 10;
 }

-->
</style>
<script language="JavaScript">
<!--
function flip(id)
  {
  window.document.all[id].style.backgroundImage = 'url(V/bilder/back_2.jpg)';
  window.setTimeout("window.document.all['"+id+"'].style.backgroundImage = 'url(V/bilder/back_3.jpg)'", 40);
  window.setTimeout("window.document.all['"+id+"'].style.backgroundImage = 'url(V/bilder/back_4.jpg)'", 80);
  }
function flop(id)
  {
  window.setTimeout("window.document.all['"+id+"'].style.backgroundImage = 'url(V/bilder/back_3.jpg)'", 0);
  window.setTimeout("window.document.all['"+id+"'].style.backgroundImage = 'url(V/bilder/back_2.jpg)'", 40);
  window.setTimeout("window.document.all['"+id+"'].style.backgroundImage = 'url(V/bilder/back.jpg)'", 80);
  }

var alltherows = new Array();
var isrowlightened = new Array();
function canyouhandlethis(e)
  {
  var i;
  var welchezeile = -1;
  var element = e.target || e.srcElement;
  // -> Zuerst schauen wir, ob das event.target Teil einer Tabellenzeile ist
  while ((element.parentNode != null) ? ((element.getAttribute('class') != "lightable") ? true : false) : false)
    element = element.parentNode;
  // -> Wenn ja, schauen wir in alltherows nach, ob wir die Zeile schon kennen.
  if (element.parentNode != null)
    {
    if (element.getAttribute('class') == "lightable")
      {
      for (i=0; i < alltherows.length; i++)
        if (alltherows[i] == element.id)
          {
          welchezeile = i;
          break;
          }
      // -> Wenn nein, wird sie zu alltherows hinzugefuegt: alltherows.push();
      if (welchezeile == -1)
        {
        alltherows.push(element.id);
        // -> Das hilft aber nur, wenn wir auch isrowlightened.push(true) gesetzt wird.
        isrowlightened.push(true);
        //    Und jetzt muss das event.target folgerichtig auch sein highlighting bekommen.
        flip(element.id);
        welchezeile = alltherows.length-1;
        } else {
        // -> Falls die Zeile schon bekannt ist UND noch nicht highlighted, wird sie
        //    angeknipst und isrowlightened[i] auf true gesetzt.
        if (isrowlightened[i] == false)
          {
          isrowlightened[i] = true;
          flip(element.id);
          }
        }
      }
    }
  // -> Und nun werden alle ANDEREN Zeilen in alltherows dunkel gemacht, wenn
  //    das parallele isrowlightened[i] true gesetzt ist. Setze es dafuer auch auf false.
  for (i=0; i < alltherows.length; i++)
    if ((i != welchezeile) && (isrowlightened[i] == true))
      {
      isrowlightened[i] = false;
      flop(alltherows[i]);
      }
  }
//-->
</script>
</head>
<body style="background-image: url(antique/bilder/chromosom.jpg); background-repeat: no-repeat; background-position: 306px 0px;" onmousemove="canyouhandlethis(event)">

<br>
<br>
<br>

<table width=900>
<tr>
<td width=100></td>
<td style="opacity: .9; filter: alpha(opacity=90); background-image: url(V/bilder/back.jpg); border: thin solid #555555;">

<table border=0>
<tr>
<td width=75> </td><td width=550>
<br>
<?php

function artikel_getpic($picture, $matrix)
  {
  $result = mysql_query("SELECT filename FROM bilder WHERE matrix = $matrix AND filename = CONCAT(id, '_', '$picture') ORDER BY date ASC");
  //HAVING LOCATE('".$picture."',filename)!=0
  $row = mysql_fetch_row($result);
  return $row[0];
  }
function artikel_pic_parameters($par, $matrix)
  {
  $par = explode(":", $par);
  $add = "";
  $styled = false;
  for ($i=0; $i < count($par); $i++)
    {
    if ((!is_numeric($par[$i])) && ($styled == false))
      {
      $styled = true;
      $add .= ' style="';
      for ($j=$i; $j < count($par); $j++)
        {
        if ($par[$j] == "right")
          $add .= 'float:right;';
        if ($par[$j] == "left")
          $add .= 'float:left;';
        if ( ($par[$j] != "right") && ($par[$j] != "left") && (!is_numeric($par[$j])) )
          $add .= 'cursor:pointer;';
        }
      $add .= '"';
      }
    if (is_numeric($par[$i]))
      $add .= ' width='.$par[$i];
    if ( ($par[$i] != "right") && ($par[$i] != "left") && (!is_numeric($par[$i])) )
      {
      $result = mysql_query("SELECT name FROM matrix WHERE name = '".$par[$i]."' AND gruppe = '$matrix' LIMIT 1");
      if ($row = mysql_fetch_row($result))
        $add .= 'title="'.$row[0].'" onClick="getContent('."'matrix_".$matrix."', 'matrix/artikel.php?page=".$row[0]."&matrix=$matrix&SID=".session_id()."'".')"';
      }
    }
  return $add;
  }

$tablecount = 1;
function artikel_gettable($tabletext)
  {
  global $tablecount;
  global $matrix;
  $tabletext = explode("\n||", $tabletext);
  $invisible = false;
  $active = false;
  $active_row = false;
  $header = false;
  $center = false;
  $close = false;
  $tight = false;
  $fusion_dimension = array();
  $rowspan = array();
  $colspan = array();
  $text = "";
  if ($tabletext[0][0] != "|")
    {
    $text = '<br><table width=100% border=1 cellpadding=3 style="border-collapse: collapse">';
    } else {
    //Erste Zeile mit "|||" gibt Formatierungsangaben der Tabelle an.
    //Es koennen beliebig viele Formatierungsangaben angegeben werden.
    //Nichtschluesselwoerter werden ignoriert.
    $tabletext[0] = substr($tabletext[0], 1);
    $head = explode(" ", $tabletext[0]);
    for ($i=0; $i < count($head); $i++)
      {
      if ($head[$i] == "invisible") $invisible = true;     //keine Umrandungen
      if ($head[$i] == "active") $active = true;           //Zellen leuchten auf
      if ($head[$i] == "active-row") $active_row = true;   //Zeilen leuchten auf
      if ($head[$i] == "header") $header = true;           //Erste Zeile ist Spaltenbeschriftung
      if ($head[$i] == "center") $center = true;           //Ausrichtung in der Zellenmitte
      if ($head[$i] == "close") $close = true;             //Die Tabelle liegt dicht am umgebenden Text
      if ($head[$i] == "tight") $tight = true;             //Die Zellentexte haben keinen Abstand zum Rand
      if (substr($head[$i], 0, 6) == "fusion")             //Zellen nehmen mehr Platz ein.
        {
        $head[$i] = explode("_", $head[$i]);
        $fusion_dimension[0] = $head[$i][1];
        $fusion_dimension[1] = $head[$i][2];
        $fusion_dimension[2] = $head[$i][3];
        if (!$fusion_dimension[2]) $fusion_dimension[2] = 1;
        array_push($colspan, $fusion_dimension);
        }
      if (substr($head[$i], 0, 14) == "vfusion")           //Zellen nehmen vertical mehr Platz ein.
        {
        $head[$i] = explode("_", $head[$i]);
        $fusion_dimension[0] = $head[$i][1];
        $fusion_dimension[1] = $head[$i][2];
        $fusion_dimension[2] = $head[$i][3];
        if (!$fusion_dimension[2]) $fusion_dimension[2] = 1;
        array_push($rowspan, $fusion_dimension);
        }
      }
    if ($active_row || $active_col) $active = false;
    //Jetzt die Formatierungen:
    if (!$close)
      $text .= '<br><table width=100%';
      else
      $text = '<table width=100%';
    if (!$invisible)
      $text .= ' border=1';
      else
      $text .= ' border=0';
    if ($tight)
      $text .= ' cellpadding=0';
      else
      $text .= ' cellpadding=3';
    $text .= ' style="border-collapse: collapse">';
    array_shift($tabletext);
    }
  if ($header)
    //Jetzt wird die Kopfzeile der Tabelle geschrieben
    {
    $headline = array_shift($tabletext);
    $headline = explode(" || ", $headline);
    $text .= '<tr>';
    for ($i=0; $i < count($headline); $i++)
      {
      //Die einzelnen Zellen der Kopfzeile
      $text .= '<th';
      if (!$invisible) $text .= ' style="border: thin solid #444444"';
      for ($k=0; $k < count($rowspan); $k++)
        {
        if (($rowspan[$k][0] == $i+1) && ($rowspan[$k][1] == 1))
          {
          $text .= ' rowspan="'.($rowspan[$k][2]+1).'"';
          }
        }
      for ($k=0; $k < count($colspan); $k++)
        {
        if (($colspan[$k][0] == $i+1) && ($colspan[$k][1] == 1))
          {
          $text .= ' colspan="'.($colspan[$k][2]+1).'"';
          }
        }
      $text .= '>'.str_replace("||", "", $headline[$i]).'</th>';
      }
    $text .= '</tr>';
    }
  for ($i=0; $i < count($tabletext); $i++)
    {
    //Und jetzt wird die eigentliche Tabelle geschrieben.
    $text .= '<tr id="mx_tbl_'.$matrix.'_'.$tablecount.'_row_'.$i.'"';
    if ($active_row)
      $text .= ' class="lightable"';
    $text .= '>';
    $tabletext[$i] = explode(" || ", $tabletext[$i]);
    for ($j=0; $j < count($tabletext[$i]); $j++)
      {
      $text .= '<td';
      if (!$center) $text .= ' valign=top';
      $text .= ' id="mx_tbl_'.$matrix.'_'.$tablecount.'_'.$j.'_'.$i.'"';
      if (!$invisible) $text .= ' style="border: thin solid #444444"';
      if (($active) && (!$active_row))
        {
        $text .= ' class="lightable"';
        }
      for ($k=0; $k < count($rowspan); $k++)
        {
        if (($rowspan[$k][0] == $j+1) && ($rowspan[$k][1] == $i+1+($header ? +1 : 0)))
          {
          $text .= ' rowspan="'.($rowspan[$k][2]+1).'"';
          }
        }
      for ($k=0; $k < count($colspan); $k++)
        {
        if (($colspan[$k][0] == $j+1) && ($colspan[$k][1] == $i+1+($header ? +1 : 0)))
          {
          $text .= ' colspan="'.($colspan[$k][2]+1).'"';
          }
        }
      $text .= '>';
      if ($center) $text .= '<center>';
      $text .= str_replace("||", "", $tabletext[$i][$j]);
      if ($center) $text .= '</center>';
      $text .= "</td>";
      }
    $text .= "</tr>";
    }
  $text .= "</table>";
  if (!$close) $text .= "<br>\n";
  $tablecount++;
  return $text;
  }

function artikel_makelist($listtext, $stage)
  {
  $listtext = explode("\n", $listtext);
  $text = "<ul";
  if ($stage == 1) $text .= ' type="circle"';
  if ($stage == 2) $text .= ' type="disc"';
  if ($stage == 3) $text .= ' type="square"';
  if ($stage == 4) $stage = 0;
  $text .= ">";
  for ($i=0; $i < count($listtext); $i++)
    $listtext[$i] = substr($listtext[$i], 1);
  for ($i=0; $i < count($listtext); $i++)
    {
    if ($listtext[$i][0] != "-")
      {
      if ($listtext[$i][0] != "+")
        {
        $text .= "<li>".$listtext[$i];
        } else {
        $new_num = $listtext[$i];
        $i++;
        while ($listtext[$i][0] == "+")
          {
          $new_num .= "\n".$listtext[$i];
          $i++;
          }
        $text .= artikel_makenum($new_num, 0);
        $i--;
        }
      } else {
      $new_list = $listtext[$i];
      $i++;
      while ($listtext[$i][0] == "-")
        {
        $new_list .= "\n".$listtext[$i];
        $i++;
        }
      $text .= artikel_makelist($new_list, $stage+1);
      $i--;
      }
    }
  $text .= "</ul>";
  return $text;
  }
function artikel_makenum($numtext, $stage)
  {
  $numtext = explode("\n", $numtext);
  $text = "<ol";
  if ($stage == 1) $text .= ' type="A"';
  if ($stage == 2) $text .= ' type="a"';
  if ($stage == 3) $text .= ' type="I"';
  if ($stage == 4) $text .= ' type="i"';
  if ($stage == 5) $stage = 0;
  $text .= ">";
  for ($i=0; $i < count($numtext); $i++)
    $numtext[$i] = substr($numtext[$i], 1);
  for ($i=0; $i < count($numtext); $i++)
    {
    if ($numtext[$i][0] != "+")
      {
      if ($numtext[$i][0] != "-")
        {
        $text .= "<li>".$numtext[$i];
        } else {
        $new_list = $numtext[$i];
        $i++;
        while ($numtext[$i][0] == "-")
          {
          $new_list .= "\n".$numtext[$i];
          $i++;
          }
        $text .= artikel_makelist($new_list, 0);
        $i--;
        }
      } else {
      $new_num = $numtext[$i];
      $i++;
      while ($numtext[$i][0] == "+")
        {
        $new_num .= "\n".$numtext[$i];
        $i++;
        }
      $text .= artikel_makenum($new_num, $stage+1);
      $i--;
      }
    }
  $text .= "</ol>";
  return $text;
  }

$discussioncount = 0;
function artikel_discuss($topic, $matrix)
  {
  }

function artikel_makeheaders($text)
  {
  return "<h2>".str_replace(" ", "&nbsp;", $text)."</h2>";
  }

function artikel_tsc_code_parser($text, $title, $matrix, $depth)
  {
  $text = str_replace("~", "LONGSNAKE", $text);
  $text = htmlentities($text, ENT_NOQUOTES, "UTF-8");
  $text = preg_replace('/\{\{([^~]+?)\}\}/e', "artikel_tsc_code_parser('\\1', '$title', $matrix, ".($depth+1).")", $text);
  $text = preg_replace('/\n\|\|([^~]+?)\|\|\n\n/e', "artikel_gettable('\\1')", $text);
  $text = preg_replace('/\%\%([^~]+?)\%\%/', '<i>$1</i>', $text);
  $text = preg_replace('/\&sect;\&sect;(.*?)\&sect;\&sect;/e', "artikel_discuss('\\1', $matrix)", $text);
  $text = preg_replace('/\n\n!!(.*?)\n\n/e', "artikel_makeheaders('\\1')", $text);
  $text = preg_replace('/\n!!(.*?)\n\n/e', "artikel_makeheaders('\\1')", $text);
  $text = preg_replace('/\n\n!!(.*?)\n/e', "artikel_makeheaders('\\1')", $text);
  $text = preg_replace('/\n!!(.*?)\n/e', "artikel_makeheaders('\\1')", $text);
  $text = preg_replace('/!!(.*?)\n/e', "artikel_makeheaders('\\1')", $text);
  $text = preg_replace('/\n\-([^<]+?)\n\n/e', "artikel_makelist('\\1', 0)", $text);
  $text = preg_replace('/\n\+([^<]+?)\n\n/e', "artikel_makenum('\\1', 0)", $text);
  $text = preg_replace("/\[img\:(\w[\w|\:|\.|\-]+)\](\w[\w|\.|\-]+)/e", "'<img src=".'"'."V/bilder/matrix/'.artikel_getpic('\\2', ".$matrix.").'".'"'." '.artikel_pic_parameters('\\1', $matrix).'>'", $text);
  $text = preg_replace("/\[img\](\w[\w|\.|\-]+)/e", "'<img src=".'"'."V/bilder/matrix/'.artikel_getpic('\\1', ".$matrix.").'".'"'.">'", $text);
  $text = nl2br($text);
  $result = mysql_query("SELECT DISTINCT name FROM matrix WHERE gruppe = $matrix AND name != '$title' ORDER BY CHAR_LENGTH(name) DESC");
  while ($row = mysql_fetch_row($result))
    {
    $text = str_replace(" ".$row[0], ' <a href="lexikon.php?matrix='.$matrix.'&artikel='.str_replace(" ", "&nbsp;", $row[0]).'">'.str_replace(" ", "&nbsp;", $row[0]).'</a>', $text);
    }
  $text = str_replace("\n", "", $text);
  $text = str_replace ("\\", "", $text);
  $text = str_replace("LONGSNAKE", "~", $text);
  $text = str_replace("&nbsp;", " ", $text);
  return $text;
  }

if (!$wherecameifrom)
  $wherecameifrom = array("SUCHMASKE");
if (!$frommatrix)
  $frommatrix = array(0);
if ($wherecameifrom[count($wherecameifrom)-1] == (($artikel) ? $artikel : "SUCHMASKE"))
  array_pop($wherecameifrom);
if ($frommatrix[count($frommatrix)-1] == (($matrix) ? $matrix : 0))
  array_pop($frommatrix);
if (count($wherecameifrom) > 0)
  print '<span style="font-size: 10">';
for ($i = count($wherecameifrom); $i > 0; $i--)
  {
  if ($i < count($wherecameifrom)) print " &lt; ";
  print '<a href="lexikon.php?matrix='.(($frommatrix[$i-1] != 0) ? $frommatrix[$i-1] : 0).'&artikel='.(($wherecameifrom[$i-1] != "SUCHMASKE") ? $wherecameifrom[$i-1] : '').'">';
  print (($wherecameifrom[$i-1] != "SUCHMASKE") ? $wherecameifrom[$i-1] : "Suchmaske").'</a>';
  }
if (count($wherecameifrom) > 0)
  print '</span><br>';
//if ((count($wherecameifrom) == 0) || ($wherecameifrom[count($wherecameifrom)-1] != $page))
if (true)
  {
  array_push($wherecameifrom, (($artikel) ? $artikel : "SUCHMASKE"));
  if (count($wherecameifrom) > 5) array_shift($wherecameifrom);
  array_push($frommatrix, (($matrix) ? $matrix : 0));
  if (count($frommatrix) > 5) array_shift($frommatrix);
  }

if (($matrix) && ($artikel) && ($artikel != "SUCHMASKE"))
  {
  $result = mysql_query("SELECT m.eintrag, m.bild FROM `matrix` m, `gruppe` g WHERE g.id = m.gruppe AND g.freigegeben = 1 AND m.gruppe = $matrix AND m.name = '$artikel' ORDER BY m.version DESC LIMIT 1");
  $row = mysql_fetch_row($result);
  print '<center><h1>'.$artikel.'</h1></center>';
  if ($row[1])
    {
    $size = getimagesize("V/bilder/matrix/".$row[1]);
    print '<span style="float:right"><img src="V/bilder/matrix/'.$row[1].'"';
    if ($size[1] > $size[0])
      {
      if ($size[1] > 200)
        print " height=200";
      } else {
      if ($size[0] > 200)
        print " width=200";
      }
    print "></span>";
    }
  print artikel_tsc_code_parser($row[0], $artikel, $matrix, 1);
  } else {
  print "<center><h2>Suchen</h2>";

  print '<form action="lexikon.php">
<input type="Text" name="suchenach" size=40 value="'.$suchenach.'" style="-moz-border-radius-topright: 0px; -moz-border-radius-bottomright: 0px; -webkit-border-top-right-radius: 0px; -webkit-border-bottom-right-radius: 0px;">
<input type=submit value="suchen" style="-moz-border-radius-topleft: 0px; -moz-border-radius-bottomleft: 0px; -webkit-border-top-left-radius: 0px; -webkit-border-bottom-left-radius: 0px;">
</form>';

  print '<table width=550 border=0 style="border-collapse: collapse">
<tr>
<td id="suchen_buchstabe_a" class="lightable" onClick="location.href='."'lexikon.php?buchstabe=A&SID=".session_id()."'".'" width=* style="cursor: pointer; font-family: Aero, Arial;
 color: #FFAD00;
 font-size: 16;
 "><center>A</center></td>
<td id="suchen_buchstabe_b" class="lightable" onClick="location.href='."'lexikon.php?buchstabe=B&SID=".session_id()."'".'" width=* style="cursor: pointer; font-family: Aero, Arial;
 color: #FFAD00;
 font-size: 16;
 "><center>B</center></td>
<td id="suchen_buchstabe_c" class="lightable" onClick="location.href='."'lexikon.php?buchstabe=C&SID=".session_id()."'".'" width=* style="cursor: pointer; font-family: Aero, Arial;
 color: #FFAD00;
 font-size: 16;
 "><center>C</center></td>
<td id="suchen_buchstabe_d" class="lightable" onClick="location.href='."'lexikon.php?buchstabe=D&SID=".session_id()."'".'" width=* style="cursor: pointer; font-family: Aero, Arial;
 color: #FFAD00;
 font-size: 16;
 "><center>D</center></td>
<td id="suchen_buchstabe_e" class="lightable" onClick="location.href='."'lexikon.php?buchstabe=E&SID=".session_id()."'".'" width=* style="cursor: pointer; font-family: Aero, Arial;
 color: #FFAD00;
 font-size: 16;
 "><center>E</center></td>
<td id="suchen_buchstabe_f" class="lightable" onClick="location.href='."'lexikon.php?buchstabe=F&SID=".session_id()."'".'" width=* style="cursor: pointer; font-family: Aero, Arial;
 color: #FFAD00;
 font-size: 16;
 "><center>F</center></td>
<td id="suchen_buchstabe_g" class="lightable" onClick="location.href='."'lexikon.php?buchstabe=G&SID=".session_id()."'".'" width=* style="cursor: pointer; font-family: Aero, Arial;
 color: #FFAD00;
 font-size: 16;
 "><center>G</center></td>
<td id="suchen_buchstabe_h" class="lightable" onClick="location.href='."'lexikon.php?buchstabe=H&SID=".session_id()."'".'" width=* style="cursor: pointer; font-family: Aero, Arial;
 color: #FFAD00;
 font-size: 16;
 "><center>H</center></td>
<td id="suchen_buchstabe_i" class="lightable" onClick="location.href='."'lexikon.php?buchstabe=I&SID=".session_id()."'".'" width=* style="cursor: pointer; font-family: Aero, Arial;
 color: #FFAD00;
 font-size: 16;
 "><center>I</center></td>
<td id="suchen_buchstabe_j" class="lightable" onClick="location.href='."'lexikon.php?buchstabe=J&SID=".session_id()."'".'" width=* style="cursor: pointer; font-family: Aero, Arial;
 color: #FFAD00;
 font-size: 16;
 "><center>J</center></td>
<td id="suchen_buchstabe_k" class="lightable" onClick="location.href='."'lexikon.php?buchstabe=K&SID=".session_id()."'".'" width=* style="cursor: pointer; font-family: Aero, Arial;
 color: #FFAD00;
 font-size: 16;
 "><center>K</center></td>
<td id="suchen_buchstabe_l" class="lightable" onClick="location.href='."'lexikon.php?buchstabe=L&SID=".session_id()."'".'" width=* style="cursor: pointer; font-family: Aero, Arial;
 color: #FFAD00;
 font-size: 16;
 "><center>L</center></td>
<td id="suchen_buchstabe_m" class="lightable" onClick="location.href='."'lexikon.php?buchstabe=M&SID=".session_id()."'".'" width=* style="cursor: pointer; font-family: Aero, Arial;
 color: #FFAD00;
 font-size: 16;
 "><center>M</center></td>
<td id="suchen_buchstabe_n" class="lightable" onClick="location.href='."'lexikon.php?buchstabe=N&SID=".session_id()."'".'" width=* style="cursor: pointer; font-family: Aero, Arial;
 color: #FFAD00;
 font-size: 16;
 "><center>N</center></td>
<td id="suchen_buchstabe_o" class="lightable" onClick="location.href='."'lexikon.php?buchstabe=O&SID=".session_id()."'".'" width=* style="cursor: pointer; font-family: Aero, Arial;
 color: #FFAD00;
 font-size: 16;
 "><center>O</center></td>
<td id="suchen_buchstabe_p" class="lightable" onClick="location.href='."'lexikon.php?buchstabe=P&SID=".session_id()."'".'" width=* style="cursor: pointer; font-family: Aero, Arial;
 color: #FFAD00;
 font-size: 16;
 "><center>P</center></td>
<td id="suchen_buchstabe_q" class="lightable" onClick="location.href='."'lexikon.php?buchstabe=Q&SID=".session_id()."'".'" width=* style="cursor: pointer; font-family: Aero, Arial;
 color: #FFAD00;
 font-size: 16;
 "><center>Q</center></td>
<td id="suchen_buchstabe_r" class="lightable" onClick="location.href='."'lexikon.php?buchstabe=R&SID=".session_id()."'".'" width=* style="cursor: pointer; font-family: Aero, Arial;
 color: #FFAD00;
 font-size: 16;
 "><center>R</center></td>
<td id="suchen_buchstabe_s" class="lightable" onClick="location.href='."'lexikon.php?buchstabe=S&SID=".session_id()."'".'" width=* style="cursor: pointer; font-family: Aero, Arial;
 color: #FFAD00;
 font-size: 16;
 "><center>S</center></td>
<td id="suchen_buchstabe_t" class="lightable" onClick="location.href='."'lexikon.php?buchstabe=T&SID=".session_id()."'".'" width=* style="cursor: pointer; font-family: Aero, Arial;
 color: #FFAD00;
 font-size: 16;
 "><center>T</center></td>
<td id="suchen_buchstabe_u" class="lightable" onClick="location.href='."'lexikon.php?buchstabe=U&SID=".session_id()."'".'" width=* style="cursor: pointer; font-family: Aero, Arial;
 color: #FFAD00;
 font-size: 16;
 "><center>U</center></td>
<td id="suchen_buchstabe_v" class="lightable" onClick="location.href='."'lexikon.php?buchstabe=V&SID=".session_id()."'".'" width=* style="cursor: pointer; font-family: Aero, Arial;
 color: #FFAD00;
 font-size: 16;
 "><center>V</center></td>
<td id="suchen_buchstabe_w" class="lightable" onClick="location.href='."'lexikon.php?buchstabe=W&SID=".session_id()."'".'" width=* style="cursor: pointer; font-family: Aero, Arial;
 color: #FFAD00;
 font-size: 16;
 "><center>W</center></td>
<td id="suchen_buchstabe_x" class="lightable" onClick="location.href='."'lexikon.php?buchstabe=X&SID=".session_id()."'".'" width=* style="cursor: pointer; font-family: Aero, Arial;
 color: #FFAD00;
 font-size: 16;
 "><center>X</center></td>
<td id="suchen_buchstabe_y" class="lightable" onClick="location.href='."'lexikon.php?buchstabe=Y&SID=".session_id()."'".'" width=* style="cursor: pointer; font-family: Aero, Arial;
 color: #FFAD00;
 font-size: 16;
 "><center>Y</center></td>
<td id="suchen_buchstabe_z" class="lightable" onClick="location.href='."'lexikon.php?buchstabe=Z&SID=".session_id()."'".'" width=* style="cursor: pointer; font-family: Aero, Arial;
 color: #FFAD00;
 font-size: 16;
 "><center>Z</center></td>
</tr>';

  if ($buchstabe)
    {
    print "<tr>";
    $result = mysql_query("SELECT DISTINCT m.name, m.gruppe FROM `matrix` m, `gruppe` g WHERE LEFT(m.name, 1) = '$buchstabe' AND m.gruppe = g.id AND g.freigegeben = 1 ORDER BY m.name ASC");
    if (!$result)
      {
      print "Fehler: ".mysql_error();
      }
    if (mysql_num_rows($result) == 0)
      print '<td colspan=26 style="padding-top:8">Keine Artikel zum Buchstaben '.$buchstabe.' gefunden.</td>';
      else {
      print '<td colspan=26 style="padding-top:8">';
      while ($row = mysql_fetch_row($result))
        print '<a href="lexikon.php?matrix='.$row[1].'&artikel='.$row[0].'&SID='.session_id().'">'.$row[0].'</a><br>';
      print "</td>";
      }
    print "</tr>";
    }
  print '</table></center><br><br>';

  print '<table border=0 width=550><tr><td width=30% valign=top>
  <h2>Letzte &Auml;nderungen</h2>';

  $result = mysql_query("SELECT m.name, max(m.version), m.gruppe FROM `matrix` m, `gruppe` g WHERE g.id = m.gruppe AND g.freigegeben = 1 GROUP BY m.name ORDER BY max(m.version) DESC LIMIT 10");
  while ($row = mysql_fetch_row($result))
    {
    print '<a href="lexikon.php?matrix='.$row[2].'&artikel='.$row[0].'&SID='.session_id().'">'.$row[0].'</a><br>';
    }
  print '</td><td width=40% valign=top>';

  if ($suchenach)
    {
    print "<h2>Suchergebnisse</h2>
<ol>";
    $result = mysql_query("SELECT DISTINCT m.name, m.gruppe FROM `matrix` m, `gruppe` g WHERE g.id = m.gruppe AND g.freigegeben = 1 HAVING LOCATE('".$suchenach."',m.name)!=0 ORDER BY m.version DESC");
    if (mysql_num_rows($result) == 0)
      print "<li>Keine Ergebnisse im Namen</li>";
    while ($row = mysql_fetch_row($result))
      {
      print '<li><a href="lexikon.php?matrix='.$row[1].'&artikel='.$row[0].'&SID='.session_id().'">'.$row[0].'</a></li>';
      }
    print "</ol>";

    print "<h2>Treffer im Text</h2>
<ol>";
    $result = mysql_query("SELECT m.name, m.version, m.eintrag, m.gruppe FROM `matrix` m, `gruppe` g WHERE g.id = m.gruppe AND g.freigegeben = 1 HAVING LOCATE('".$suchenach."', m.eintrag)!=0 ORDER BY m.version DESC");
    if (mysql_num_rows($result) == 0)
      print "<li>Keine Ergebnisse im Text</li>";
    while ($row = mysql_fetch_row($result))
      {
      $checkifalreadyin = false;
      for ($i=0; $i < count($names); $i++)
        {
        if ($names[$i] == $row[0])
          $checkifalreadyin = true;
        }
      if ($checkifalreadyin == false)
        {
        $a[0] = $row[0];
        $a[1] = $row[1];
        $names[] = $a;
        }
      }
    for($i=0; $i < count($names); $i++)
      {
      $result = mysql_query("SELECT version, gruppe FROM matrix WHERE name='".$names[$i][0]."' ORDER BY version DESC LIMIT 1");
      $row = mysql_fetch_row($result);
      if ($row[0] == $names[$i][1])
        print '<li><a href="lexikon.php?matrix'.$row[1].'artikel='.$names[$i][0].'&SID='.session_id().'">'.$names[$i][0].'</a></li>';
      }
    print "</ol>";
    }
  print '</td><td width=30% valign=top>';

  $bild = array();
  function scanforpictures($picture, $matrix)
    {
    global $bild;
    $result = mysql_query("SELECT filename FROM bilder WHERE matrix = $matrix AND filename = CONCAT(id, '_', '$picture') ORDER BY date ASC");
    $row = mysql_fetch_row($result);
    array_push($bild, $row[0]);
    return $row[0];
    }

  $result = mysql_query("SELECT DISTINCT m.name, m.gruppe FROM `matrix` m, `gruppe` g WHERE g.id = m.gruppe AND g.freigegeben = 1 AND m.bild != '' OR LOCATE('[img', m.eintrag) != 0 ORDER BY RAND()");
  if (!$result)
    {
    print "Fehler: ".mysql_error();
    }
  $gefunden = false;
  while (!$gefunden)
    {
    if ($row = mysql_fetch_row($result))
      $gefunden = true;
    $result2 = mysql_query("SELECT name, bild, eintrag, gruppe FROM matrix WHERE gruppe='".$row[1]."' AND name = '".$row[0]."' ORDER BY version DESC LIMIT 1");
    $row = mysql_fetch_row($result2);
    //Text nach Bildern scannen und zufaellig eines auswaehlen
    $row[2] = preg_replace("/\[img\:(\w[\w|\:|\.|\-]+)\](\w[\w|\.|\-]+)/e", "'<img src=".'"'."V/bilder/matrix/'.scanforpictures('\\2', ".$row[3].").'".'"'." >'", $row[2]);
    $row[2] = preg_replace("/\[img\](\w[\w|\.|\-]+)/e", "'<img src=".'"'."V/bilder/matrix/'.scanforpictures('\\1', ".$row[3].").'".'"'.">'", $row[2]);
    //Nun sind in $bild alle Bilder aus dem Text drin.
    if ($row[1] != 0)
      {
      if (count($bild) != 0)
        {
        if (rand(0, 1) == 1)
          {
          $gefunden = true;
          print '<h2>Find out about</h2><center><a href="lexikon.php?matrix='.$row[3].'&artikel='.$row[0].'&SID='.session_id().'"><img width=165 src="V/bilder/matrix/'.$row[1].'" border=0><br>'.$row[0].'</a></center>';
          } else {
          $gefunden = true;
          print '<h2>Find out about</h2><center><a href="lexikon.php?matrix='.$row[3].'&artikel='.$row[0].'&SID='.session_id().'"><img width=165 src="V/bilder/matrix/'.$bild[rand(0, count($bild)-1)].'" border=0><br>'.$row[0].'</a></center>';
          }
        } else {
        $gefunden = true;
        print '<h2>Find out about</h2><center><a href="lexikon.php?matrix='.$row[3].'&artikel='.$row[0].'&SID='.session_id().'"><img width=165 src="V/bilder/matrix/'.$row[1].'" border=0><br>'.$row[0].'</a></center>';
        }
      } else {
      if (count($bild) != 0)
        {
        $gefunden = true;
        print '<h2>Find out about</h2><center><a href="lexikon.php?matrix='.$row[3].'&artikel='.$row[0].'&SID='.session_id().'"><img width=165 src="V/bilder/matrix/'.$bild[rand(0, count($bild)-1)].'" border=0><br>'.$row[0].'</a></center>';
        }
      }
    $gefunden = 1;
    }

  print '</td></tr></table>';
  $result = mysql_query("SELECT DISTINCT m.name, m.gruppe FROM `matrix` m, `gruppe` g WHERE g.id = m.gruppe AND g.freigegeben = 1 ORDER BY m.name ASC");
  $anzahl = mysql_num_rows($result);
  print "<br><br>Es sind ".$anzahl." Artikel in der Enzyklop&auml;die.";
  }

?>

<br>
<br>

</td><td width=75> </td>
</tr>
</table>

</td>
<td width=100></td>
</tr>
</table>

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
<br>
<br>
<br>
<br>
<br>
<br>

</body>
</html>