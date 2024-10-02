<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: template.inc.php,v 1.49 2004/11/20 17:23:17 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* Include dataspace as required
*/
if (!class_exists('DataSpace')) {
	require WACT_ROOT . 'util/dataspace.inc.php';
}

/**
* @see ResolveTemplateCompiledFileName
* @see ResolveTemplateSourceFileName
*/
define('TMPL_IMPORT', 'import');
/**
* @see ResolveTemplateCompiledFileName
* @see ResolveTemplateSourceFileName
*/
define('TMPL_INCLUDE', 'include');
//--------------------------------------------------------------------------------

/**
* @see http://wact.sourceforge.net/index.php/FileScheme
*/
if (!defined('TMPL_FILESCHEME_PATH')) {
	define('TMPL_FILESCHEME_PATH', WACT_ROOT . 'template/fileschemes/simpleroot/');
}
/**
* Include runtime support
*/
require_once TMPL_FILESCHEME_PATH . 'runtimesupport.inc.php';

//--------------------------------------------------------------------------------
/**
* Instantiate global variable $TemplateRender and $TemplateConstruct as arrays
*/
$TemplateRender = array();
$TemplateConstruct = array();

//--------------------------------------------------------------------------------
/**
* Base class for runtime components.<br />
* Note that components that output XML tags should not inherit directly from
* Component but rather the child TagComponent<br />
* Note that in the comments for this class, the terms parent and child
* refer to the given components relative position in a template's
* hierarchy, not to the PHP class hierarchy
* @see http://wact.sourceforge.net/index.php/Component
* @access public
* @abstract
* @package WACT_COMPONENT
*/
class Component {
	/**
	* Array of child components
	* @var array of component objects
	* @access private
	*/
	var $children = array();
	
	/**
	* Parent component - "parent" refers to nesting in template
	* not to class hierarchy.
	* @var object component object
	* @access private
	*/
	var $parent;
	
	/**
	* ID of component, corresponding to it's ID attribute in the template
	* @var string
	* @access private
	*/
	var $id;

	/**
	* Does this component support dynamic rendering?
	* @var object component object
	* @access private
	*/
	var $IsDynamicallyRendered = FALSE;

	/**
	* Returns the ID of the component, as defined in the template tags
	* ID attribute
	* @return string
	* @access public
	*/
	function getServerId() {
		return $this->id;
	}

	/**
	* Returns a child component given it's ID.<br />
	* Note this is a potentially expensive operation if dealing with
	* many components, as it calls the findChild method of children
	* based on alphanumeric order: strcasecmp(). Attempt to call it via
	* the nearest known component to the required child.
	* @param string id
	* @return mixed refernce to child component object or FALSE if not found
	* @access public
	*/
	function &findChild($ServerId) {
		foreach( array_keys($this->children) as $key) {
			if (strcasecmp($key, $ServerId)) {
				$result =& $this->children[$key]->findChild($ServerId);
				if ($result) {
					return $result;
				}
			} else {
				return $this->children[$key];
			}
		}
		return FALSE;
	}

	/**
	* Same as find child, except raises error if child is not found
	* @param string id
	* @return object refernce to child component object
	* @access public
	*/
	function &getChild($ServerId) {
		$result =& $this->findChild($ServerId);
		if (!is_object($result)) {
			RaiseError('compiler', 'COMPONENTNOTFOUND', array(
				'ServerId' => $ServerId));
		}
		return $result;
	}
	
	/**
	* set the data source of a child component, or raise an error
	* if the child is not found.
	* @param string path
	* @param DataSource object
	* @access public
	*/
	function setChildDataSource($path, &$datasource) {
		$child =& $this->getChild($path);
		$child->registerDataSource($datasource);
	}

	/**
	* Returns the first child component matching the supplied WACT_TEMPLATE
	* Component PHP class name<br />
	* @param string component class name
	* @return mixed reference to child component object or FALSE if not found
	* @access public
	*/
	function &findChildByClass($class) {
		foreach( array_keys($this->children) as $key) {
			if (is_a($this->children[$key], $class)) {
				return $this->children[$key];
			} else {
				$result =& $this->children[$key]->findChildByClass($class);
				if ($result) {
					return $result;
				}
			}
		}
		return FALSE;
	}

	/**
	* Recursively searches through parents of this component searching
	* for a given WACT_TEMPLATE component PHP class name
	* @param string component class name
	* @return mixed reference to parent component object or FALSE if not found
	* @access public
	*/
	function &findParentByClass($class) {
		$Parent =& $this->parent;
		while ($Parent && !is_a($Parent, $class)) {
			$Parent =& $Parent->parent;
		}
		return $Parent;
	}

	/**
	* Adds a reference to a child component to this component, using it's
	* ID attribute as the child array key
	* @param object child component
	* @param string value for ID attribute
	* @return void
	* @access public
	*/
	function addChild(&$Child, $ServerId = NULL) {
		if (is_null($ServerId)) {
			static $genid = 1;
			$ServerId = 'widxxx_' . $genid;
			$genid++;
		}
		$Child->parent =& $this;
		$Child->id = $ServerId;
		$this->children[$ServerId] =& $Child;
	}

	/**
	* Outputs the component, rendering any child components as well
	* This method will only ever be called on components that support
	* Dynamic rendering.
	* @return void
	* @access public
	*/
	function render() {
		foreach( array_keys($this->children) as $key) {
			if ($this->children[$key]->IsDynamicallyRendered) {
				$this->children[$key]->render();
			}
		}
	}
	
}

//--------------------------------------------------------------------------------
/**
* Base class for runtime components that output XML tags
* @see http://wact.sourceforge.net/index.php/TagComponent
* @access public
* @abstract
* @package WACT_COMPONENT
*/
class TagComponent extends Component {
	/**
	* Array of XML attributes
	* @var array
	* @access private
	*/
	var $attributes = array();

	/**
	* Returns the value of the ID attribute
	* @param string component class name
	* @return string
	* @access public
	*/
	function getClientId() {
		return $this->getAttribute('id');
	}

	/**
	* returns the case-preserving, case-insensitive name of an attribute
	* @param string name of attribute
	* @return string canonical name of attribute
	* @access private
	*/
	function getCanonicalAttributeName($attrib) {
		// quick check if they happen to use the same case.
		if (array_key_exists($attrib, $this->attributes)) {
			return $attrib;
		}
		
		// slow check
		foreach(array_keys($this->attributes) as $key) {
			if (strcasecmp( $attrib, $key) == 0) {
				return $key;
			}
		}
		
		return $attrib;
	}

	/**
	* Sets an attribute
	* @param string name of attribute
	* @param string value of attribute
	* @return void
	* @access public
	*/
	function setAttribute($attrib, $value) {
		$attrib = $this->getCanonicalAttributeName($attrib);
		$this->attributes[$attrib] = $value;
	}
	
	/**
	* Returns the value of an attribute, given it's name
	* @param string name of attribute
	* @return string value of attribute
	* @access public
	*/
	function getAttribute($attrib) {
		$attrib = $this->getCanonicalAttributeName($attrib);
		if (isset($this->attributes[$attrib])) {
			return $this->attributes[$attrib];
		}
	}

	/**
	* Remove an attribute from the list
	* @param string name of attribute
	* @return void
	* @access public
	*/
	function removeAttribute($attrib) {
		$attrib = $this->getCanonicalAttributeName($attrib);
		unset($this->attributes[$attrib]);
	}
	
	/**
	* Check to see whether a named attribute exists
	* @param string name of attribute
	* @return boolean
	* @access public
	*/
	function hasAttribute($attrib) {
		$attrib = $this->getCanonicalAttributeName($attrib);
		return array_key_exists($attrib, $this->attributes);
	}

	/**
	* Writes the contents of the attributes to the screen, using
	* htmlspecialchars to convert entities in values. Called by
	* a compiled template
	* @return void
	* @access public
	*/
	function renderAttributes() {
		foreach ($this->attributes as $name => $value) {
			echo ' ';
			echo $name;
			if (!is_null($value)) {
				echo '="';
				echo htmlspecialchars($value, ENT_QUOTES);
				echo '"';
			}
		}
	}
}

//--------------------------------------------------------------------------------
/**
* Base class for runtime components that hold data
* @see http://wact.sourceforge.net/index.php/TagComponent
* @access public
* @abstract
* @package WACT_COMPONENT
*/
class DataSourceComponent extends Component {
	/**
	* DataSource object that we delegate to
	* @var array
	* @access private
	*/
	var $_datasource;

	function ensureDataSourceAvailable() {
		if (!isset($this->_datasource)) {
			$this->registerDataSource(new DataSpace());
		}
	}

    /**
	* @deprecated slated for removal; here for BC purposes only
	*/
	function set($name, $value) {
		$this->ensureDataSourceAvailable();
		$this->_datasource->set($name, $value);
	}

    /**
	* @deprecated slated for removal; here for BC purposes only
	*/
	function get($name) {
		$this->ensureDataSourceAvailable();
		return $this->_datasource->get($name);
	}

    /**
	* @deprecated slated for removal; here for BC purposes only
	*/
	function prepare() {
		$this->ensureDataSourceAvailable();
		$this->_datasource->prepare();
	}

    /**
	* Registers a DataSource with this component
	* @param object 
	* @return void
	* @access public
	*/
	function registerDataSource(&$datasource) {
		$this->_datasource =& $datasource;
	}
	
	function &getDataSource() {
		return $this->_datasource;
	}
	
}

//--------------------------------------------------------------------------------
/**
* Used to fetch data serialized in a var file
* @see http://wact.sourceforge.net/index.php/importVarFile
* @param filename of var file (relative or full path)
* @return mixed data stored in var file
* @access protected
*/
function importVarFile($file) {
	
	if (ConfigManager::getOption('config', 'templates', 'forcecompile')) {
		include_once WACT_ROOT . 'template/compiler/varfilecompiler.inc.php';
		CompileVarFile($file);
	}

	$CodeFile = ResolveTemplateCompiledFileName($file, TMPL_IMPORT);

	$data = @readTemplateFile($CodeFile);
	if (!$data) {
		include_once WACT_ROOT . 'template/compiler/varfilecompiler.inc.php';
		CompileVarFile($file);
		$data = readTemplateFile($CodeFile);
	}
	
	return unserialize($data);
	
}

//--------------------------------------------------------------------------------
/**
* Public facade for handling templates, dealing with loading, compiling and
* displaying
* @see http://wact.sourceforge.net/index.php/Template
* @access public
* @package WACT_COMPONENT
*/
class Template extends DataSourceComponent {
	/**
	* Stored the name of the compiled template file
	* @var string
	* @access private
	*/
	var $codefile;
	
	var $file;
	/**
	* Name of function in compiled template which outputs display to screen
	* @var string
	* @access private
	*/
	var $render_function;

	/**
	* Constructs Template
	* @param string name of (source) template file (relative or full path)
	* @access public
	*/
	function Template($file) {
		$this->file = $file;

		$this->codefile = ResolveTemplateCompiledFileName($file, TMPL_INCLUDE);
		if (!isset($GLOBALS['TemplateRender'][$this->codefile])) {

			if (ConfigManager::getOption('config', 'templates', 'forcecompile')) {
				include_once WACT_ROOT . 'template/compiler/templatecompiler.inc.php';
				CompileTemplateFile($file);
			}
			
			$errorlevel = error_reporting();
			error_reporting($errorlevel & ~E_WARNING);
			$found = include_once($this->codefile);
			error_reporting($errorlevel);
			if (!$found) {
				include_once WACT_ROOT . 'template/compiler/templatecompiler.inc.php';
				CompileTemplateFile($file);
				include_once($this->codefile);
			}
		}
		$this->render_function = $GLOBALS['TemplateRender'][$this->codefile];
		$func = $GLOBALS['TemplateConstruct'][$this->codefile];
		$func($this);
	}

	/**
	* return a DataSource for the name property.  The property should
	* be an array or a DataSource object.
	* If the property is not found or is not an array or object, then
	* an empty DataSource will be returned.
	* @param string name of property
	* @return DataSource 
	* @access public
	*/
	function &_dereference($DataSource, $name) {
		$value = $DataSource->get($name);
		$ref =& DataSpace::makeDataSpace($value);
		if (is_null($ref)) {
			require_once WACT_ROOT . 'util/emptydataset.inc.php';
			return new EmptyDataSet();
		}
		return $ref;
	}

	/**
	* Outputs the template, calling the compiled templates render function
	* @return void
	* @access public
	*/
	function display() {
		$func = $this->render_function;
		$func($this);
	}
	
	function capture() {
		ob_start();
		$this->display();
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
}
?>