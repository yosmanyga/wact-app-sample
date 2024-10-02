<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_COMPONENT
* @version $Id: select.inc.php,v 1.4 2004/11/17 00:41:37 jeffmoore Exp $
*/

require_once WACT_ROOT . 'template/components/form/form.inc.php';

/**
* Represents an HTML select multiple tag where multiple options
* can be selected
* @see http://wact.sourceforge.net/index.php/SelectMultipleComponent
* @access public
* @package WACT_COMPONENT
*/
class SelectMultipleComponent extends FormElement {
	/**
	* A associative array of choices to build the option list with
	* @var array
	* @access private
	*/
	var $choiceList = array();
	/**
	* The object responsible for rendering the option tags
	* @var object
	* @access private
	*/
	var $optionHandler;

	/**
	* Override FormElement method to deal with name attributes containing
	* PHP array syntax.
	* @return array the contents of the value
	* @access private
	*/
	function getValue() {
		$FormComponent =& $this->findParentByClass('FormComponent');
		$name = str_replace('[]', '', $this->getAttribute('name'));
		return $FormComponent->_getValue($name);
	}

	/**
	* Sets the choice list. Passed an associative array, the keys become the
	* contents of the option value attributes and the values in the array
	* become the text contents of the option tag e.g.
	* <code>
	* $choices = array ( 4 => 'red', 5=>'blue', 6=>'green' );
	* </code>
	* ...becomes...
	* <pre>
	* <select multiple>
	*   <option value="4">red</option>
	*   <option value="5">blue</option>
	*   <option value="6">green</option>
	* </select>
	* </pre>
	* @see setSelection()
	* @param array
	* @return void
	* @access public
	*/
	function setChoices($choiceList) {
		$this->choiceList = $choiceList;
	}

	/**
	* Sets a list of values to be displayed as selected. These should
	* correspond to the <i>keys</i> of the array passed to setChoices()
	* e.g.
	* <code>
	* $choices = array ( 4 => 'red', 5=>'blue', 6=>'green' );
	* $selections = array ( 4, 6 );
	* </code>
	* ...becomes...
	* <pre>
	* <select multiple>
	*   <option value="4" selected>red</option>
	*   <option value="5">blue</option>
	*   <option value="6" selected>green</option>
	* </select>
	* </pre>
	* @see setChoices()
	* @param array indexed array of selected options
	* @return void
	* @access public
	*/
	function setSelection($selection) {
		$FormComponent =& $this->findParentByClass('FormComponent');
		$name = str_replace('[]', '', $this->getAttribute('name'));
		$FormComponent->_setValue($name, $selection);
	}

	/**
	* Sets object responsible for rendering the options
	* Supply your own OptionRenderer if the default
	* is too simple
	* @see OptionRenderer
	* @param object
	* @return void
	* @access public
	*/
	function setOptionRenderer($optionHandler) {
		$this->optionHandler = $optionHandler;
	}

	/**
	* Renders the contents of the the select tag, option tags being built by
	* the option handler. Called from with a compiled template render function.
	* @return void
	* @access public
	*/
	function renderContents() {
		$values = $this->getValue();

		if ( !is_array($values) ) {
			$values = array(reset($this->choiceList));
		} else {
			$found = false;
			foreach ( $values as $value ) {
				if ( array_key_exists($value,$this->choiceList) ) {
					$found = true;
					break;
				}
			}
			if ( !$found )
				$values = array(reset($this->choiceList));
		}

		if (empty($this->optionHandler)) {
			$this->optionHandler = new OptionRenderer();
		}

		foreach($this->choiceList as $key => $contents) {
			if ( $key === 0 ) {
				$key = '0';
			}
			$this->optionHandler->renderOption($key, $contents, in_array($key,$values));
		}
	}
}

//--------------------------------------------------------------------------------
/**
* Deals with rendering option elements for HTML select tags
* Simple renderer for OPTIONs.  Does not support disabled
* and label attributes. Does not support OPTGROUP tags.
* @see http://wact.sourceforge.net/index.php/OptionRenderer
* @access public
* @package WACT_COMPONENT
*/
class OptionRenderer {

	/**
	* Renders an option, sending directly to display.
	* Called from SelectSingleComponent or SelectMultipleComponent
	* in their renderContents() method
	* @todo XTHML: selected="selected"
	* @param string value to place within the option value attribute
	* @param string contents of the option tag
	* @param boolean whether the option is selected or not
	* @return void
	* @access private
	*/
	function renderOption($key, $contents, $selected) {
		echo '<option value="';
		echo htmlspecialchars($key, ENT_QUOTES);
		echo '"';
		if ($selected) {
			echo " selected";
		}
		echo '>';
		if (empty($contents)) {
			echo htmlspecialchars($key, ENT_QUOTES);
		} else {
			echo htmlspecialchars($contents, ENT_QUOTES);
		}
		echo '</option>';
	}
}

//--------------------------------------------------------------------------------
/**
* Represents an HTML select tag where only a single option can
* be selected
* @see http://wact.sourceforge.net/index.php/SelectSingleComponent
* @access public
* @package WACT_COMPONENT
*/
class SelectSingleComponent extends FormElement {

	/**
	* A associative array of choices to build the option list with
	* @var array
	* @access private
	*/
	var $choiceList = array();
	
	/**
	* The object responsible for rendering the option tags
	* @var object
	* @access private
	*/
	var $optionHandler;

	/**
	* Sets the choice list. Passed an associative array, the keys become the
	* contents of the option value attributes and the values in the array
	* become the text contents of the option tag e.g.
	* <code>
	* $choices = array ( 4 => 'red', 5=>'blue', 6=>'green' );
	* </code>
	* ...becomes...
	* <pre>
	* <select>
	*   <option value="4">red</option>
	*   <option value="5">blue</option>
	*   <option value="6">green</option>
	* </select>
	* </pre>
	* @see setSelection()
	* @param array
	* @return void
	* @access public
	*/
	function setChoices($choiceList) {
		$this->choiceList = $choiceList;
	}

	/**
	* Sets a single option to be displayed as selected. Value
	* should correspond to a key in the array passed to
	* setChoices() e.g.
	* <code>
	* $choices = array ( 4 => 'red', 5=>'blue', 6=>'green' );
	* $selection = 5;
	* </code>
	* ...becomes...
	* <pre>
	* <select multiple>
	*   <option value="4">red</option>
	*   <option value="5" selected>blue</option>
	*   <option value="6">green</option>
	* </select>
	* </pre>	
	* @see setChoices()
	* @param string option which is selected
	* @return void
	* @access public
	*/
	function setSelection($selection) {
		$FormComponent =& $this->findParentByClass('FormComponent');
		$FormComponent->_setValue($this->getAttribute('name'), $selection);
	}

	/**
	* Sets object responsible for rendering the options
	* Supply your own OptionRenderer if the default
	* is too simple
	* @see OptionRenderer
	* @return void
	* @access public
	*/
	function setOptionRenderer($optionHandler) {
		$this->optionHandler = $optionHandler;
	}

	/**
	* Renders the contents of the the select tag, option tags being built by
	* the option handler. Called from with a compiled template render function.
	* @return void
	* @access protected
	*/
	function renderContents() {
		$value = $this->getValue();
		
		if (empty($value) || !array_key_exists($value, $this->choiceList)) {
			$value = reset($this->choiceList);
		}

		if (empty($this->optionHandler)) {
			$this->optionHandler = new OptionRenderer();
		}

		foreach($this->choiceList as $key => $contents) {
			if ( $key === 0 ) {
				$key = '0';
			}
			$this->optionHandler->renderOption($key, $contents, $key == $value);
		}
	}
}

?>