<?php

require_once dirname(__file__)."/ModuleController.class.php";

class ModuleGroupController extends ModuleController {

    protected $group = null;
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * returns an array of action-names which are "grouped"
     */
    protected function framedActions() {
        return array();
    }
    
    public function activateAction($action) {
        $group = $this->getGroup();
        if (!empty($_REQUEST['group'])
                OR !in_array($action, $this->framedActions())) {
            $this->group = count($group) === 1 ? $group[0]['id'] : ($_REQUEST['group'] ?? null);
            parent::activateAction($action);
        } else {
            ob_start();
            $this->group = $group[0]['id'];
            parent::activateAction($action);
            $content = ob_get_contents();
            ob_end_clean();
            print Template::summon(dirname(__file__)."/../views/ModuleGroupFrame.php")
                    ->with("content", $content)
                    ->with("controller", get_class($this))
                    ->with("group", $group)
                    ->render();
        }
    }
    
    protected function getGroup() {
        return array();
    }
}

