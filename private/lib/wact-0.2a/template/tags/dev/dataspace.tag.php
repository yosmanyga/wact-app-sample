<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: dataspace.tag.php,v 1.4 2004/11/18 04:22:48 jeffmoore Exp $
*/

/**
* Register the tag
*/
$taginfo =& new TagInfo('dev:DATASPACE', 'DevDataSpaceTag');
$taginfo->setEndTag(ENDTAG_FORBIDDEN);
TagDictionary::registerTag($taginfo, __FILE__);

/**
* Dumps a runtime dataspace for display using print_r or var_dump
* @see http://wact.sourceforge.net/index.php/DevDataSpaceTag
* @access protected
* @package WACT_TAG
*/
class DevDataSpaceTag extends CompilerDirectiveTag {
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateContents(&$code) {
		parent::preGenerate($code);
		if ( NULL !== ( $context = $this->getAttribute('context') ) ) {
			$contexts = array('root','parent','current');
			if ( !in_array($context,$contexts) ) {
				$context = 'current';
			}
		} else {
			$context = 'current';
		}
		if ( NULL !== ( $output = $this->getAttribute('output') ) ) {
			$outputs = array('print_r','var_dump');
			if ( !in_array($output,$outputs) ) {
				$output = 'print_r';
			}
		} else {
			$output = 'print_r';
		}
		$code->writeHTML('<div aligh="left"><hr /><h3>Begin '.ucfirst($context).' DataSpace</h3><hr /></div>');
		switch ( $context ) {
			case 'root':
				$Context =& $this->getRootDataSource();
				break;
			case 'parent':
				$Context =& $this->getParentDataSource();
			break;
			default:
				$Context =& $this->getDataSource();
			break;
		}
		$code->writeHTML('<pre>');
		$code->writePHP('if ( is_object('.$Context->getDataSourceRefCode().
			') && method_exists ('.$Context->getDataSourceRefCode().',"export") ) {');
		$code->writePHP($output.'('.$Context->getDataSourceRefCode().'->export());');
		$code->writePHP('} else {');
		$code->writeHTML('Dataspace unavailable');
		$code->writePHP('}');
		$code->writeHTML('</pre>');
		$code->writeHTML('<div aligh="left"><hr /><h3>End '.ucfirst($context).' DataSpace</h3><hr /></div>');
	}
	
}
?>