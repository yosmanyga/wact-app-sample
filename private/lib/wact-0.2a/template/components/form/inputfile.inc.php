<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_COMPONENT
* @version $Id: inputfile.inc.php,v 1.1 2004/11/16 03:32:33 jeffmoore Exp $
*/

require_once WACT_ROOT . 'template/components/form/form.inc.php';

/**
* Represents an HTML input type="file" tag
*
*   Someday someone is actually going to need to upload something.
*   Maybe then they will come write some nice methods for this
*   tag.
*
* @see http://wact.sourceforge.net/index.php/InputFileComponent
* @access public
* @package WACT_COMPONENT
*/
class InputFileComponent extends InputFormElement {

	/**
	* We can't get a meaningful 'value' attribute for file upload controls
	* after form submission - the value would need to be the full path to the
	* file on the client machine and we don't have a handle on that
	* information. The component's 'value' is instead set to the relevant
	* portion of the $_FILES array, allowing initial validation of uploaded
	* files w/ WACT.
	*/
	function getValue() {
		return;
	}
}

?>