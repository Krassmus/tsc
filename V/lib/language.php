<?php
/*
 * Created on 16.05.2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require_once dirname(__file__)."/DBManager.class.php";

class language {
  private $db;
  private static $oldmessages;  //like an internal cache
  private static $language;
  
	public function __construct($language = "german") {
    self::$language = $language;
		self::$db = DBManager::get();
	}
  
  public function gettext($text) {
    //Standardsprache ist Deutsch:
    if (self::$language === "german") {
    	return $text;
    }
    $text_hash = md5($text);
    if (isset($this->oldmessages[$text_hash])) {
    	return $this->oldmessages[$text_hash];
    } else {
    	$statement = $this->db->prepare("SELECT translation " .
          "FROM languages " .
          "WHERE id = :hash_text AND language = :lang ");
      $result = $statement->execute(array('hash_text' => $text_hash, 'lang' => self::$language))->fetch();
      if ($result['translation'] !== NULL) {
        return $result['translation'] ? $result['translation'] : $text;
      } else {
        $statement = $this->db->prepare("INSERT INTO languages SET id = :hash_text, original = :text, translation = :trans, mkdate = :time ");
        $statement->execute(array('id' => $text_hash,
                                  'text' => $text,
                                  'trans' => "",
                                  'time' => time()));
      	return $text;
      }
    }
  }
}

function §($text) {
  $languagizer = new language("german");
  return $languagizer->gettext($text);
}
