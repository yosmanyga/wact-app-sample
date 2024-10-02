<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: import.tag.php,v 1.23 2004/11/18 04:22:47 jeffmoore Exp $
*/

/**
* Include the VarFileCompiler
*/
require_once WACT_ROOT . 'template/compiler/varfilecompiler.inc.php';
require_once WACT_ROOT . 'template/compiler/property/constant.inc.php';

/**
* Register tag
*/
$taginfo =& new TagInfo('core:IMPORT', 'CoreImportTag');
$taginfo->setCompilerAttributes(array('file'));
$taginfo->setEndTag(ENDTAG_FORBIDDEN);
TagDictionary::registerTag($taginfo, __FILE__);

/**
* Imports a property definition file into the DataSource (e.g. a configuration file)
* @see http://wact.sourceforge.net/index.php/CoreImportTag
* @access protected
* @package WACT_TAG
*/
class CoreImportTag extends SilentCompilerDirectiveTag {
    /**
    * @return void
    * @access protected
    */
    function CheckNestingLevel() {
        if ($this->findParentByClass('CoreImportTag')) {
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

        $sourcefile = ResolveTemplateSourceFileName($file, TMPL_IMPORT, $this->SourceFile);
        if (empty($sourcefile)) {
            RaiseError('compiler', 'MISSINGFILE', array(
                'tag' => $this->tag,
                'srcfile' => $file, 
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
        }
        
        $DataSource =& $this->getDataSource();
		foreach(parseVarFile($sourcefile) as $name => $value) {
		    $property =& new ConstantProperty($value);
    		$DataSource->registerProperty($name, $property);
		}

        return PARSER_FORBID_PARSING;
    }
}
?>