<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------

/**
* @package WACT_CONTROLLERS
* @version $Id: form.inc.php,v 1.5 2004/11/16 01:22:39 jeffmoore Exp $
*/
// EXPERIMENTAL

require_once WACT_ROOT . 'controller/controller.inc.php';

class FormController extends Controller {

    /**
    * Indexed array of Rule objects
    * @see Rule
    * @var array
    * @access private
    */
    var $rules = array();

    var $method = 'POST';
    var $onLoadListeners = array();
    var $onPostBackListener = array();

	function FormController() {
	    parent::Controller();
    }
    
    function isPostback(&$request) {
	    if ($this->method == 'POST') {
	        return !strcasecmp($request->getMethod(), 'POST');
	    } else if ($this->method == 'GET') {
	        return !strcasecmp($request->getMethod(), 'GET') && $request->hasParameters();
	    }
    }

    function _appendDispatchInfo(&$pathFragment, &$parameters, $name) {
        if ($name != $this->defaultName) {
            $parameters[$name] = NULL;
        }
    }

    function _getDispatchName(&$request) {
		foreach( array_keys($this->children) as $key) {
		    if ($request->hasPostProperty($key)) {
		        return $key;
		    }
		}
        return $this->defaultName;
    }

    function postBackRestore(&$request, &$responseModel) {
        //$responseModel->import($request->exportPostProperties());
		$responseModel->merge($request->exportPostProperties());
    }

    /**
    * Registers a Validation Rule
    * @param Rule
    * @return void
    * @access public
    */
    function addRule(&$rule) {
        $this->rules[] =& $rule;
    }

    function registerOnLoadListener(&$listener) {
        $this->onLoadListeners[] =& $listener;
    }

    function registerOnPostBackListener(&$listener) {
        $this->onPostBackListeners[] =& $listener;
    }

	function dispatchEvents(&$request, &$responseModel) {
        if ($this->isPostback($request)) {
            $this->postBackRestore($request, $responseModel);

            $view = $this->triggerEvent($this->onPostBackListeners, 
                array(&$this, &$request, &$responseModel));
            if (is_null($view)) {
                foreach( array_keys($this->rules) as $key) {
                    Handle::resolve($this->rules[$key]);
                    $responseModel->applyRule($this->rules[$key]);
                }
    
                $view = $this->dispatchChild($request, $responseModel);
            }
        } else {
            $view = $this->triggerEvent($this->onLoadListeners, 
                array(&$this, &$request, &$responseModel));
        }
        return $view;        
    }

}

class ButtonController extends Controller {

    var $onClickListeners = array();

    function ButtonController($onClick = NULL) {
        parent::Controller();
        if (is_object($onClick)) {
            $this->registerOnClickListener($onClick);
        }
    }

    function registerOnClickListener(&$listener) {
        $this->onClickListeners[] =& $listener;
    }

	function dispatchEvents(&$request, &$responseModel) {
        return $this->triggerEvent($this->onClickListeners, 
            array(&$this, &$request, &$responseModel));
    }

}
?>