<?php

require_once dirname(__file__)."/../../lib/ModuleGroupController.class.php";
require_once dirname(__file__)."/../../lib/Text.class.php";
require_once dirname(__file__)."/classes/MatrixImage.class.php";

class matrix extends ModuleGroupController {
    
    private $breadcrumb_length = 5;
    
    public function __construct() {
        FileInclude::JS("matrix_js", "matrix.js", $this);
        if (class_exists("bureau")) {
            bureau::registerFirepoint("group_statistic", "matrix::getStatistics");
        }
        Text::addFormatRule("matrix::insertMatrixLinks", "general");
    }
    
    /**
     * sets the navigation for the user in the "bridge" or second navigation
     * @return: array('actionname1' => $clearName, 'actionname2' => $clearName, ...)
     */
    public function getNavigation() {
        $nav = array(
            'pictures' => array('title' => "Bilder"),
            'pages' => array('title' => "Informationen")
        );
        return $nav;
    }
    
    public function defaultAction() {
        return "pages";
    }
    
    public function getTitle() {
        return "Matrix";
    }

    protected function framedActions() {
        return array("pages");
    }
    
    protected function getGroup() {
        global $gruppe;
        $ret = array();
        foreach ($gruppe as $gruppe_id) {
            $ret[] = array('id' => $gruppe_id, 'name' => Groups::id2name($gruppe_id));
        }
        return $ret;
    }
    

    public static function getStatistics($params) {
        $db = DBManager::get();
        $ret = "";
        $ret .= $db->query(
            "SELECT COUNT(DISTINCT name) " .
            "FROM matrix " .
            "WHERE gruppe=".$db->quote($params['matrix'])." " .
        "")->fetch(PDO::FETCH_COLUMN, 0)." Matrix-Einträge<br>";
        $ret .= $db->query(
            "SELECT COUNT(DISTINCT id) " .
            "FROM bilder " .
            "WHERE matrix=".$db->quote($params['matrix'])." " .
        "")->fetch(PDO::FETCH_COLUMN, 0)." Bilder<br>";
        return $ret;
    }

    ////////////////////////////////////////////////////////////////////////////
    //                                actions                                 // 
    ////////////////////////////////////////////////////////////////////////////
    
    public function action_pictures() {
        global $force, $login;
        $db = DBManager::get();
        $images = $db->query("SELECT DISTINCT bilder.id, bilder.filename, bilder.width, bilder.height " .
            "FROM bilder " .
                "INNER JOIN relate_f_gr AS g ON (bilder.matrix = g.gruppe_id) " .
                "INNER JOIN relate_sp_f AS f ON (g.force_id = f.force_id) " .
            "WHERE f.spieler = ".$db->quote($login)." " .
            "ORDER BY bilder.id DESC "
        )->fetchAll();

        print Template::summon(dirname(__file__)."/views/pictures.php")
                    ->with("images", $images)
                    ->with("matrix", $this->group)
                    ->with("force", $force)
                    ->render();
        
    }
    
    public function action_upload_picture() {
        $new_file = new MatrixImage();
        $new_file->create();
    }
    
    public function action_reupload_picture() {
        $old_file = new MatrixImage($_REQUEST['file_id']);
        $old_file->upload();
    }
    
    public function action_pages() {
        global $db, $masterof;
        if ($_REQUEST['artikel'] === "SUCHMASKE") {
            $this->suchmaske();
            return;
        }
        $page = $_REQUEST['artikel'] OR $page = Groups::id2name($this->group);
        $versionen = $db->query(
            "SELECT version, autor_force " .
            "FROM matrix " .
            "WHERE name = ".$db->quote($page)." " .
                "AND gruppe = ".$db->quote($this->group)." " .
            "ORDER BY version DESC " .
        "")->fetchAll();
        $artikel = $db->query(
            "SELECT eintrag, version, autor_force, bild " .
            "FROM matrix " .
            "WHERE name = ".$db->quote($page)." " .
                "AND gruppe = ".$db->quote($this->group)." " .
            "ORDER BY version DESC " .
            "LIMIT 1 " .
        "")->fetch();
        print Template::summon(dirname(__file__)."/views/page.php")
                    ->with('page', $page)
                    ->with('breadcrumb', $this->lastPages($page))
                    ->with('matrix', $this->group)
                    ->with('masterof', $masterof)
                    ->with('artikel', $artikel)
                    ->with('versionen', $versionen)
                    ->with('additional_data', $this->fire('datasheet', "array", array('page' => $page, 'matrix' => $this->group)))
                    ->with('loeschbar', $masterof->has($this->group) OR $this->einzigerAutor($page))
                    ->render();
    }

    public function action_version() {
        $masterof;
        $db = DBManager::get();
        $page = $_REQUEST['artikel'] OR $page = Groups::id2name($this->group);
        $version = $_REQUEST['version'];
        $artikel = $db->query(
            "SELECT * " .
            "FROM matrix " .
            "WHERE name = ".$db->quote($page)." " .
                "AND gruppe = ".$db->quote($this->group)." " .
                "AND version = ".$db->quote($version)." " .
        "")->fetch(PDO::FETCH_ASSOC);
        $versionen = $db->query(
            "SELECT version, autor_force " .
            "FROM matrix " .
            "WHERE name = ".$db->quote($page)." " .
                "AND gruppe = ".$db->quote($this->group)." " .
            "ORDER BY version DESC " .
        "")->fetchAll();
        $old_version = $db->query(
            "SELECT eintrag " .
            "FROM matrix " .
            "WHERE name = ".$db->quote($page)." " .
                "AND gruppe = ".$db->quote($this->group)." " .
                "AND version < ".$db->quote($version)." " .
            "ORDER BY version DESC " .
            "LIMIT 1, 1 " . //also den vorletzten Eintrag in der Matrix
        "")->fetch(PDO::FETCH_COLUMN, 0);
        $diff = $this->htmlDiff($artikel['eintrag'], $old_version ? $old_version : "");
        print Template::summon(dirname(__file__)."/views/version.php")
                    ->with('page', $page)
                    ->with('breadcrumb', $this->lastPages())
                    ->with('matrix', $this->group)
                    ->with('artikel', $artikel)
                    ->with('diff', $diff)
                    ->with('versionen', $versionen)
                    ->render();
    }

    protected function diff($old, $new){
        foreach($old as $oindex => $ovalue){
            $nkeys = array_keys($new, $ovalue);
                foreach($nkeys as $nindex){
                $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
                $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
                if($matrix[$oindex][$nindex] > $maxlen){
                    $maxlen = $matrix[$oindex][$nindex];
                    $omax = $oindex + 1 - $maxlen;
                    $nmax = $nindex + 1 - $maxlen;
                }
            }
        }
        if($maxlen == 0) return array(array('d'=>$old, 'i'=>$new));
            return array_merge(
                $this->diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
                array_slice($new, $nmax, $maxlen),
                $this->diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
    }

    protected function htmlDiff($old, $new){
        $diff = $this->diff(explode(' ', $old), explode(' ', $new));
        foreach($diff as $k){
            if(is_array($k))
                $ret .= (!empty($k['d'])?"<ins>".implode(' ',$k['d'])."</ins> ":'').
                (!empty($k['i'])?"<del>".implode(' ',$k['i'])."</del> ":'');
            else $ret .= $k . ' ';
        }
        return $ret;
    }
    
    protected function suchmaske() {
        $db = DBManager::get();
        $lastChanges = $db->query(
            "SELECT name " .
            "FROM matrix " .
            "WHERE gruppe = ".$db->quote($this->group)." " .
            "GROUP BY name " .
            "ORDER BY MAX(version) DESC " .
            "LIMIT 20 " .
        "")->fetchAll(PDO::FETCH_COLUMN, 0);
        if ($_REQUEST['letter']) {
            $letter = strtolower($_REQUEST['letter']);
            if ($letter === "#") {
                $results = $db->query(
                    "SELECT DISTINCT name " .
                    "FROM matrix " .
                    "WHERE gruppe = ".$db->quote($this->group)." " .
                        "AND LEFT(name, 1) IN ('1','2','3','4','5','6','7','9','0') " .
                    "ORDER BY name ASC " .
                "")->fetchAll(PDO::FETCH_COLUMN, 0);
            } else {
                $letter = str_replace(array("ä", "ö", "ü"), array("a", "o", "u"), $letter);
                $results = $db->query(
                    "SELECT DISTINCT name " .
                    "FROM matrix " .
                    "WHERE gruppe = ".$db->quote($this->group)." " .
                        "AND LOWER(LEFT(name, 1)) = ".$db->quote($letter)." " .
                    "ORDER BY name ASC " .
                "")->fetchAll(PDO::FETCH_COLUMN, 0);
            }
        } elseif($_REQUEST['suche']) {
            $results = $db->query(
                "SELECT DISTINCT name " .
                "FROM matrix " .
                "WHERE gruppe = ".$db->quote($this->group)." " .
                                    "AND name LIKE ".$db->quote("%".$_REQUEST['suche']."%")." " .
            "")->fetchAll(PDO::FETCH_COLUMN, 0);
            $results2 = $db->query(
                "SELECT DISTINCT m.name " .
                "FROM matrix AS m " .
                "WHERE gruppe = ".$db->quote($this->group)." " .
                    "AND MATCH m.eintrag AGAINST (".$db->quote($_REQUEST['suche'])." IN BOOLEAN MODE) " .
                    "AND version = (SELECT MAX(m2.version) FROM matrix AS m2 WHERE m2.name=m.name AND m2.gruppe = ".$db->quote($this->group)." GROUP BY m2.name) " .
            "")->fetchAll(PDO::FETCH_COLUMN, 0);
            foreach ($results2 as $word) {
                if (!in_array($word, $results)) {
                    $results[] = $word;
                }
            }
        }
        print Template::summon(dirname(__file__)."/views/suchmaske.php")
                    ->with('breadcrumb', $this->lastPages("SUCHMASKE"))
                    ->with('matrix', $this->group)
                    ->with('results', $results)
                    ->with('lastChanges', $lastChanges)
                    ->render();
    }
    
    public function action_edit_article() {
        global $force;
        $db = DBManager::get();
        $pictures = $db->query("SELECT id, filename " .
                               "FROM bilder " .
                               "WHERE matrix = ".$db->quote($_REQUEST['group'])." " .
                               "ORDER BY date DESC"
                    )->fetchAll();
        
        $page = $_REQUEST['artikel'] OR $page = "SUCHMASKE";
        $artikel = $db->query(
            "SELECT eintrag, version, autor_force, bild " .
            "FROM matrix " .
            "WHERE name = ".$db->quote($page)." " .
                "AND gruppe = ".$db->quote($this->group)." " .
            "ORDER BY version DESC " .
        "")->fetch();
        
        print Template::summon(dirname(__file__)."/views/edit.php")
                    ->with('page', $page)
                    ->with('force', $force)
                    ->with('matrix', $_REQUEST['group'])
                    ->with('breadcrumb', $this->lastPages())
                    ->with('pictures', $pictures)
                    ->with('artikel', $artikel)
                    ->render();
    }
    
    public function action_save_article() {
        global $force;
        $db = DBManager::get();
        if (!$force->has($_REQUEST['autor'])) {
            throw new Exception("Falsche Macht ist am Werke!");
            return;
        }
        if (($_REQUEST['content']) && ($_REQUEST['title']) && ($_REQUEST['autor']) && ($this->group)) {
            $row = $db->query(
                "SELECT CURRENT_TIMESTAMP - version, autor_force, name, gruppe " .
                "FROM matrix " .
                "ORDER BY version DESC " .
                "LIMIT 1 " .
            "")->fetch();
            if (($row['autor_force'] == $_REQUEST['autor']) && ($row[0] < 3*3600) && ($row['name'] == $_REQUEST['title']) && ($row['gruppe'] == $_REQUEST['group'])) {
                $db->query("UPDATE matrix SET " .
                        "eintrag = ".$db->quote($_REQUEST['content']).", " .
                        "bild = ".$db->quote($_REQUEST['bild']).", " .
                        "version = CURRENT_TIMESTAMP " .
                    "WHERE name = ".$db->quote($_REQUEST['title'])." " .
                        "AND gruppe = ".$db->quote($_REQUEST['group'])." " .
                        "AND autor_force = ".$db->quote($_REQUEST['autor'])." " .
                    "ORDER BY version DESC " .
                    "LIMIT 1");
            } else {
                $db->query(
                    "INSERT INTO matrix (name, gruppe, eintrag, autor_force, bild) " .
                    "VALUES ( ".
                        $db->quote($_REQUEST['title']).", ".
                        $db->quote($_REQUEST['group']).", ".
                        $db->quote($_REQUEST['content']).", ".
                        $db->quote($_REQUEST['autor']).", ".
                        $db->quote($_REQUEST['bild'])." " .
                    ") " .
                "");
            }
        }
    }
    
    public function action_delete_article() {
        global $masterof, $login;
        $db = DBManager::get();
        
        if ($masterof->has($this->group) OR $this->einzigerAutor($_REQUEST['page'])) {
            //Seite löschen:
            $query = "DELETE FROM matrix WHERE name = ".$db->quote($_REQUEST['page'])." AND gruppe = ".$db->quote($this->group);
            $db->exec($query);
            //Und noch die History bereinigen.
            $_SESSION["lastPages"][$this->group] 
                    = array_filter($_SESSION["lastPages"][$this->group], 
                                    create_function('$a','return $a !== "'.addslashes($_REQUEST['page']).'";'));
        } else {
            throw new Exception ("Sie dürfen diesen Artikel nicht löschen.");
        }
    }

    public function action_get_image_details() {
        global $masterof, $login, $force;
        $db = DBManager::get();
        $image = $db->query(
            "SELECT id, filename, matrix, autor_force_id, beschreibung, mime_type, width, height, date " .
            "FROM bilder " .
            "WHERE id = ".$db->quote($_REQUEST['file_id'])." " .
        "")->fetch();
		$matrixseiten = $db->query(
			"SELECT name, gruppe " .
			"FROM matrix " .
			"WHERE gruppe = ".$db->quote($image['matrix'])." " .
				"AND version IN (" .
					"SELECT MAX(version) " .
					"FROM matrix " .
					"WHERE gruppe = ".$db->quote($image['matrix'])." " .
					"GROUP BY name " .
				") " .
				"AND (bild = ".$db->quote($_REQUEST['file_id'])." " .
					"OR eintrag LIKE ".$db->quote("%]".$_REQUEST['file_id']." %")." " .
					"OR eintrag LIKE ".$db->quote("%]".$image['filename']."%")." ) " .
		"")->fetchAll(PDO::FETCH_ASSOC);
        print Template::summon(dirname(__file__)."/views/image_details.php")
                    ->with("image", $image)
                    ->with("force", $force)
                    ->with("masterof", $masterof)
					->with("matrixseiten", $matrixseiten)
                    ->render();
    }

    public function action_get_encyclopedia() {
        //Die Library soll tatsächlich nur hier eingebunden werden und nicht für jeden anderen Aufruf.
        //Dafür ist die einfach zu dick.
        include_once dirname(__file__)."/classes/PDF.class.php";
        $db = DBManager::get();
        $part = (int) $_REQUEST['part'] ? $_REQUEST['part'] : 1;
        $articles = $db->query(
            "SELECT " .
                "m.name, " .
                "m.gruppe, " .
                "(SELECT m2.eintrag FROM matrix as m2 WHERE m2.gruppe = m.gruppe AND m2.name = m.name ORDER BY CHAR_LENGTH(m2.eintrag) DESC LIMIT 1) AS eintrag, " .
                "(SELECT m2.bild FROM matrix as m2 WHERE m2.gruppe = m.gruppe AND m2.name = m.name ORDER BY CHAR_LENGTH(m2.eintrag) DESC LIMIT 1) AS bild " .
            "FROM matrix AS m " .
            "WHERE m.gruppe = ".$db->quote($this->group)." " .
            //    "AND m.name = 'Perenna' " .
            "GROUP BY m.name ASC LIMIT ".addslashes(($part-1)*50).", ".addslashes($part*50)." " .
        "")->fetchAll();

        $images = $db->query(
            "SELECT id FROM bilder WHERE matrix = ".$db->quote($this->group).
        "")->fetchAll(PDO::FETCH_COLUMN, 0);

        // create new PDF document
        $pdf = new PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Team TSC');
        $pdf->SetTitle('TSC V');
        $pdf->SetSubject('Enzyklopädie');
        $pdf->SetKeywords('TSC');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

        // set header and footer fonts
        $pdf->setHeaderFont(Array("aeaswfte", '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array("ocraext", '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        //set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        //set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        //set some language-dependent strings
        $pdf->setLanguageArray($l);

        // ---------------------------------------------------------

        // set font
        $tmp_dir = dirname(__file__)."/../../tmp";
        foreach ($images as $image_id) {
            $image_file = new MatrixImage($image_id);
            $image_file->save($tmp_dir."/pdf_image_".$image_id);
        }

        $super_html = "";
        foreach ($articles as $article) {
            // add a page
            $pdf->AddPage();

            $pdf->SetFont('aeaswfte', '', 10);
            $html = '<h1 style="text-align: center; margin-bottom: 20px;">'.escape($article['name'])."</h1><br>";
            //print $html;
            //var_dump($article['bild']);
            if ($article['bild']) {
                $norm = 300;
                $bild = new MatrixImage(Text::getpic($article['bild'], $this->group));
                $image = $bild->getSize();
                $width = $image['width'] ? $image['width'] : $norm;
                $height = $image['height'] ? $image['height'] : $norm;
                /*if ($width > $height) {
                    if ($height > $norm) {
                        $width = floor($width * $norm / $height);
                        $height = $norm;
                    }
                } else {
                    if ($width > $norm) {
                        $height = floor($height * $norm / $width);
                        $width = $norm;
                    }
                }*/
                $bild_adress = Text::getpic($article['bild'], $this->group);
                if ($bild_adress) {
                    $html .= '<img style="'.($height > 500 ? 'height: 500px; ' : '').'float: right; margin-left: auto; margin-right: auto;" src="tmp/pdf_image_'.$bild_adress.'">';
                }
            }
            $super_html .= $html;
            $pdf->writeHTML($html, true, false, true, false, '');

            $pdf->SetFont('ocraext', '', 10);
            $html = Text::pdf_format($article['eintrag'], $article['name'], $this->group);
            $super_html .= $html;
            //print $html;
            $pdf->writeHTML($html, true, false, true, false, '');
        }

        // ---------------------------------------------------------
        //die($super_html);

        //Close and output PDF document
        $pdf->Output('encyclopedia.pdf', 'I');
        //Bilder abräumen:
        foreach ($images as $key => $image_id) {
            //unlink($tmp_dir."/pdf_image_".$image_id);
        }

    }

    /**
     * checks if the current user (by login) is the only author of an article
     */
    protected function einzigerAutor($page) {
        global $login;
        $db = DBManager::get();
        $anzahlAutoren = $db->query(
            "SELECT COUNT(*) " .
            "FROM matrix " .
            "WHERE gruppe = ".$db->quote($this->group)." " .
                "AND name = ".$db->quote($page)
        )->fetch(PDO::FETCH_COLUMN, 0);
        $anzahlIch = $db->query(
            "SELECT COUNT(*) " .
            "FROM matrix AS m " .
                "INNER JOIN relate_sp_f AS f ON (m.autor_force = f.force_id) " .
            "WHERE m.gruppe = ".$db->quote($this->group)." " .
                "AND m.name = ".$db->quote($page)." " .
                "AND f.spieler = ".$db->quote($login)
        )->fetch(PDO::FETCH_COLUMN, 0);
        return ($anzahlAutoren === $anzahlIch);
    }
    
    private function lastPages($page = null) {
        if (!$_SESSION["lastPages"]) {
            $_SESSION["lastPages"] = array();
        }
        if (!$_SESSION["lastPages"][$this->group]) {
            $_SESSION["lastPages"][$this->group] = array("SUCHMASKE");
        }
        if ($page) {
            if ($_SESSION["lastPages"][$this->group][count($_SESSION["lastPages"][$this->group])-1] !== $page) {
                array_push($_SESSION["lastPages"][$this->group], $page);
            }
            if (count($_SESSION["lastPages"][$this->group]) > $this->breadcrumb_length + 1) {
                array_shift($_SESSION["lastPages"][$this->group]);
            }
            
        }
        return array_slice($_SESSION["lastPages"][$this->group], 
                            0, 
                            count($_SESSION["lastPages"][$this->group]) - ($page ? 1 : 0));
    }

    static public function insertMatrixLinks($text) {
        $text = preg_replace('/\[\[(.*?)\]\]/e', 'matrix::getMatrixLink("$1")', $text);
        return $text;
    }

    static protected function getMatrixLink($title) {
        global $login;
        $db = DBManager::get();
        $row = $db->query(
            "SELECT matrix.name, matrix.gruppe " .
            "FROM matrix " .
                "INNER JOIN relate_f_gr ON (relate_f_gr.gruppe_id = matrix.gruppe) " .
                "INNER JOIN relate_sp_f ON (relate_f_gr.force_id = relate_sp_f.force_id) " .
            "WHERE matrix.name = ".$db->quote($title)." " .
                "AND relate_sp_f.spieler = ".$db->quote($login)." " .
        "")->fetch();
        if ($row) {
            return '<a onClick="TSC.matrix.openArticle('."'".$row['gruppe']."', '" .Text::escape(str_replace(" ", "%20", $row['name']))."'".');">'.$title."</a>";
        } else {
            return $title;
        }
    }


}
