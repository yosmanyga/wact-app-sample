<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_CONTROLLERS
* @version $Id: controller.inc.php,v 1.12 2004/12/02 03:51:18 jeffmoore Exp $
*/
// EXPERIMENTAL

require_once WACT_ROOT . 'controller/request.inc.php';
require_once WACT_ROOT . 'controller/responsemodel.inc.php';
require_once WACT_ROOT . 'util/delegate.inc.php';

/**
* Define state
*/
define('WACT_NO_VIEW', 1);
define('WACT_DEFAULT_VIEW', 2);

/**
* @access public
* @package WACT_CONTROLLERS
*/
class Controller {

	/**
	* Parent controller
	* @access protected
	*/
	var $parent = NULL;

	/**
	* The name of this controller
	* @access protected
	*/
	var $id;

	/**
	* @access private
	*/
    var $onActivateListeners = array();

	/**
	* @access private
	*/
    var $onDeActivateListeners = array();

	/**
	* Array of child controllers
	* @access private
	*/
	var $children = array();

	/**
	* @access protected
	*/
    var $defaultName;

	/**
	* A list of view tokens and their corresponding view handles
	* @var array associative
	* @access protected
	*/
	var $viewMappings = array();

	/**
	* Default View
	* @access protected
	*/
	var $defaultView = NULL;

	/**
	* Constructor
	*/
    function Controller() {
    }

	/**
	* Broadcast an event to listeners
	* @access protected
	*/
    function triggerEvent(&$listener, $args) {
        if (is_object($listener)) {
	        return $listener->invoke($args);
        } else if (is_array($listener)) {
            foreach(array_keys($listener) as $key) {
                $ActionResult = $listener[$key]->invoke($args);
                if (!is_null($ActionResult)) {
                    return $ActionResult;
                }
            }
        }
    }
    
	/**
	* Add a child controller
	* @access public
	*/
    function addChild($name, &$controller) {
        $this->children[$name] =& $controller;
        if (!Handle::isHandle($controller)) {
            $controller->attachToParent($this, $name);
        }
    }

	/**
	* Specify the default child controller by name
	* @access public
	*/
    function setDefaultChild($name) {
        if (!$this->hasChild($name)) {
            RaiseError('runtime', 'INVALID_DEFAULT_NAME',
                array('name' => $name));
        }
        $this->defaultName = $name;
    }

	/**
	* Does this controller have an immediate child of the specified name?
	* @access public
	*/
    function hasChild($name) {
        return isset($this->children[$name]);
    }

	/**
	* Return an instance of the named child controller.
	* If the specified name does not exist, the default controller is returned
	* @access public
	*/
    function &getChild($name) {
        if (!$this->hasChild($name)) {
            $name = $this->defaultName;
        }
        if (is_null($name)) {
            return NULL;
        }
        if (Handle::isHandle($this->children[$name])) {
            Handle::resolve($this->children[$name]);
            $this->children[$name]->attachToParent($this, $name);
        }

        return $this->children[$name];
    }

	/**
	* Add a single mapping
	* @param string token (value returned from an Action)
	* @param mixed Handle for View
	* @return void
	*/
	function addView($token, $view) {
		$this->viewMappings[$token] = $view;
	}

	/**
	* Register a View object handle with the  Controller
	* @see http://wact.sourceforge.net/index.php/View
	* @param mixed a Handle to the View
	* @access public
	*/
	function setDefaultView($view) {
		$this->defaultView = $view;
	}

	/**
	* register a listener to receive activation events
	* @access public
	*/
    function registerOnActivateListener(&$listener) {
        $this->onActivateListeners[] =& $listener;
    }

	/**
	* register a listener to receive activation events
	* @access public
	*/
    function registerOnDeActivateListener(&$listener) {
        $this->onDeActivateListeners[] =& $listener;
    }

	/**
	* Part of the protocol whereby a controller is added to the tree of controllers
	* @access public
	*/
    function attachToParent(&$parent, $name) {
        $this->parent =& $parent;
        $this->id = $name;
    }

	/**
	* Given a request object, determine the name of the child component
	* to dispatch the request to.
	* @access private
	* @abstract
	*/
    function _getDispatchName(&$request) {
        RaiseError('compiler', 'ABSTRACTMETHOD',
            array('method' => __FUNCTION__ .'()', 'class' => __CLASS__));
    }
    
	/**
	* Utility method used in getRealPath
	* @access private
	* @abstract
	*/
    function _appendDispatchInfo(&$pathFragment, &$parameters, $name) {
        RaiseError('compiler', 'ABSTRACTMETHOD',
            array('method' => __FUNCTION__ .'()', 'class' => __CLASS__));
    }

	/**
	* Recursive utility method used in getRealPath
	* @access private
	*/
    function _buildRealPath(&$realPath, &$parameters, $virtualPath) {

        if ($virtualPath == '/') {
            return;
        }
       
        // split into virtualPath and Name
        if (substr($virtualPath, 0, 1) == '/') {
            $pos = strpos($virtualPath, '/', 1);
            if (is_integer($pos)) {
                $name = substr($virtualPath, 1, $pos - 1);
                $virtualPath = substr($virtualPath, $pos);
            } else {
                $name = substr($virtualPath, 1);
                $virtualPath = '';
            }
        } else {
            RaiseError('runtime', 'RELATIVE_UNSUPPORTED',
                array('name' => $virtualPath));
        }

        if (!$this->hasChild($name)) {
            RaiseError('runtime', 'INVALID_DEFAULT_NAME',
                array('name' => $name));
        }

        if (!empty($virtualPath)) {
    	    $child =& $this->getChild($name);
    	    $child->_buildRealPath($realPath, $parameters, $virtualPath);
        }

        $this->_appendDispatchInfo($realPath, $parameters, $name);
    }    

	/**
	* Given a virtual path through the controller heirarchy, return
	* an URL to address that controller.
	* @param string virtual path (to right of front controller script in URL)
	* @return string real path
	* @access public
	*/
	function getRealPath($virtualPath) {
	    if (is_object($this->parent)) {
	        return $this->parent->getRealPath($virtualPath);
	    } else {
            if (is_integer($querypos = strpos($virtualPath, '?'))) {
                $parameterStr = substr($virtualPath, $querypos+1);
                $virtualPath = substr($virtualPath, 0, $querypos); 
            } else {
                $parameterStr = '';
            }

            $Path = '';
            $parameters = array();
            $this->_buildRealPath($Path, $parameters, $virtualPath);
            
            $realPath = $_SERVER['SCRIPT_NAME'];
            if (!empty($Path)) {
                $realPath .= '/' . $Path;
            }
            
            foreach ($parameters as $name => $value) {
                if (empty($parameterStr)) {
                    $parameterStr = $name . '=' . $value;
                } else {
                    $parameterStr .= '&' . $name . '=' . $value;
                }
            }

            if (empty($parameterStr)) {
    	        return $realPath;
            } else {
    	        return $realPath . '?' . $parameterStr;
            }
		}
	}

	/**
	* Dispatch execution to the controller identified by the virtualPath
	* @param string virtual path
	* @return void
	*/
	function forward($virtualPath, &$request, &$responseModel) {
	}

    function dispatchChild(&$request, &$responseModel) {
	    $child =& $this->getChild($this->_getDispatchName($request));
        if (!is_null($child)) {
    	    return $child->handleRequest($request, $responseModel);
        }
    }

    function dispatchEvents(&$request, &$responseModel) {
        return $this->dispatchChild($request, $responseModel);
    }

	/**
	* Receive the HTTP Request event and process it.
	* This involves delegating the handling of the request to the
	* child controllers.
	* The raw request may be passed down to the child controllers,
	* or this method may trigger more fine grained events which
	* handle the request.
	* The child controllers cooporate in handling the event by
	* building a ResponseModel.  The ResponseModel is then
	* rendered by a view to produce an HTTP response for the
	* incoming HTTP request.
	* @access public
	*/
	function handleRequest(&$request, &$responseModel) {
        $view = $this->triggerEvent($this->onActivateListeners, 
            array(&$this, &$request, &$responseModel));
        
        if (is_null($view)) {
            $view = $this->dispatchEvents($request, $responseModel);
        }

        if (is_null($view)) {
            $view = $this->triggerEvent($this->onDeActivateListeners, 
                array(&$this, &$request, &$responseModel));
        }

        if (is_string($view) && array_key_exists($view, $this->viewMappings)) {
            $view = $this->viewMappings[$view];
        }
        if ((is_null($view) || $view == WACT_DEFAULT_VIEW) && is_object($this->defaultView)) {
            $view = $this->defaultView;
        }
        if (is_object($view)) {
			Handle::resolve($view);
            $view->display($this, $request, $responseModel);
			return WACT_NO_VIEW;
        }
        return $view;
    }
	
	/**
	* Begin the process of handling the current PHP request
	* This method may only be called on the root controller of a tree
	* of controllers.  (Page controller or Front controller)
	* @return void
	* @access public
	*/
	function start() {
        // For now, we use a global variable to communicate with our rewriter tags
        $GLOBALS['FrontController'] =& $this;
        $this->handleRequest(new Request(), new ResponseModel());
	}
}

class PageController extends Controller {

    var $onLoadListeners = array();

	function PageController() {
	    parent::Controller();
    }

    function _appendDispatchInfo(&$pathFragment, &$parameters, $name) {
        return;
    }
    
    function _getDispatchName(&$request) {
        return $this->defaultName;
    }

    function registerOnLoadListener(&$listener) {
        $this->onLoadListeners[] =& $listener;
    }

	function dispatchEvents(&$request, &$responseModel) {
        $view =  $this->triggerEvent($this->onLoadListeners, 
            array(&$this, &$request, &$responseModel));
        if (is_null($view)) {
            $view = $this->dispatchChild($request, $responseModel);
        }
        return $view;
    }

}

?>