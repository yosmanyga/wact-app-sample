<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: input.tag.php,v 1.24 2004/11/18 04:22:48 jeffmoore Exp $
* @see http://www.w3.org/TR/html4/interact/forms.html
*/

/**
* Include control tag
*/
require_once WACT_ROOT . 'template/tags/form/control.inc.php';

/**
* Register the tag
*/
$taginfo =& new TagInfo('input', 'InputTag');
$taginfo->setDefaultLocation(LOCATION_CLIENT);
$taginfo->setEndTag(ENDTAG_FORBIDDEN);
$taginfo->setCompilerAttributes(array('errorclass', 'errorstyle', 'displayname'));
$taginfo->setKnownParent('FormTag');
TagDictionary::registerTag($taginfo, __FILE__);

/**
* Compile time component for building runtime InputComponents
* Creates all the components beginning with the name Input
* @see http://wact.sourceforge.net/index.php/InputTag
* @access protected
* @package WACT_TAG
*/
class InputTag extends ControlTag {
	/**
	* File to include at runtime
	* @var string path to runtime component relative to WACT_ROOT
	* @access private
	*/
	var $runtimeIncludeFile = '/template/components/form/form.inc.php';

	/**
	* Sets the runtimeComponentName property, depending on the type of
	* Input tag
	* @return void
	* @access protected
	*/
	function prepare() {
		$type = strtolower($this->getAttribute('type'));
		switch ($type) {
		case 'text':
			$this->runtimeComponentName = 'InputFormElement';
			break;
		case 'password':
			$this->runtimeComponentName = 'FormElement';
			break;
		case 'checkbox':
			$this->runtimeComponentName = 'CheckableFormElement';
			break;
		case 'submit':
			$this->runtimeComponentName = 'FormElement';
			break;
		case 'radio':
			$this->runtimeComponentName = 'CheckableFormElement';
			break;
		case 'reset':
			$this->runtimeComponentName = 'FormElement';
			break;
		case 'file':
			$this->runtimeComponentName = 'InputFileComponent';
            $this->runtimeIncludeFile = '/template/components/form/inputfile.inc.php';
			break;
		case 'hidden':
			$this->runtimeComponentName = 'InputFormElement';
			break;
		case 'image':
			$this->runtimeComponentName = 'InputFormElement';
			break;
		case 'button':
			$this->runtimeComponentName = 'InputFormElement';
			break;
		default:
            RaiseError('compiler', 'UNKNOWNINPUTYPE', array(
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
		}
		
		parent::prepare();
	}
}
?>
