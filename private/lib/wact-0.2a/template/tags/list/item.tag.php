<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: item.tag.php,v 1.12 2004/11/18 04:22:49 jeffmoore Exp $
*/

/**
* Register tag
*/
TagDictionary::registerTag(new TagInfo('list:ITEM', 'ListItemTag'), __FILE__);

/**
* Compile time component for items (rows) in the list
* @see http://wact.sourceforge.net/index.php/ListItemTag
* @access protected
* @package WACT_TAG
*/
class ListItemTag extends CompilerDirectiveTag {
	/**
	* @return void
	* @access protected
	*/
	function CheckNestingLevel() {
		if ( !is_a($this->parent, 'ListListTag') && !is_a($this->parent, 'ErrorSummaryTag')) {
            RaiseError('compiler', 'MISSINGENCLOSURE', array(
                'tag' => $this->tag,
                'EnclosingTag' => 'list:LIST or ERRORSUMMARY',
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
		}
	}

	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateContents(&$code) {
		$SepChild =& $this->findImmediateChildByClass('ListSeparatorTag');
        if ($SepChild) {
            $ShowSeparator = $code->getTempVariable();
			$code->writePHP('$' . $ShowSeparator . ' = FALSE;');
        }

		$code->writePHP('do { ');

		if ($SepChild) {
			$code->writePHP('if ($' . $ShowSeparator . ') {');
			$SepChild->generateNow($code);
			$code->writePHP('}');
			$code->writePHP('$' . $ShowSeparator . ' = TRUE;');
		}
		parent::generateContents($code);
		$code->writePHP('} while (' . $this->getDataSourceRefCode() . '->next());');
	}
}
?>