<?php 

require_once dirname(__file__)."../../../../lib/DBFile.class.php";
require_once dirname(__file__)."/pdf2text.class.php";


class NewsPDF extends DBFile {
    public $table = "messages";
    public $id_field = "id";
    public $filename_field = "pdf_title";
    public $content_field = "pdf_content";
    public $mime_type_part1 = "application/pdf";
    public $mime_type_field = null;
    protected $id;
    
    public function __construct($id = null) {
        parent::__construct($id);
    }
    
    public function readable() {
        global $login;
        return true;
        $db = DBManager::get();
        $hasrightstoread = $db->query("SELECT 1 " .
            "FROM bilder " .
                "INNER JOIN relate_f_gr AS g ON (bilder.matrix = g.gruppe_id) " .
                "INNER JOIN gruppe ON (gruppe.id = g.gruppe_id) " .
                "LEFT JOIN relate_sp_f AS f ON (g.force_id = f.force_id) " .
            "WHERE (f.spieler = ".$db->quote($login)." " .
                    "OR gruppe.freigegeben = '1') " .
                "AND bilder.id = ".$db->quote($this->id)." " .
            "ORDER BY bilder.id DESC "
        )->fetch(PDO::FETCH_COLUMN, 0);
        return $hasrightstoread ? true : false;
    }
    
    public function writable() {
        return true;
    }
    
    public function upload($db_entries = array()) {
        parent::upload(
            array(
                "fromforce" => $_REQUEST['force_id'],
                "pdf" => 1
            )
        );
    }
	
    public function deliver() {
        global $force;
        $db = DBManager::get();
        foreach ($force as $f) {
            $db->exec(
                    "UPDATE relate_messages SET yet_read = '1' WHERE ms_id = ".$db->quote($this->id)." AND toforce = ".$db->quote($f)." " .
            "");
        }
        parent::deliver();
    }
    
    public function getSize() {
        $db = DBManager::get();
        return $db->query(
            "SELECT width, height FROM bilder WHERE id = ".$db->quote($thid->id)." " .
        "")->fetchAll();
    }
    
    
    
}