<?php

require_once dirname(__file__)."/DBManager.class.php";

class Text {
  
    static private $tablecount = 1;
    static private $headercount = 0;
    static private $discussioncount = 0;

    static protected $depth = 1;
    static protected $aspects = array();
  
    static public function format($text, $aspects = array("general")) {
        $replacement = sha1("LONGSNAKE".time())."_".md5("LONGSNAKE".time());
        $text = str_replace("~", $replacement, $text);
        $text = self::escape($text);

        self::$depth = 1;
        $text = self::subformat($text, $aspects);

        $text = nl2br($text);
        $text = str_replace("\n", "", $text);
        $text = str_replace ("\\", "", $text);
        $text = str_replace($replacement, "~", $text);
        return $text;
    }

    static protected function subformat($text, $aspects = array()) {
        self::$depth++;
        //$text = preg_replace('/\{\{([^~]+?)\}\}/e', 'self::subformat("\\1", $aspects)', $text);
        $text = preg_replace_callback(
            '/\{\{([^~]+?)\}\}/',
            function($match) use ($aspects) {
                return self::subformat($match, $aspects);
            },
            $text
        );
        self::$depth--;
        foreach ($aspects as $aspect) {
            if (isset(self::$aspects[$aspect])) {
                foreach (self::$aspects[$aspect] as $rule) {
                    $text = call_user_func($rule, $text);
                }
            }
        }
        return $text;
    }

    static public function addFormatRule($rule_func, $aspect = "general") {
        self::$aspects[$aspect][] = $rule_func;
    }

    static public function initRules() {
        //general:
        self::addFormatRule("Text::special_format_textstyle");
        self::addFormatRule("Text::special_format_images");

        //wiki:
        self::addFormatRule("Text::special_format_wikilinks", "matrix");

        //PDF:
        self::addFormatRule("Text::special_format_textstyle", "PDF");
        self::addFormatRule("Text::special_format_pdf", "PDF");

        //Kürzen auf Text:
        self::addFormatRule("Text::special_format_shorten", "shorten");
    }

    static public function escape($text) {
        return htmlentities($text, ENT_COMPAT, 'UTF-8');
    }

    ////////////////////////////////////////////////////////////////////////////
    //                                helpers                                 //
    ////////////////////////////////////////////////////////////////////////////

    static public function getpic($picture, $matrix = NULL) {
        global $gruppe;
        if (is_numeric($picture)) {
            return floor($picture);
        } else {
            $db = DBManager::get();
            $gruppen_array = "";
            foreach ($gruppe as $key => $g) {
                $key < 1 OR $gruppen_array .= ", ";
                $gruppen_array .= $db->quote($g);
            }
            $row = $db->query("SELECT id FROM bilder WHERE matrix IN (".$gruppen_array.") AND filename = ".$db->quote($picture)." ORDER BY date ASC")->fetch();
            return $row['id'];
        }
    }

    static protected function pic_parameters($par, $matrix = NULL) {
        $db = DBManager::get();
        $par = explode(":", $par);
        $add = "";
        $styled = false;
        for ($i=0; $i < count($par); $i++) {
            if ((!is_numeric($par[$i])) && ($styled == false)) {
                $styled = true;
                $add .= ' style="';
                for ($j=$i; $j < count($par); $j++) {
                    if ($par[$j] == "right")
                        $add .= 'float:right;';
                    if ($par[$j] == "left")
                        $add .= 'float:left;';
                    if ( ($par[$j] != "right") && ($par[$j] != "left") && (!is_numeric($par[$j]) && ($matrix !== NULL)) )
                        $add .= 'cursor:pointer;';
                }
                $add .= '"';
            }
            if (is_numeric($par[$i]) OR preg_match("/\d{1,3}%/", $par[$i]))
                $add .= ' width='.$par[$i];
            if ( ($par[$i] != "right") && ($par[$i] != "left") && (!is_numeric($par[$i]) && ($matrix !== NULL)) ) {
                $row = $db->query("SELECT name " .
                    "FROM matrix " .
                    "WHERE name = ".$db->quote($par[$i])." " .
                    "AND gruppe = ".$db->quote($matrix)." " .
                "LIMIT 1")->fetch();
                if ($row)
                    $add .= 'title="'.$row[0].'" onClick="getContent('."'matrix_".$matrix."', 'matrix/artikel.php?page=".$row[0]."&matrix=$matrix&SID=".session_id()."'".')"';
            }
        }
        return $add;
    }

    static protected function gettable($tabletext, $prefix = NULL)
        {
        if ($prefix === NULL) {
            $prefix = md5(uniqid());
        }
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
                } elseif (substr($head[$i], 0, 14) == "vfusion")           //Zellen nehmen vertical mehr Platz ein.
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
            $text .= '<tr id="mx_tbl_'.$prefix.'_'.self::$tablecount.'_row_'.$i.'"';
            if ($active_row)
                $text .= ' class="lightable"';
            $text .= '>';
            $tabletext[$i] = explode(" || ", $tabletext[$i]);
            for ($j=0; $j < count($tabletext[$i]); $j++)
            {
                $text .= '<td';
                if (!$center) $text .= ' valign=top';
                $text .= ' id="mx_tbl_'.$prefix.'_'.self::$tablecount.'_'.$j.'_'.$i.'"';
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
        self::$tablecount++;
        return $text;
        }

    static protected function makelist($listtext, $stage = 0) {
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
                    $text .= self::makenum($new_num, 0);
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
                $text .= self::makelist($new_list, $stage+1);
                $i--;
            }
        }
        $text .= "</ul>";
        return $text;
        }
    static function makenum($numtext, $stage = 0)
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
                    $text .= self::makelist($new_list, 0);
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
                $text .= self::makenum($new_num, $stage+1);
                $i--;
            }
        }
        $text .= "</ol>";
        return $text;
    }
  
    static protected function makeheaders($text, $matrix = "") {
        self::$headercount++;
        return '<h2 id="matrix_'.$matrix.'_'.self::$headercount.'">'.str_replace(" ", "&nbsp;", $text)."</h2>";
    }

    static protected function discuss($topic, $matrix) {
        $topic = trim($topic);
        $topic = str_replace(" ", "&nbsp;", $topic);
        global $stil;
        global $force;
        $db = DBManager::get();
        self::$discussioncount++;
        $text = '
            <center><table id="Diskussion_'.$matrix.'_'.self::$discussioncount.'" style="background-color:#555555; width: 400px; border: thin solid #505050; -moz-border-radius: 5px; -webkit-border-radius: 5px; border-radius: 5px; height: 20px;"><tr><td valign=top>';
        $text .= '<a href="Javascript: open_discussion('.$matrix.', '.self::$discussioncount.')">Diskussion: <i>'.$topic.'</i></a>';
        $text .= '<span id="Diskussion_'.$matrix.'_'.self::$discussioncount.'_screen" style="display:none; font-size:5;"><br><center><iframe name="Diskussion_'.$matrix.'_'.self::$discussioncount.'_frame" frameborder=1 border=0 src="matrix/forum.php?matrix='.$matrix.'&topic='.$topic.'" style="width:380px; height:200px; border:1px solid #888888;">Ihr Betrachter ist f&uuml;r dieses Forum zu alt oder hat eingeschr&auml;nkte Rechte. Der Diskussion werden Sie erst beiwohnen k&ouml;nnen, wenn Sie einen anderen Betrachter benutzen.</iframe></span>';
        $text .= '<span id="Diskussion_'.$matrix.'_'.self::$discussioncount.'_send" style="display:none; font-size:5;"><br>
          <form name="forumeintrag_'.$matrix.'_'.self::$discussioncount.'"><textarea name="content" style="width: 92%; height: 80px; font-size: '.(($stil[4]) ? ($stil[4]-2) : 10).'; background-color:#444444;"></textarea><br>
            ';
        if (count($force)>1)
        {
            $text .= '<span style="font-size: '.(($stil[4]) ? ($stil[4]-2) : 10).';">Als <select style="font-size: 10;" name="fromforce">';
            for ($i=0; $i < $force->length(); $i++)
            {
                $f_name = $db->query("SELECT name FROM forces WHERE id = ".$db->quote($force[$i]))->fetch();
                $text .= '<option value="'.$force[$i].'">'.$f_name[0].'</option>
                  ';
            }
            $text .= "</select></span>
              ";
        } else {
            $text .= '<input type=hidden name="fromforce" value="'.$force[0].'">';
        }
        $text .= '<input type=hidden name="topic" value="'.$topic.'">';
        $text .= '<input type="button" style="font-size: '.(($stil[4]) ? ($stil[4]-2) : 10).';" value="Senf hinzugeben" onClick="sendDiscussionEntry('.$matrix.', '.self::$discussioncount.')"></form></span>';
        $text .= "</td></tr></table></center>";
        return $text;
    }

    /**
     * @deprecated use Text::format instead
     */
    static function wiki_format($text, $title, $depth, $matrix = null) {
        return self::format($text, array('general', 'matrix'));
    }
    /**
     * @deprecated use Text::format instead
     */
    static function pdf_format($text, $depth, $matrix = null) {
        return self::format($text, array('PDF'));
    }
    /**
     * @deprecated use Text::format instead
     */
    static function general_format($text, $depth = 0, $matrix = 1) {
        return self::format($text, array('general'));
    }
  
    static public function shortened_format($text, $cut = NULL, $depth = 0) {
        $text = self::format($text, array("general", "shorten"));
        //Bilder und andere blöde Formatierungen löschen:
        //$text =  strip_tags($text, '<br><i>');

        //Kürzen:
        //$text = substr($text, 0, 200);
        
        return $text;
    }
  
    static public function autowrap($str) {
        while (strlen($str) > 18) {
            $text[] = substr($str, 0, 18);
            $str = substr($str, 18);
        }
        $text[] = $str;
        $str = implode(" ", $text);
        return $str;
    }
  
    static protected function special_format_textstyle($text) {
        $text = preg_replace_callback('/\n\|\|([^~]+?)\|\|\n\n/', "Text::gettable", $text);
        $text = preg_replace('/\n\n!!(.*?)\n\n/', '<h2>$1</h2>', $text);
        $text = preg_replace('/\n!!(.*?)\n\n/', '<h2>$1</h2>', $text);
        $text = preg_replace('/\n\n!!(.*?)\n/', '<h2>$1</h2>', $text);
        $text = preg_replace('/\n!!(.*?)\n/', '<h2>$1</h2>', $text);
        $text = preg_replace('/!!(.*?)\n/', '<h2>$1</h2>', $text);
        $text = preg_replace('/\%\%([^~]+?)\%\%/', '<i>$1</i>', $text);
        $text = preg_replace_callback('/\n\-([^<]+?)\n\n/', "Text::makelist", $text);
        $text = preg_replace_callback('/\n\+([^<]+?)\n\n/', "Text::makenum", $text);
        return $text;
    }

    static protected function special_format_images($text) {
        $text = preg_replace("/\[img\:(\w[\w|\:|\.|\-%]+)\](\w[\w|\.|\-]+)/e", "'<img src=".'"'."file.php?module=matrix&type=MatrixImage&file_id='.self::getpic('\\2').'".'"'." '.self::pic_parameters('\\1').'>'", $text);
        $text = preg_replace("/\[img\](\w[\w|\.|\-%]+)/e", "'<img src=".'"'."file.php?module=matrix&type=MatrixImage&file_id='.self::getpic('\\1').'".'"'.">'", $text);
        return $text;
    }

    static protected function special_format_PDF($text) {
        $text = preg_replace('/\&sect;\&sect;(.*?)\&sect;\&sect;/e', "", $text);
        $text = preg_replace("/\[img\:(\w[\w|\:|\.|\-%]+)\](\w[\w|\.|\-]+)/e", "'<img src=".'"'."tmp/pdf_image_'.self::getpic('\\2', '".$matrix."').'".'"'." '.self::pic_parameters('\\1').'>'", $text);
        $text = preg_replace("/\[img\](\w[\w|\.|\-%]+)/e", "'<img src=".'"'."tmp/pdf_image_'.self::getpic('\\1', '".$matrix."').'".'"'.">'", $text);
        $text = str_replace("<img src=\"tmp/pdf_image_\">", "", $text);
        return $text;
    }

    static protected function special_format_wikilinks($text) {
        global $gruppe;
        $db = DBManager::get();
        if ($_REQUEST['group'] && $gruppe->has($_REQUEST['group'])) {
            $gruppen_array = $db->quote($_REQUEST['group']);
        } else {
            $gruppen_array = "";
            foreach ($gruppe as $key => $g) {
                $key < 1 OR $gruppen_array .= ", ";
                $gruppen_array .= $db->quote($g);
            }
        }
        $title = $_REQUEST['artikel'] && $_REQUEST['artikel'] !== "SUCHMASKE" ? $_REQUEST['artikel'] : null;
        $result = $db->query(
            "SELECT DISTINCT name, gruppe " .
            "FROM matrix " .
            "WHERE gruppe IN (".$gruppen_array.") " .
                ($title ? "AND name != ".$db->quote($title)." " : "") .
            "ORDER BY CHAR_LENGTH(name) DESC " .
        "")->fetchAll();
        foreach($result as $row) {
            $text = str_replace(" ".self::escape($row['name']), //wegen der Umlaute
                              ' <a onClick="TSC.matrix.openArticle('."'".$row['gruppe']."', '" .str_replace("'", '%HOCHKOMMA%', self::escape(str_replace(" ", "%20", $row['name'])))."'".');">'.str_replace(" ", "&nbsp;", self::escape($row['name'])).'</a>',
                              $text);
        }
        if (self::$depth === 1) {
            //Hier müssen die erzwungenen Spaces noch aus den Linkparametern. 
            //Das darf aber nicht geschehen, wenn wir noch mindestens eine Ebene tiefer
            //sind, damit wir nicht doppelt Links erzeugen.
            $text = str_replace("&nbsp;", " ", $text);
        }
        return $text;
    }

    static protected function special_format_shorten($text) {
        //Bilder und andere blöde Formatierungen löschen:
        $text =  strip_tags($text, '<br><i>');
        //Kürzen:
        $text = substr($text, 0, 200);
        return $text;
    }

    
}

function escape($text) {
    return Text::escape($text);
}

Text::initRules();