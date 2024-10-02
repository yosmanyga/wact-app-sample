<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: output.tag.php,v 1.9 2004/11/18 04:22:47 jeffmoore Exp $
*/

/**
* Register the tag
*/
$taginfo =& new TagInfo('core:OUTPUT', 'CoreOutputTag');
$taginfo->setCompilerAttributes(array('value'));
$taginfo->setEndTag(ENDTAG_FORBIDDEN);
TagDictionary::registerTag($taginfo, __FILE__);

/**
* Defines an action take, should a DataSource property have been set at runtime.
* The opposite of the CoreDefaultTag
* @see CoreDefaultTag
* @see http://wact.sourceforge.net/index.php/CoreOutputTag
* @access protected
* @package WACT_TAG
*/
class CoreOutputTag extends CompilerDirectiveTag {
	/**
	* @var Expression
	* @access private
	*/
	var $expression;

	/**
	* @return int PARSER_REQUIRE_PARSING
	* @access protected
	*/
	function preParse() {
		$binding = $this->getAttribute('value'); 
		if (empty($binding)) {
            RaiseError('compiler', 'MISSINGREQUIREATTRIBUTE', array(
                'tag' => $this->tag,
                'attribute' => 'value', 
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
		}
		
		$this->expression =& new Expression($binding, $this, 'html');

		return PARSER_REQUIRE_PARSING;
	}

	function prepare() {
	    $this->expression->prepare();
		parent::prepare();
	}
	
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function preGenerate(&$code) {
		parent::preGenerate($code);

	    if ($this->expression->isConstant()) {
	        $code->writeHTML($this->expression->getValue());
	    } else {
            $this->expression->generatePreStatement($code);
            $code->writePHP('echo ');
            $this->expression->generateExpression($code);
            $code->writePHP(';');		
            $this->expression->generatePostStatement($code);
        }
	}

}
?>