<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: default.tag.php,v 1.16 2004/11/18 04:22:47 jeffmoore Exp $
*/

/**
* Register the tag
*/
$taginfo =& new TagInfo('core:DEFAULT', 'CoreDefaultTag');
$taginfo->setCompilerAttributes(array('for'));
TagDictionary::registerTag($taginfo, __FILE__);

/**
* Allows a default action to take place at runtime, should a
* DataSource property have failed to be populated
* @see http://wact.sourceforge.net/index.php/CoreDefaultTag
* @access protected
* @package WACT_TAG
*/
class CoreDefaultTag extends CompilerDirectiveTag {
	/**
	* @var DataBindingExpression
	* @access private
	*/
    var $DBE;

	/**
	* @return int PARSER_REQUIRE_PARSING
	* @access protected
	*/
	function preParse() {
		$binding = $this->getAttribute('for'); 
		if (empty($binding)) {
            RaiseError('compiler', 'MISSINGREQUIREATTRIBUTE', array(
                'tag' => $this->tag,
                'attribute' => 'for',
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
		}
		
        $this->DBE =& new DataBindingExpression($binding, $this);
		return PARSER_REQUIRE_PARSING;
	}
	
	function prepare() {
	    $this->DBE->prepare();
		parent::prepare();
	}

	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function preGenerate(&$code) {
		parent::preGenerate($code);

		$tempvar = $code->getTempVariable();
		$this->DBE->generatePreStatement($code);
		$code->writePHP('$' . $tempvar . ' = ');
		$this->DBE->generateExpression($code);
        $code->writePHP(';');		
		$this->DBE->generatePostStatement($code);
		
		$code->writePHP('if (empty($' . $tempvar . ')  && $' . $tempvar . ' !== "0" && $' . $tempvar . ' !== 0) {');
	}

	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function postGenerate(&$code) {
		$code->writePHP('}');
		parent::postGenerate($code);
	}
}
?>