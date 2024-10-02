<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_CONTROLLERS
* @version $Id: parameter.inc.php,v 1.3 2004/11/13 01:18:16 jeffmoore Exp $
*/
// EXPERIMENTAL

require_once WACT_ROOT . 'controller/controller.inc.php';

class ParameterDispatchController extends Controller {

    var $parameterName;

	function ParameterDispatchController($children = array(), $default = NULL, $parameterName = 'option') {
        parent::Controller();
	    $this->parameterName = $parameterName;
	    $this->children = $children;
	    if (!is_null($default)) {
    	    $this->setDefaultChild($default);
        }
    }
    
    function setParameterName($parameterName = 'option') {
	    $this->parameterName = $parameterName;
    }

    function _appendDispatchInfo(&$pathFragment, &$parameters, $name) {
        if ($name != $this->defaultName) {
            $parameters[$this->parameterName] = $name;
        }
    }
    
    function _getDispatchName(&$request) {
        if ($request->hasParameter($this->parameterName)) {
            return $request->getParameter($this->parameterName);
        } else {
            return $this->defaultName;
        }
    }

}

?>