<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: varfilecompiler.inc.php,v 1.9 2004/02/16 08:24:03 jeffmoore Exp $
*/
/**
* Include files
*/
require_once TMPL_FILESCHEME_PATH . 'compilersupport.inc.php';

//--------------------------------------------------------------------------------
// use parse_ini_file for a faster/better implementation???
// Line breaks in the file must match the line breaks used by the host OS
// Now that this is done at compile time, many other options are available.
/**
* Parses a var file into a data structure. Used in conjunction with an
* ImportTag
* @see http://wact.sourceforge.net/index.php/parseVarFile
* @todo replace internals with parse_ini_file() ?
* @param string filename (with full or relative path)
* @return array datastructure built from var file
* @access protected
*/
function parseVarFile($filename) {

	$Result = array();

	$RawLines = preg_split("/\r?\n|\r/", readTemplateFile($filename));
	
	while (list(,$Line) = each($RawLines)) {
	    if (empty($Line)) {
	        continue;
	    }
		$EqualPos = strpos($Line, '=');
		if ($EqualPos === FALSE) {
			$Result[trim($Line)] = NULL;
		} else {
			$Key = trim(substr($Line, 0, $EqualPos));
			if (strlen($Key) > 0) {
				$Result[$Key] = trim(substr($Line, $EqualPos+1));
			}
		}
	}
	return $Result;
}

//--------------------------------------------------------------------------------
/**
* Compiles a var file and calls writeTemplateFile
* @see http://wact.sourceforge.net/index.php/compileVarFile
* @see writeTemplateFile
* @param string filename (with full or relative path)
* @return void
* @access protected
*/
function CompileVarFile($filename) {

	$destfile = ResolveTemplateCompiledFileName($filename, TMPL_IMPORT);
	$sourcefile = ResolveTemplateSourceFileName($filename, TMPL_IMPORT);
	if (empty($sourcefile)) {
        RaiseError('compiler', 'MISSINGFILE2', array(
            'srcfile' => $filename));
	}
	
	$text = serialize(parseVarFile($sourcefile));

	writeTemplateFile($destfile, $text);
}
?>