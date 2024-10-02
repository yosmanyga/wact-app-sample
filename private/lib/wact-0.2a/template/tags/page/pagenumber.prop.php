<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: pagenumber.prop.php,v 1.2 2004/11/18 04:22:50 jeffmoore Exp $
*/

PropertyDictionary::registerProperty(
    new PropertyInfo('PageNumber', 'page:navigator', 'PageNumberProperty'), __FILE__);

class PageNumberProperty extends CompilerProperty {

    var $context;
    
    function PageNumberProperty(&$context) {
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
        $code->writePHP('->getCurrentPageNumber()');
    }

}

?>