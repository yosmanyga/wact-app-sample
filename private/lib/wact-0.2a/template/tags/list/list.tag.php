<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: list.tag.php,v 1.23 2004/11/18 04:22:49 jeffmoore Exp $
*/

/**
* Register tag
*/
$taginfo =& new TagInfo('list:LIST', 'ListListTag');
$taginfo->setCompilerAttributes(array('from'));
TagDictionary::registerTag($taginfo, __FILE__);

/**
* The parent compile time component for lists
* @see http://wact.sourceforge.net/index.php/ListListTag
* @access protected
* @package WACT_TAG
*/
class ListListTag extends ServerDataComponentTag {
	/**
	* File to include at runtime
	* @var string path to runtime component relative to WACT_ROOT
	* @access private
	*/
	var $runtimeIncludeFile = '/template/components/list/list.inc.php';
	/**
	* Name of runtime component class
	* @var string
	* @access private
	*/
	var $runtimeComponentName = 'ListComponent';
	/**
	* Name of runtime DataSource property
	* @var string
	* @access private
	*/
	// var $DataSourceVar;

	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function preGenerate(&$code) {
		parent::preGenerate($code);
		
		$code->writePHP('if (' . $this->getDataSourceRefCode() . '->next()) {');
	}

	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function postGenerate(&$code) {
		$code->writePHP('}');
		
		$emptyChild =& $this->findImmediateChildByClass('ListDefaultTag');
		if ($emptyChild) {
			$code->writePHP(' else { ');
			$emptyChild->generateNow($code);
			$code->writePHP('}');
		}
		parent::postGenerate($code);
	}

}
?>
