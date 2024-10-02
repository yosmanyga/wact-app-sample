<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_COMPONENT
* @version $Id: form.inc.php,v 1.19 2004/11/17 00:41:37 jeffmoore Exp $
*/

/**
* The FormComponent provide a runtime API for control the behavior of a form
* @see http://wact.sourceforge.net/index.php/FormComponent
* @access public
* @package WACT_COMPONENT
*/
class FormComponent extends TagComponent {
	/**
	* An ErrorList object
	* @var ErrorList
	* @access private
	*/
	var $ErrorList;

	/**
	* Switch to identify whether the form has errors or not
	* @var boolean TRUE means no errors
	* @access private
	*/
	var $IsValid = TRUE;

	/**
	* An indexed array of variable names used to build hidden form fields which
	* are passed on in the next POST request
	* @var array
	* @access private
	*/
	var $StateVars = array();

	/**
	* DataSource object that we delegate to
	* @var array
	* @access private
	*/
	var $_datasource;

	/**
	* @access private
	*/
	function ensureDataSourceAvailable() {
		if (!isset($this->_datasource)) {
			$this->registerDataSource(new DataSpace());
		}
	}

	/**
	* Get the named property from the form DataSource
	* @param string variable name
	* @return mixed value or void if not found
	* @access public
	* @deprecated will probablybe removed in a future reorganization of
	*   how form elements become associated with their values
	*/
	function _getValue($name) {
		$this->ensureDataSourceAvailable();
		return $this->_datasource->get($name);
	}

	/**
	* Set a named property in the form DataSource
	* @param string variable name
	* @param mixed variable value
	* @return void
	* @access public
	* @deprecated will probablybe removed in a future reorganization of
	*   how form elements become associated with their values
	*/
	function _setValue($name, $value) {
		$this->ensureDataSourceAvailable();
		$this->_datasource->set($name, $value);
	}

	/**
	* Initializes the form DataSource
	* (typically this is called for you by controllers)
	* @return void
	* @access public
	*/
	function prepare() {
		$this->ensureDataSourceAvailable();
		$this->_datasource->prepare();
	}

	/**
	* Registers a DataSource with this component
	* (typically this is called for you by controllers)
	* @param object implementing DataSource interface
	* @return void
	* @access public
	*/
	function registerDataSource(&$datasource) {
		$this->_datasource =& $datasource;
	}

	/**
	* Return the DataSource
	* (typically this is called for you by controllers)	
	* @return object implementing DataSource
	* @access public
	*/
	function &getDataSource() {
		return $this->_datasource;
	}

	/**
	* Finds the LabelComponent associated with a form field, allowing 
	* an error message to be displayed next to the field. Called by this
	* setErrors.
	* @param string server id of the form field where the error occurred
	* @param object component below which the LabelComponent can be found
	* @return mixed either a LabelComponent or false if not found
	* @access private
	*/
	function &findLabel($FieldId, &$Component) {
		foreach( array_keys($Component->children) as $key) {
			$Child =& $Component->children[$key];
			if (is_a($Child, 'LabelComponent') && $Child->getAttribute('for') == $FieldId) {
				return $Child;
			} else {
				$result =& $this->findLabel($FieldId, $Child);
				if ($result) {
					return $result;
				}
			}
		}
		return FALSE;
	}

	/**
	* If errors occur, use this method to identify them to the FormComponent.
	* (typically this is called for you by controllers)
	* @see FormController
	* @param ErrorList
	* @return void
	* @access public
	*/
	function setErrors(&$ErrorList) {
		
		// Sets the human readable dictionary corresponding to form fields.
		// Entries in the dictionary defined by displayname attribute of tag
		$ErrorList->setFieldNameDictionary(new FormFieldNameDictionary($this));		
		
		$ErrorList->reset();
		
		while ($ErrorList->next()) {
			$this->IsValid = FALSE;
			
			$Error =& $ErrorList->getError();
			
			// Find the component(s) that the error applies to and tell
			// them there was an error (using their setError() method)
			// as well as notifying related label components if found
			foreach ($Error->FieldList as $tokenName => $fieldName) {
				$Field =& $this->findChild($fieldName);
				if (is_object($Field)) {
					$Field->setError();
					if ($Field->hasAttribute('id')) {
						$Label =& $this->findLabel($Field->getAttribute('id'), $this);
						if ($Label) {
							$Label->setError();
						}
					}
				}
			}
			
		}
		
		$this->ErrorList =& $ErrorList;
	}

	/**
	* Determine whether the form has errors.
	* (typically this is called for you by controllers)
	* @return boolean TRUE if the form has errros
	* @access public
	*/
	function hasErrors() {
		return !$this->IsValid;
	}

	/**
	* Returns the ErrorList if it exists or an EmptyErrorList if not
	* (typically this is called for you by controllers)
	* @return object ErrorList or EmptyErrorList
	* @access public
	*/
	function &getErrorDataSet() {
		if (!isset($this->ErrorList)) {
			require_once WACT_ROOT . 'validation/emptyerrorlist.inc.php';
			$this->ErrorList =& new EmptyErrorList();
		}
		return $this->ErrorList;
	}

	/**
	* Identify a property stored in the DataSource of the component, which
	* should be passed as a hidden input field in the form post. The name
	* attribute of the hidden input field will be the name of the property.
	* Use this to have properties persist between form submits
	* @see renderState()
	* @param string name of property
	* @return void
	* @access public
	*/
	function preserveState($variable) {
		$this->StateVars[] = $variable;
	}

	/**
	* Renders the hidden fields for variables which should be preserved.
	* Called from within a compiled template render function.
	* @todo XHTML: Input fields should be closed
	* @see preserveState()
	* @return void
	* @access public
	*/
	function renderState() {
		foreach ($this->StateVars as $var) {
			echo '<input type="hidden" name="';
			echo $var;
			echo '" value="';
			echo htmlspecialchars($this->_getValue($var), ENT_QUOTES);
			echo '">';
		}
	}
	
}

//--------------------------------------------------------------------------------
/**
* Translates between form name attributes and tag displayname
* attributes (human reabable). Created in FormComponent::setErrors.
* (typically this is handled for you by controllers / form component)
* @see FormComponent::setErrors()
* @see http://wact.sourceforge.net/index.php/FieldNameDictionary
* @access protected
* @package WACT_VALIDATION
*/
class FormFieldNameDictionary {

	/**
	* @var FormComponent
	* @access private
	*/
	var $form;

	/**
	* @param FormComponent
	* @access protected
	*/
	function FormFieldNameDictionary(&$form) {
		$this->form =& $form;
	}

	/**
	* @param string name attribute of the field
	* @return string displayname attribute of the field
	* @access protected
	*/
	function getFieldName($fieldName) {
		$Field =& $this->form->findChild($fieldName);
		if (is_object($Field)) {
			return $Field->getDisplayName();
		} else {
			return $fieldName;
		}
	}
}


//--------------------------------------------------------------------------------
/**
* Base class for concrete form elements
* @see http://wact.sourceforge.net/index.php/FormElement
* @access public
* @abstract
* @package WACT_COMPONENT
*/
class FormElement extends TagComponent {

	/**
	* Whether the form element has validated successfully (default TRUE)
	* @var boolean
	* @access private
	*/
	var $IsValid = TRUE;

	/**
	* Human reable name of the form element determined by
	* tag displayname attribute
	* @var string
	* @access protected
	*/
	var $displayname;

	/**
	* CSS class attribute the element should display if there is an error
	* Determined by tag errorclass attribute
	* @var string
	* @access private
	*/
	var $errorclass;

	/**
	* CSS style attribute the element should display if there is an error
	* Determined by tag errorstyle attribute
	* @var string
	* @access private
	*/
	var $errorstyle;

	/**
	* Returns a value for the name attribute. If $this->displayname is not
	* set, returns either the title, alt or name attribute (in that order
	* of preference, defined for the tag
	* (typically this is called for you by controllers)
	* @return string
	* @access protected
	*/
	function getDisplayName() {
		if (isset($this->displayname)) {
			return $this->displayname;
		} else if ($this->hasAttribute('title')) {
			return $this->getAttribute('title');
		} else if ($this->hasAttribute('alt')) {
			return $this->getAttribute('alt');
		} else {
			return str_replace("_", " ", $this->getAttribute('name'));
		}
	}

	/**
	* Returns true if the form element is in an error state
	* (typically this is called for you by controllers)
	* @return boolean
	* @access protected
	*/
	function hasErrors() {
		return !$this->IsValid;
	}

	/**
	* Puts the element into the error state and assigns the error class or
	* style attributes, if the corresponding member vars have a value
	* (typically you shouldn't need to call this)
	* @see FormComponent::setErrors()
	* @return boolean
	* @access protected
	*/
	function setError() {
		$this->IsValid = FALSE;
		if (isset($this->errorclass)) {
			$this->setAttribute('class', $this->errorclass);
		}
		if (isset($this->errorstyle)) {
			$this->setAttribute('style', $this->errorstyle);
		}
	}

	/**
	* Returns the value of the form element  (it's value in the form DataSource)
	* @return string
	* @access public
	*/
	function getValue() {
		$FormComponent =& $this->findParentByClass('FormComponent');
		return $FormComponent->_getValue($this->getAttribute('name'));
	}
	
	/**
	* Sets the value of the form element  (it's value in the form DataSource)
	* @param string value
	* @return void
	* @access public
	*/
	function setValue($value) {
		$FormComponent =& $this->findParentByClass('FormComponent');
		return $FormComponent->_setValue($this->getAttribute('name'),$value);
	}

	/**
	* Overrides TagComponent method so that requests for the value of
	* the attribute named "value" return the value from the FormComponent
	* DataSource, if it exists. This implementation is overridden itself
	* in CheckableFormElement
	* @param string attribute name
	* @return string attribute value
	* @access public
	*/
	function getAttribute($name) {
		if ( strcasecmp($name,'value') == 0 ) {
			if ( !is_null($value = $this->getValue()) ) {
				return $value;
			}
		}
		return parent::getAttribute($name);
	}
	
	/**
	* Overrides TagComponent method so keep value attribute and value
	* in form DataSource in sync
	* @param string attribute name
	* @param string attribute value
	* @return void
	* @access public
	*/
	function setAttribute($name,$value) {
		if ( strcasecmp($name,'value') == 0 ) {
			$this->setValue($value);
		}
		parent::setAttribute($name,$value);
	}

}

//--------------------------------------------------------------------------------
/**
* Inherited by InputTextComponent to make sure they
* have a value attribute
* @see http://wact.sourceforge.net/index.php/InputFormElement
* @access public
* @abstract
* @package WACT_COMPONENT
*/
class InputFormElement extends FormElement {

	/**
	* Overrides then calls with the parent renderAttributes() method. Makes
	* sure there is always a value attribute, even if it's empty.
	* Called from within a compiled template render function.
	* @todo XHTML: Null attributes need a value
	* @return void
	* @access protected
	*/
	function renderAttributes() {
		$value = $this->getValue();
		if (!is_null($value)) {
			$this->setAttribute('value', $value);
		} else {
			$this->setAttribute('value', '');
		}
		parent::renderAttributes();
	}
	
}

//--------------------------------------------------------------------------------
/**
* Represents an HTML label tag
* @see http://wact.sourceforge.net/index.php/LabelComponent
* @access public
* @package WACT_COMPONENT
*/
class LabelComponent extends TagComponent {

	/**
	* CSS class attribute to display on error
	* Determined by tag errorclass attribute
	* @var string
	* @access private
	*/
	var $errorclass;
	
	/**
	* CSS style attribute to display on error
	* Determined by tag errorstyle attribute
	* @var string
	* @access private
	*/
	var $errorstyle;

	/**
	* If either are set, assigns the attributes for error class or style
	* @see FormComponent::setErrors
	* @return void
	* @access protected
	*/
	function setError() {
		if (isset($this->errorclass)) {
			$this->setAttribute('class', $this->errorclass);
		}
		if (isset($this->errorstyle)) {
			$this->setAttribute('style', $this->errorstyle);
		}
	}

}

//--------------------------------------------------------------------------------
/**
* Represents an HTML input type="radio" tag
* Represents an HTML input type="checkbox" tag
* @see http://wact.sourceforge.net/index.php/CheckableFormElement
* @access public
* @package WACT_COMPONENT
*/
class CheckableFormElement extends FormElement {

	/**
	* Routes call to TagComponent::getAttribute
	* @param string attribute name
	* @return string attribute value
	* @access public
	*/
	function getAttribute($name) {
		// Can't think of a smarter way do this. Would be nice if TagComponent
		// wasnt hard coded but rather we skip FormElement
		return TagComponent::getAttribute($name);
	}
	
	/**
	* Routes call to TagComponent::setAttribute
	* @param string attribute name
	* @param string attribute value
	* @return void
	* @access public
	*/
	function setAttribute($name,$value) {
		TagComponent::setAttribute($name,$value);
	}

	/**
	* Overrides then calls with the parent renderAttributes() method dealing
	* with the special case of the checked attribute
	* Called from compiled template
	* @todo XHTML: Need checked="checked"
	* @return void
	* @access public
	*/
	function renderAttributes() {
		$value = $this->getValue();
		if ($value == $this->getAttribute('value')) {
			$this->setAttribute('checked', NULL);
		} else {
			$this->removeAttribute('checked');
		}
		parent::renderAttributes();
	}
	
}

//--------------------------------------------------------------------------------
/**
* Represents an HTML textarea tag
* @see http://wact.sourceforge.net/index.php/TextAreaComponent
* @access public
* @package WACT_COMPONENT
*/
class TextAreaComponent extends FormElement {

	/**
	* Output the contents of the textarea, passing through htmlspecialchars().
	* Called from within a compiled template's render function
	* @return void
	* @access protected
	*/
	function renderContents() {
		echo htmlspecialchars($this->getValue(), ENT_QUOTES);
	}
	
}
?>