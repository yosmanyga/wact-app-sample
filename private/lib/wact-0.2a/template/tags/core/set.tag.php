<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------

/**
* @package WACT_TAG
* @version $Id: set.tag.php,v 1.15 2004/11/18 04:22:47 jeffmoore Exp $
*/

require_once WACT_ROOT . 'template/compiler/property/attribute.inc.php';

/**
* Register the tag
*/
$taginfo =& new TagInfo('core:SET', 'CoreSetTag');
$taginfo->setEndTag(ENDTAG_FORBIDDEN);
TagDictionary::registerTag($taginfo, __FILE__);

/**
* Sets a property in the runtime DataSource, according the attributes of this
* tag at compile time.
* @see http://wact.sourceforge.net/index.php/CoreSetTag
* @access protected
* @package WACT_TAG
*/
class CoreSetTag extends SilentCompilerDirectiveTag {
	/**
	* @return void
	* @access protected
	*/
	function CheckNestingLevel() {
		if ($this->findParentByClass('CoreSetTag')) {
            RaiseError('compiler', 'BADSELFNESTING', array(
                'tag' => $this->tag,
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
		}
	}

	/**
	* @return int PARSER_FORBID_PARSING
	* @access protected
	*/
	function preParse() {
		$DataSource =& $this->getDataSource();

		foreach(array_keys($this->attributeNodes) as $key) {
		    $name = $this->attributeNodes[$key]->name;
		    $property =& new AttributeProperty($this->attributeNodes[$key]);
    		$DataSource->registerProperty($name, $property);
		}

		return PARSER_FORBID_PARSING;
	}
}
?>