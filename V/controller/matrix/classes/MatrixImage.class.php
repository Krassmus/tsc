<?php 

require_once dirname(__file__)."../../../../lib/DBFile.class.php";


class MatrixImage extends DBFile {
    public $table = "bilder";
    public $id_field = "id";
    protected $id;
    public $mime_type_part1 = "image";
    public $width_field = "width";
    public $height_field = "height";
    
    public function __construct($id = null) {
        parent::__construct($id);
    }
    
    public function readable() {
        global $login;
        $db = DBManager::get();
        $hasrightstoread = $db->query(
            "SELECT 1 " .
            "FROM bilder " .
                "INNER JOIN relate_f_gr AS g ON (bilder.matrix = g.gruppe_id) " .
                "INNER JOIN gruppe ON (gruppe.id = g.gruppe_id) " .
                "LEFT JOIN relate_sp_f AS f ON (g.force_id = f.force_id) " .
            "WHERE (f.spieler = ".$db->quote($login)." " .
                    "OR gruppe.freigegeben = '1') " .
                "AND bilder.id = ".$db->quote($this->id)." " .
            "ORDER BY bilder.id DESC " .
        "")->fetch(PDO::FETCH_COLUMN, 0);
        return $hasrightstoread ? true : false;
    }
    
    public function writable() {
        return parent__writable();
    }
    
    public function create($db_entries = array()) {
        parent::create(
            array(
                "autor_force_id" => $_REQUEST['force_id'], 
                "matrix" => Forces::forcesGroup($_REQUEST['force_id'])
            )
        );
    }
    
    public function update($db_entries = array()) {
        parent::update(
            array(
                "autor_force_id" => $_REQUEST['force_id'], 
                "matrix" => Forces::forcesGroup($_REQUEST['force_id'])
            )
        );
    }
    
    public function getSize() {
        $db = DBManager::get();
        return $db->query(
                "SELECT width, height FROM bilder WHERE id = ".$db->quote($thid->id)." " .
        "")->fetchAll();
    }
    
    
    
}