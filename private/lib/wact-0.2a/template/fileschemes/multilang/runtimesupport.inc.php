<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: runtimesupport.inc.php,v 1.13 2004/11/20 18:09:48 jeffmoore Exp $
* Contains the multilang filescheme runtime side functions
* @see http://wact.sourceforge.net/index.php/FileScheme
*/

//--------------------------------------------------------------------------------
/**
* Determines the full path to a compiled template file.
* @see http://wact.sourceforge.net/index.php/ResolveTemplateCompiledFileName
* @param string template file name
* @param operation (TMPL_INCLUDE default) - deprecated?
* @return void
* @access protected
* @ignore
*/
function ResolveTemplateCompiledFileName($file, $operation = TMPL_INCLUDE) {

	if (gettype(strpos($file, "..")) == "integer" || gettype(strpos($file, "//")) == "integer") {
        RaiseError('compiler', 'INVALIDFILENAME', array(
            'file' => $file));
	}

	if (substr($file, 0, 1) == '/') {
	    $root = (ConfigManager::getOptionAsPath('config', 'filescheme', 'templateroot'));
		if (isset($root)) {
			$FileRoot = $root . '/compiled';
		} else {
			$FileRoot = WACT_MAIN_DIR . '/templates/compiled';
		}
		return $FileRoot . '/' . $GLOBALS['CurrentLanguage'] . $file;
	} else {
        RaiseError('compiler', 'RELATIVEPATH', array(
            'file' => $file));
	}
}

/**
* Returns the contents of a compiled template file
* @see http://wact.sourceforge.net/index.php/readTemplateFile
* @param string template file name
* @return string
* @access protected
* @ignore
*/
function readTemplateFile($file) {
	if (!function_exists('file_get_contents')) {
        require_once WACT_ROOT . 'util/phpcompat/file_get_contents.php';
    }
	return file_get_contents($file);
}
?>