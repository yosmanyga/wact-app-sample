<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_CONTROLLERS
* @version $Id: view.inc.php,v 1.9 2004/12/01 02:44:19 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------

require_once WACT_ROOT . 'template/template.inc.php';

/**
* Implements TemplateView pattern. Base View for non-forms
* @abstract
* @package WACT_VIEW
*/
class View {

    /**
    * @access Protected
    * @var Template
    */
    var $Template;

    /**
    * @param string filename of template
    */
    function View($TemplateFile = NULL) {
        if (!is_null($TemplateFile)) {
            $this->Template =& new Template($TemplateFile);
        }
    }

    /**
    * Template method provided as a place for subclasses to place
    * template manipulation logic.  It is not necessary to 
    * call the parent prepare method when this method is overridden.
    * @return void
    * @access public
    */
    function prepare(&$controller, &$request, &$responseModel) {
    }
   
    /**
    * Output the View
    * @return void
    * @access public
    */
    function display(&$controller, &$request, &$responseModel) {
        $this->Template->registerDataSource($responseModel);
        $this->prepare($controller, $request, $responseModel);
        $this->Template->display();
    }

}

?>