<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: select.tag.php,v 1.26 2004/11/18 04:22:48 jeffmoore Exp $
* @see http://www.w3.org/TR/html4/interact/forms.html
*/

/**
* Include control tag
*/
require_once WACT_ROOT . 'template/tags/form/control.inc.php';

/**
* Register the tag
*/
$taginfo =& new TagInfo('select', 'SelectTag');
$taginfo->setDefaultLocation(LOCATION_CLIENT);
$taginfo->setCompilerAttributes(array('errorclass', 'errorstyle', 'displayname'));
$taginfo->setKnownParent('FormTag');
TagDictionary::registerTag($taginfo, __FILE__);

/**
* Compile time component for building runtime select components
* @see http://wact.sourceforge.net/index.php/SelectTag
* @access protected
* @package WACT_TAG
*/
class SelectTag extends ControlTag {
	/**
	* File to include at runtime
	* @var string path to runtime component relative to WACT_ROOT
	* @access private
	*/
	var $runtimeIncludeFile;
	/**
	* Name of runtime component class
	* @var string
	* @access private
	*/
	var $runtimeComponentName;

	/**
	* @return void
	* @access protected
	*/
	function prepare() {

		if ($this->getBoolAttribute('multiple')) {
			$this->runtimeIncludeFile = 'template/components/form/select.inc.php';
			$this->runtimeComponentName = 'SelectMultipleComponent';
			
			// Repetition of ControlTag::prepare but required for special case
			// of SelectMultiple to provide meaningful error messages
			if (!$this->getBoolAttribute('name')) {
				if ( $this->getBoolAttribute('id') ) {
					// Note - appends [] to id value
					$this->setAttribute('name',$this->getAttribute('id').'[]');
				} else {
					RaiseError('compiler', 'NAMEREQUIRED', array(
						'tag' => $this->tag,
						'file' => $this->SourceFile,
						'line' => $this->StartingLineNo));
				}
			}

			if (!is_integer(strpos($this->getAttribute('name'), '[]'))) {
				RaiseError('compiler', 'CONTROLARRAYREQUIRED', array(
					'name' => $this->getAttribute('name'),
					'file' => $this->SourceFile,
					'line' => $this->StartingLineNo));
			}
		} else {
			$this->runtimeIncludeFile = 'template/components/form/select.inc.php';
			$this->runtimeComponentName = 'SelectSingleComponent';
		}

		parent::prepare();
	}

	/**
	* Ignore the compiler time contents and generate the contents at run time.
	* @return void
	* @access protected
	*/
	// Ignore the compiler time contents and generate the contents at run time.
	function generateContents(&$code) {
		$code->writePHP($this->getComponentRefCode() . '->renderContents();');
	}
}
?>
