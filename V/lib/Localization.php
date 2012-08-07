<?php

require_once dirname(__file__)."/locale/I18nConnector.php";

function l($text, $language_id = null) {
    $translation = Localization::translate($text, $language_id);
    $translated = $text !== $translation;
    return $translated 
        ? '<span class="translation '.$language_id.'" data-original-string="'.escape($text).'">'.escape($translation).'</span>'
        : '<span class="nottranslated" data-original-string="'.escape($text).'">'.escape($translation).'</span>';
}

function ll($text, $language_id = null) {
    return Localization::translate($text, $language_id);
}

class Localization {
    static protected $replacements = array();
    
    static public function translate($text, $language_id = null) {
        $language_id or $language_id = $GLOBALS['user_language'];
        $translation = I18nConnector::get()->translate($language_id, $text);
        $translation or $translation = $text;
        $replacements = (array) self::$replacements[$language_id];
        return str_replace(array_keys($replacements), $replacements, $translation);
    }
    
    static public function setReplacement($language_id, $word, $replacement) {
        self::$replacements[$language_id][$word] = $replacement;
    }
    
}