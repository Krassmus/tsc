<?php

require_once dirname(__file__)."/vendor/qqUpload.php";

class DBFile {
    
    //Die folgenden müssen public sein für die Rechtechecks
    public $table;
    public $mime_type_field = "mime_type";
    public $filename_field = "filename";
    public $content_field = "content";
    public $date_field = "date";
    public $mime_type_part1 = "text";
    public $width_field = NULL;
    public $height_field = NULL;
    protected $id;
    
    public function __construct($id = null) {
        $this->id = $id;
        if (!$this->table) {
            throw new Exception("Datei-Typ hat keinen zugewiesenen Tabellennamen in der Datenbank.");
            $this->__destruct();
            return;
        }
        if (!$this->id_field) {
            $db = DBManager::get();
        }
    }
    
    public function getId() {
        return $this->id;
    }

    /**
     * darf der aktuelle Nutzer die Datei sehen?
     */
    public function readable() {
        return true;
    }
    
    /**
     * darf der aktuelle Nutzer die Datei überschreiben/bearbeiten?
     */
    public function writable() {
        return false;
    }
    
    /**
     * Liefert die aktuelle Datei aus, wenn der Nutzer das Recht zum Sehen hat.
     * Ignoriert alle anderen Ausgaben von PHP und beendet das Programm, sodass nur die Datei
     * zurückgegeben werden kann!
     */
    public function deliver() {
        if ($this->readable()) {
            $db = DBManager::get();
            $file_data = $db->query("SELECT * " .
                               "FROM `".$this->table."` " .
                               "WHERE ".$this->id_field." = ".$db->quote($this->id))->fetch();
            
            if ($this->mime_type_field === null) {
                header("Content-type: ".$this->mime_type_part1);
            } else {
                header("Content-type: ".$file_data[$this->mime_type_field]);
            }
            if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] && $file_data[$this->date_field] < strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
                //cache-control:
                header("HTTP/1.1 304 Not Modified");
                exit;
            }
            header('Content-Disposition: inline; filename="'.$file_data[$this->filename_field].'"');
            header("Cache-Control: no-cache");
            header("Pragma: no-cache");
            print $file_data[$this->content_field];
            exit;
        }
    }

    public function save($file_path) {
        if ($this->readable()) {
            $db = DBManager::get();
            $file_data = $db->query("SELECT * " .
                               "FROM `".$this->table."` " .
                               "WHERE ".$this->id_field." = ".$db->quote($this->id))->fetch();
            file_put_contents($file_path, $file_data['content']);
        }
    }
    
    /**
     * deprecated
     */
    public function create($db_entries = array()) {
        global $force;
        $allowedExtensions = array();
        $sizeLimit = 1 * 1024 * 1024;
        
        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        $result = $uploader->handleUpload(dirname(__file__)."/../tmp/");
        // to pass data through iframe you will need to encode all html tags
        echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
        
        if ($result['success'] === true && $force->has($_REQUEST['force_id'])) {
            //packe das Bild in die Datenbank:
            $db = DBManager::get();
            $filename = $result['filename'];
            $mime_type = $this->mime_type_part1."/".$result['ext'];
            if ($this->width_field OR $this->height_field) {
                $size = getimagesize($result['path']);
            }
            $additional_db_entries = "";
            foreach ($db_entries as $key => $value) {
                $additional_db_entries .= "`".addslashes($key) . "` = " . $db->quote($value) . ", ";
            }
            $time = time();
            $db->exec(
                "INSERT INTO `".addslashes($this->table)."` " .
                "SET ".addslashes($this->filename_field)." = ".$db->quote($filename).", " .
                    $additional_db_entries .
                    ($this->width_field ? 
                        "`".addslashes($this->width_field)."` = ".($size[0] ? $db->quote($size[0]) : "NULL").", " : "") .
                    ($this->height_field ?
                        "`".addslashes($this->height_field)."` = ".($size[1] ? $db->quote($size[1]) : "NULL").", " : "") .
                    "`".addslashes($this->date_field)."` = ".$db->quote($time).", " .
                    ($this->mime_type_field ? 
                        "`".addslashes($this->mime_type_field)."` = ".$db->quote($mime_type).", " : "") .
                    "`".addslashes($this->content_field)."` = ".$db->quote(file_get_contents($result['path']))." " .
            "");
            //der folgende Aufruf sollte genau genug sein, um ein Table-Lock unnötig werden zu lassen:
            $this->id = $db->lastInsertId();
        };
        //Datei löschen und fertig:
        if (file_exists($result['path'])) {
            unlink($result['path']);
        }
        
    }
    
    /**
     * deprecated
     */
    public function update($db_entries = array()) {
        global $force;
        if (!$this->id) {
            return false;
        }
        
        $allowedExtensions = array();
        $sizeLimit = 1 * 1024 * 1024;
        
        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        $result = $uploader->handleUpload(dirname(__file__)."/../tmp/");
        // to pass data through iframe you will need to encode all html tags
        if ($result['success'] === true && $force->has($_REQUEST['force_id']) && $this->writable()) {
            //packe das Bild in die Datenbank:
            $db = DBManager::get();
            $filename = $result['filename'];
            $mime_type = $this->mime_type_part1."/".$result['ext'];
            if ($this->width_field OR $this->height_field) {
                $size = getimagesize($result['path']);
            }
            $additional_db_entries = "";
            foreach ($db_entries as $key => $value) {
                $additional_db_entries .= $key . " = " . $db->quote($value) . ", ";
            }
            $db->exec(
                "UPDATE `".addslashes($this->table)."` " .
                "SET ".addslashes($this->filename_field)." = ".$db->quote($filename).", " .
                    $additional_db_entries .
                    "`".addslashes($this->width_field)."` = ".($size[0] ? $db->quote($size[0]) : "NULL").", " .
                    "`".addslashes($this->height_field)."` = ".($size[1] ? $db->quote($size[1]) : "NULL").", " .
                    "`".addslashes($this->date_field)."` = ".$db->quote($time).", " .
                    "`".addslashes($this->mime_type_field)."` = ".$db->quote($mime_type).", " .
                    "`".addslashes($this->content_field)."` = ".$db->quote(file_get_contents($result['path']))." " .
                "WHERE ".$this->id_field." = ".$db->quote($this->id)." " .
            "");
        };
        $result['id'] = $this->id;
        echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
        
        //Datei löschen und fertig:
        if (file_exists($result['path'])) {
            unlink($result['path']);
        }
    }
    
    public function upload($db_entries = array()) {
        global $force;
        
        $allowedExtensions = array();
        $sizeLimit = 1 * 1024 * 1024;
        
        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        $result = $uploader->handleUpload(dirname(__file__)."/../tmp/");
        // to pass data through iframe you will need to encode all html tags
        
        if ($result['success'] === true && $force->has($_REQUEST['force_id']) && $this->writable()) {
            //packe die Datei in die Datenbank:
            $db = DBManager::get();
            $filename = $result['filename'];
            $mime_type = $this->mime_type_part1."/".$result['ext'];
            if ($this->width_field OR $this->height_field) {
                $size = getimagesize($result['path']);
            }
            $additional_db_entries = "";
            foreach ($db_entries as $key => $value) {
                $additional_db_entries .= $key . " = " . $db->quote($value) . ", ";
            }
            $set = "SET `".addslashes($this->filename_field)."` = ".$db->quote($filename).", " .
                    $additional_db_entries .
                    ($this->width_field ? "`".addslashes($this->width_field)."` = ".($size[0] ? $db->quote($size[0]) : "NULL").", " : "") .
                    ($this->height_field ? "`".addslashes($this->height_field)."` = ".($size[1] ? $db->quote($size[1]) : "NULL").", " : "") .
                    ($this->date_field ? "`".addslashes($this->date_field)."` = ".$db->quote($time).", " : "") .
                    ($this->mime_type_field ? "`".addslashes($this->mime_type_field)."` = ".$db->quote($mime_type).", " : "") .
                    "`".addslashes($this->content_field)."` = ".$db->quote(file_get_contents($result['path']))." ";
            if ($this->id) {
                print "hi ".$this->id;
                $db->exec(
                    "UPDATE `".addslashes($this->table)."` " .
                    $set .
                    "WHERE `".$this->id_field."` = ".$db->quote($this->id)." " .
                "");
            } else {
                $db->exec(
                    "INSERT INTO `".addslashes($this->table)."` " .
                    $set .
                "");
                $this->id = $db->lastInsertId();
            }
        }

        $result['id'] = $this->id;
        echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);

        //Datei löschen und fertig:
        if (file_exists($result['path'])) {
            unlink($result['path']);
        }
    }
}