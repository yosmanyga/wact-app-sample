<?php
//--------------------------------------------------------------------------------
// Copyright 2004 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
 * @package WACT_COMPONENT
 * @version $Id: inputautocomplete.inc.php,v 1.4 2004/11/16 03:07:57 jeffmoore Exp $
 */
//--------------------------------------------------------------------------------
/**
 * Includes
 */
require_once WACT_ROOT . 'template/components/form/form.inc.php';

//--------------------------------------------------------------------------------
/**
 * Runtime component for input text fields with autocompletion.
 * Allows a list of autocompete words to be provided. Use a
 * MemberRule if you want to validate that the data provided was
 * in the list (there's no guarantees - this is JavaScript)
 * @see MemberRule
 * @package WACT_COMPONENT
 * @access public
 */
class InputAutoCompleteComponent extends InputFormElement {
	/**
	* @var array list of words for autocompletion
	* @access private
	*/
	var $autocompletelist = array();

	/**
	* Set the autocomplete list
	* @param array list of words to autocomplete against
	* @return void
	* @access public
	*/
	function setAutoCompleteList($list) {
		$this->autocompletelist = $list;
	}

	/**
	* Returns the autocomplete list (called from the template)
	* @return array list of words to autocomplete against
	* @access public
	*/
	function getAutoCompleteList() {
		$func = create_function('$item','return str_replace(\'"\',\'\"\',$item);');
		$autocompletelist = array_map($func,$this->autocompletelist);
		return $autocompletelist;
	}
}
?>
