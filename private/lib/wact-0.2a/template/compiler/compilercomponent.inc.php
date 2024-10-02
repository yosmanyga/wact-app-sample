<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: compilercomponent.inc.php,v 1.46 2004/12/02 03:27:26 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* Base class for compile time components. Compile time component methods are
* called by the template parser SourceFileParser.<br />
* Note this in the comments for this class, parent and child refer to the XML
* heirarchy in the template, as opposed to the PHP class tree.
* @see SourceFileParser
* @see http://wact.sourceforge.net/index.php/CompilerComponent
* @access public
* @abstract
* @package WACT_TEMPLATE
*/
class CompilerComponent {
	/**
	* XML attributes of the tag
	* @var array
	* @access private
	*/
	var $attributeNodes = array();
	
	/**
	* Child compile-time components
	* @var array of compile time component objects
	* @access private
	*/
	var $children = array();
	
	/**
	* A list of properties for this component
	* @var array
	* @access private
	*/
	var $properties = array();
	
	/**
	* Parent compile-time component
	* @var object subclass of CompilerComponent
	* @access private
	*/
	var $parent = NULL;
	
	/**
	* Stores the identifying component ID
	* @var string value of id attribute
	* @access private
	*/
	var $ServerId;
	
	/**
	* Name of the XML tag as it appears in the template. This would include
	* the namespace prefix, if applicable.
	* @var string tag name
	* @access private
	*/
	var $tag = '';
	
	/**
	* Used to identify the source template file, when generating compile time
	* error messages.
	* @var string source template filename
	* @access private
	*/
	var $SourceFile;
	
	/**
	* Used to indentify the line number where a compile time error occurred.
	* @var int line number
	* @access private
	*/
	var $StartingLineNo;

	/**
	* Defines whether the tag is allowed to have a closing tag
	* @var boolean
	* @access private
	*/
	var $hasClosingTag = TRUE;

	/**
	* Whether the was empty and closed such as <br />
	* @var boolean
	* @access private
	*/
	var $emptyClosedTag = FALSE;

	/**
	* Counter for plain text tags matching the current tag component
	* name. Used to prevent premature closing. See bug 906138
	* @var int
	* @access private
	*/
	var $plainTagCount = 0;

	/**
	* TagInfo metadata for this component
	* @var TagInfo
	*/
	var $TagInfo = NULL;

	/**
	* Instance of a CoreWrapTag
	* @see CoreWrapTag
	* @var CoreWrapTag
	* @access private
	*/
	var $WrappingComponents = array();

	/**
	* Set a wrapping component for this component
	* @param object 
	* @return void
	* @access protected
	*/
    function registerWrapper(&$wrapper) {
        $this->WrappingComponents[] =& $wrapper;
    }

	/**
	* Sets the XML attributes for this component (as extracted from the
	* template)
	* @param object XML attributes
	* @return void
	* @access protected
	*/
	function addChildAttribute(&$child) {
	    $attrib = strtolower($child->name);
	    if (isset($this->attributeNodes[$attrib])) {
			RaiseError('compiler', 'DUPLICATEATTRIBUTE', array(
				'attribute' => $attrib,
				'tag' => $this->tag,
				'file' => $this->SourceFile,
				'line' => $this->StartingLineNo));
	    }
		$this->attributeNodes[$attrib] =& $child;
	}

	/**
	* Sets an attribute
	* @param string name of attribute
	* @param string value of attribute
	* @return void
	* @access public
	*/
	function setAttribute($attrib, $value) {
		$Component =& new AttributeNode($attrib, $value);
		$this->addChildAttribute($Component);
    }

	/**
	* Returns the value of an XML attribute (as extracted from template) or
	* NULL if attribute not found
	* @param string attribute name
	* @return mixed string attribute value or null
	* @access public
	*/
	function getAttribute($attrib) {
		if ( isset($this->attributeNodes[strtolower($attrib)]) ) {
			return $this->attributeNodes[strtolower($attrib)]->getValue();
		}
	}
	
	/**
	* Check to see whether a named attribute exists
	* @param string name of attribute
	* @return boolean
	* @access public
	*/
	function hasAttribute($attrib) {
		return isset($this->attributeNodes[strtolower($attrib)]);
	}

	/**
	* Return the value of a boolean attribute as a boolean.
	* ATTRIBUTE=ANYTHING  (true)
	* ATTRIBUTE=(FALSE|N|NA|NO|NONE|0) (false)
	* ATTRIBUTE (true)
	* (attribute unspecified) (default)
	* @param string attribute name
	* @param boolean value to return if attribute is not found
	* @return boolean
	* @access public
	*/
    function getBoolAttribute($attrib, $default = FALSE) {
		if ( isset($this->attributeNodes[strtolower($attrib)]) ) {
			switch (strtoupper($this->attributeNodes[strtolower($attrib)]->getValue())) {
			case 'FALSE':
			case 'N':
			case 'NO':
			case 'NONE':
			case 'NA':
			case '0':
				return false;
			default:
				return true;
			}
		} else {
		    return $default;
        }
	}
	
	/**
	* Remove an attribute from the list
	* @param string name of attribute
	* @return void
	* @access public
	*/
	function removeAttribute($attrib) {
        unset($this->attributeNodes[strtolower($attrib)]);
	}

	/**
	* Returns an array containing the attributes of this component that
	* can be resolved at compile time.
	* @return array representation of attributes
	* @access protected
	*/
	function getAttributesAsArray($suppress = array()) {
	    $suppress = array_map('strtolower', $suppress);
		$attributes = array();
		foreach( array_keys($this->attributeNodes) as $key) {
		    if (!in_array($key, $suppress) && $this->attributeNodes[$key]->isConstant()) {
    		    $attributes[$this->attributeNodes[$key]->name] = 
        		    $this->attributeNodes[$key]->getValue();
    		    $this->getAttribute($key);
            }
        }
        return $attributes;
	}

    function generateAttributeList(&$code, $suppress = array()) {
	    $suppress = array_map('strtolower', $suppress);
		foreach( array_keys($this->attributeNodes) as $key) {
		    if (!in_array($key, $suppress)) {
		        $this->attributeNodes[$key]->generate($code);
            }
		}
    }

    function generateDynamicAttributeList(&$code, $suppress = array()) {
	    $suppress = array_map('strtolower', $suppress);
		foreach( array_keys($this->attributeNodes) as $key) {
		    if (!in_array($key, $suppress) && !$this->attributeNodes[$key]->isConstant()) {
		        $this->attributeNodes[$key]->generate($code);
            }
		}
    }
    
	/**
	* register a property with this component.  Currently, this
	* component must be a database to support properties.  This may
	* change.
	* @access public
	*/
    function registerProperty($name, &$property) {
        $this->properties[$name] =& $property;
    }

	/**
	* @return CompilerProperty returns the named property or NULL if it doesn't exist
	* @access public
	*/
    function &getProperty($name) {
        if (array_key_exists($name, $this->properties)) {
            return $this->properties[$name];
        } else {
            if ($this->isDataSource()) {
                return NULL;
            } else {
                return $this->parent->getProperty($name);
            }
        }
    }

	/**
	* Get the value of the XML id attribute
	* @return string value of id attribute
	* @access protected
	*/
	function getClientId() {
		if ( $this->hasAttribute('id') ) {
			return $this->getAttribute('id');
		}
	}

	/**
	* Returns the identifying server ID. It's value it determined in the
	* following order;
	* <ol>
	* <li>The XML id attribute in the template if it exists</li>
	* <li>The value of $this->ServerId</li>
	* <li>An ID generated by the getNewServerId() function</li>
	* </ol>
	* @see getNewServerId
	* @return string value identifying this component
	* @access protected
	*/
	function getServerId() {
		if ($this->hasAttribute('id')) {
			return $this->getAttribute('id');
		} else if (!empty($this->ServerId)) {
			return $this->ServerId;
		} else {
			$this->ServerId = getNewServerId();
			return $this->ServerId;
		}
	}

	/**
	* Adds a child component, by reference, to the array of children
	* @param object instance of a compile time component
	* @return void
	* @access protected
	*/
	function addChild(&$child) {
		$child->parent =& $this;
		$this->children[] =& $child;
	}

	/**
	* Removes a child component, given it's ServerID
	* @param string server id
	* @return mixed if child is found, returns a reference to it or void
	* @access protected
	*/
	function &removeChild($ServerId) {
		foreach( array_keys($this->children) as $key) {
			$child =& $this->children[$key];
			if ($child->getServerid() == $ServerId) {
				unset($this->children[$key]);
				return $child;
			}
		}
	}

	/**
	* Returns the last child added to a component
	* @return mixed last child instance or false if no children
	* @access protected
	*/
	function &getLastChild() {
		// end() doesn't return a reference!
		$end = count($this->children)-1;
		if ( $end >= 0 ) {
			return $this->children[$end];
		}
		return FALSE;
	}

	/**
	* Returns a child component, given it's ServerID
	* @param string server id
	* @return mixed if child is found, returns a reference of false
	* @access protected
	*/
	function &findChild($ServerId) {
		foreach( array_keys($this->children) as $key) {
			if ($this->children[$key]->getServerid() == $ServerId) {
				return $this->children[$key];
			} else {
				$result =& $this->children[$key]->findChild($ServerId);
				if ($result) {
					return $result;
				}
			}
		}
		return FALSE;
	}

	/**
	* Returns a child component, given it's compile time component class
	* @param string PHP class name
	* @return mixed if child is found, returns a reference of false
	* @access protected
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
	* Returns an array of child components, given it's compile time component class
	* @param string PHP class name
	* @return array
	* @access protected
	*/
	function findChildrenByClass($class) {
		$ret = array();
		foreach( array_keys($this->children) as $key) {
			if (is_a($this->children[$key], $class)) {
				$ret[] =& $this->children[$key];
			} else {
				$more_children = $this->children[$key]->findChildrenByClass($class);
				if (count($more_children)) {
					$ret = array_merge($ret, $more_children);
				}
			}
		}
		return $ret;
	}

	/**
	* Returns a child component, given it's compile time component class
	* @param string PHP class name
	* @return mixed if child is found, returns a reference of false
	* @access protected
	*/
	function &findImmediateChildByClass($class) {
		foreach( array_keys($this->children) as $key) {
			if (is_a($this->children[$key], $class)) {
				return $this->children[$key];
			}
		}
		return FALSE;
	}

	/**
	* Returns a parent component, recursively searching parents by their
	* compile time component class name
	* @param string PHP class name
	* @return mixed if parent is found, returns a reference of void
	* @access protected
	*/
	function &findParentByClass($class) {
		$Parent =& $this->parent;
		while ($Parent && !is_a($Parent, $class)) {
			$Parent =& $Parent->parent;
		}
		return $Parent;
	}

	/**
	* Extends findParentByClass to begin search at the <i>current</i> component
	* <i>then</i> moving on to its parent, if there's no match. This is called
	* from TagJudge to determine known children.
	* @param string class name
	* @return mixed if parent is found, returns a reference of void
	* @access protected
	*/
	function & findSelfOrParentByClass($class) {
		if (is_a($this, $class)) {
			return $this;
		} else {
			return $this->findParentByClass($class);
		}
	}

	/**
	* Calls the prepare method for each child component, which will override
	* this method it it's concrete implementation. In the subclasses, prepare
	* will set up compile time variables. For example the CoreWrapTag uses
	* the prepare method to assign itself as the wrapping component.
	* @see CoreWrapTag
	* @return void
	* @access protected
	*/
	function prepare() {
		foreach( array_keys($this->attributeNodes) as $key) {
			$this->attributeNodes[$key]->prepare();
		}
		foreach( array_keys($this->children) as $key) {
			$this->children[$key]->prepare();
		}
	}

	/**
	* Used to perform some error checking on the source template, such as
	* examining the tag hierarchy and triggering an error if a tag is
	* incorrectly nested. Concrete implementation is in subclasses
	* @return void
	* @access protected
	*/
	function CheckNestingLevel() {
	}

	/**
	* Provides instruction to the template parser, while parsing is in
	* progress, telling it how it should handle the tag. Subclasses of
	* CompilerComponent will return different instructions.<br />
	* Available instructions are;
	* <ul>
	* <li>PARSER_REQUIRE_PARSING - default in this class. Tag must be parsed</li>
	* <li>PARSER_FORBID_PARSING - Tag may not be parsed</li>
	* <li>PARSER_ALLOW_PARSING - Tag may can be parsed</li>
	* </ul>
	* In practice, the parser currently only pays attention to the 
	* PARSER_FORBID_PARSING instruction.<br />
	* Also used to perform error checking on template related to the syntax of
	* the concrete tag implementing this method.
	* @see SourceFileParser
	* @return int PARSER_REQUIRE_PARSING
	* @access protected
	*/
	function preParse() {
		return PARSER_REQUIRE_PARSING;
	}

	/**
	* @return Boolean Indicating whether or not this component is a DataSource
	*/
	function isDataSource() {
	    return FALSE;
	}

	/**
	* If a parent compile time component exists, returns the value of the
	* parent's getDataSource() method, which will be a concrete implementation
	* @return mixed object compile time component if parent exists or void
	* @access protected
	*/
	function &getDataSource() {
	    if (!$this->isDataSource()) {
            if (isset($this->parent)) {
                return $this->parent->getDataSource();
            }
        }
	}

	/**
	* Gets the parent in the DataSource, if one exists
	* @return mixed object compile time data component if exists or void
	* @access protected
	*/
	function &getParentDataSource() {
		$DataSource =& $this->getDataSource();
		if (isset($DataSource->parent)) {
			return $DataSource->parent->getDataSource();
		}
	}

	/**
	* Gets a root DataSource
	* @return mixed object compile time data component if exists or void
	* @access protected
	*/
	function &getRootDataSource() {
		$root =& $this;
		while ($root->parent != NULL) {
			$root =& $root->parent;
		}
		return $root;
	}

	/**
	* Gets the DataSource reference code of the parent
	* @return string
	* @access protected
	*/
	function getDataSourceRefCode() {
		return $this->parent->getDataSourceRefCode();
	}

	/**
	* Gets the component reference code of the parent. This is a PHP string
	* which is used in the compiled template to reference the component in
	* the hierarchy at runtime
	* @return string
	* @access protected
	*/
	function getComponentRefCode() {
		return $this->parent->getComponentRefCode();
	}

	/**
	* Calls the generateConstructor() method of each child component
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateConstructor(&$code) {
		foreach( array_keys($this->children) as $key) {
			$this->children[$key]->generateConstructor($code);
		}
	}

	/**
	* Calls the generate() method of each child component
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateContents(&$code) {
		foreach( array_keys($this->children) as $key) {
			$this->children[$key]->generate($code);
		}
	}

	/**
	* Pre generation method, calls the WrappingComponents
	* generateWrapperPrefix() method if the component exists
	* @see CoreWrapTag
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function preGenerate(&$code) {
		foreach( array_keys($this->WrappingComponents) as $key) {
			$this->WrappingComponents[$key]->generateWrapperPrefix($code);
		}
		foreach( array_keys($this->properties) as $key) {
		    if ($this->properties[$key]->isActive()) {
    			$this->properties[$key]->generateScopeEntry($code);
    		}
		}
	}

	/**
	* Post generation method, calls the WrappingComponents
	* generateWrapperPostfix() method if the component exists
	* @see CoreWrapTag
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function postGenerate(&$code) {
		foreach( array_keys($this->properties) as $key) {
		    if ($this->properties[$key]->isActive()) {
    			$this->properties[$key]->generateScopeExit($code);
    		}
		}
		foreach( array_reverse(array_keys($this->WrappingComponents)) as $key) {
			$this->WrappingComponents[$key]->generateWrapperPostfix($code);
		}
	}

	/**
	* Calls the local preGenerate(), generateContents() and postGenerate()
	* methods.
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generate(&$code) {
		$this->preGenerate($code);
		$this->generateContents($code);
		$this->postGenerate($code);
	}
}
?>