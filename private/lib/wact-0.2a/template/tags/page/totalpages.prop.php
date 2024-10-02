<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: totalpages.prop.php,v 1.2 2004/11/18 04:22:50 jeffmoore Exp $
*/

PropertyDictionary::registerProperty(
    new PropertyInfo('TotalPages', 'page:navigator', 'TotalPagesProperty'), __FILE__);

class TotalPagesProperty extends CompilerProperty {

    var $context;
    
    function TotalPagesProperty(&$context) {
        $this->context =& $context;
    }

	/**
	* Generate the code to read the data value at run time
	* Must generate only a valid PHP Expression.
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateExpression(&$code) {
        $code->writePHP($this->context->getComponentRefCode());	    
        $code->writePHP('->getLastPageNumber()');
    }

}

?>