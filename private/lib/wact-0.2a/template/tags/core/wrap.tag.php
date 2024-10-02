<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: wrap.tag.php,v 1.18 2004/11/18 04:22:47 jeffmoore Exp $
*/

/**
* Register the tag
*/
$taginfo =& new TagInfo('core:WRAP', 'CoreWrapTag');
$taginfo->setEndTag(ENDTAG_FORBIDDEN);
$taginfo->setCompilerAttributes(array('file', 'placeholder'));
TagDictionary::registerTag($taginfo, __FILE__);

/**
* Merges the current template with a wrapper template, the current
* template being inserted into the wrapper at the point where the
* wrap tag exists.
* @see http://wact.sourceforge.net/index.php/CoreWrapTag
* @access protected
* @package WACT_TAG
*/
class CoreWrapTag extends CompilerDirectiveTag {
	/**
	* List of tag names of the children of the wrap tag
	* @var array
	* @access private
	*/
	var $keylist;

	/**
	* @return void
	* @access protected
	*/
	function CheckNestingLevel() {
		if ($this->findParentByClass('CoreWrapTag')) {
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
		$file = $this->getAttribute('file'); 
		if (empty($file)) {
            RaiseError('compiler', 'MISSINGREQUIREATTRIBUTE', array(
                'tag' => $this->tag,
                'attribute' => 'file', 
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
		}

		$sourcefile = ResolveTemplateSourceFileName($file, TMPL_INCLUDE, $this->SourceFile);
		if (empty($sourcefile)) {
			RaiseError('compiler', 'MISSINGFILE', array(
				'tag' => $this->tag,
				'srcfile' => $file,
				'file' => $this->SourceFile,
				'line' => $this->StartingLineNo));
		}
		$sfp =& new SourceFileParser($sourcefile);
		$sfp->parse($this);

		return PARSER_FORBID_PARSING;
	}

	/**
	* @return void
	* @access protected
	*/
	function prepare() {
		$this->parent->registerWrapper($this);
		parent::prepare();
	}

	/**
	* @return void
	* @access protected
	*/
	function generateWrapperPrefix(&$code) {
		$this->keylist = array_keys($this->children);
		$name = $this->getAttribute('placeholder');

        if (empty($name)) {
            RaiseError('compiler', 'MISSINGREQUIREATTRIBUTE', array(
                'tag' => $this->tag,
                'attribute' => 'placeholder',
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
        }
		
		reset($this->keylist);
		while (list(,$key) = each($this->keylist)) {
			$child =& $this->children[$key];
			if ($child->getServerId() == $name) {
				break;
			}
			$child->generate($code);
		}
	}

	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateWrapperPostfix(&$code) {
		while (list(,$key) = each($this->keylist)) {
			$this->children[$key]->generate($code);
		}
	}

	/**
	* By the time this is called we have already called generate
	* on all of our children, so does nothing
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generate(&$code) {
		// By the time this is called we have already called generate
		// on all of our children. 
	}
}
?>