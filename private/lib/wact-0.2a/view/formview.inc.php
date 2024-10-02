<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_CONTROLLERS
* @version $Id: formview.inc.php,v 1.11 2004/12/01 02:44:19 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* Include the Template class
*/

require_once WACT_ROOT . 'template/template.inc.php';

/**
* Implements TemplateView pattern for Forms.
* @see http://wact.sourceforge.net/index.php/FormView
* @access public
*/
class FormView  {

	/**
	* Instance of FormComponent found in template
	* @var FormComponent
	* @access public
	*/	
    var $Form;

    /**
    * @access Protected
    * @var Template
    */
    var $Template;
    
	/**
	* Preserved fields allow the controller to tell the view to
	* preserve the state of certain fields between requests.
	* In a form view, this usually means add hidden fields.
	* @param string template filename
	* @param array form fields to preserve
	* @access public
	*/
    function FormView($TemplateFile = NULL) {
        if (!is_null($TemplateFile)) {
            $this->Template =& new Template($TemplateFile);
            $this->findForm();
        }
    }
    
    /**
	* This only works for templates that contain a single form.
	* @return void
	* @access protected
	*/
    function findForm() {
        $this->Form =& $this->Template->findChildByClass('FormComponent');
        // Need to raise an error here if the form component is not found.
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
    * This method will not be called if this View is participating as
    * a sub view in a composite view.
    * @return void
    * @access public
    */
    function display(&$controller, &$request, &$responseModel) {
        $this->Form->registerDataSource($responseModel);

        // Transfer the list of errors into the form.
        if (!$responseModel->isValid()) {
            $this->Form->setErrors($responseModel->getErrorList());
        }

        $this->prepare($controller, $request, $responseModel);
        $this->Template->display();
    }
    
}
?>