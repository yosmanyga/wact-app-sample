<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: include.tag.php,v 1.18 2004/11/18 04:22:47 jeffmoore Exp $
*/

/**
* Register the tag
*/
$taginfo =& new TagInfo('core:INCLUDE', 'CoreIncludeTag');
$taginfo->setEndTag(ENDTAG_FORBIDDEN);
$taginfo->setCompilerAttributes(array('file', 'literal'));
TagDictionary::registerTag($taginfo, __FILE__);

/**
* Include another template into the current template
* @see http://wact.sourceforge.net/index.php/CoreIncludeTag
* @access protected
* @package WACT_TAG
*/
class CoreIncludeTag extends CompilerDirectiveTag {

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

        if ($this->getBoolAttribute('literal')) {
            $LiteralComponent =& new TextNode(readTemplateFile($sourcefile));
            $this->addChild($LiteralComponent);
        } else {
            $sfp =& new SourceFileParser($sourcefile);
            $sfp->parse($this);
        }
        return PARSER_FORBID_PARSING;
    }
}
?>