<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_UTIL
* @version $Id: filewrite.inc.php,v 1.2 2004/11/25 11:17:54 ponticelli Exp $
*/
//--------------------------------------------------------------------------------

/**
* If a directory does not exist in the correct scheme location, create one
* @param string directory name
* @return void
* @access protected
*/
function EnsureDirectoryExists($dirname) {
	$open_basedir = ini_get('open_basedir');
	if($open_basedir && substr($dirname, 0, strlen($open_basedir)) == $open_basedir){
		$path = $open_basedir;
		$dirname = substr($dirname, strlen($open_basedir));
	} else {
		$path = '';
	}
	foreach (explode('/', $dirname) as $dir) {
		$path  .= $dir . '/';
		if (!file_exists($path)) {
			if (!@mkdir($path, 0777)) {
                RaiseError('compiler', 'CANNOTCREATEDIRECTORY', array(
                    'path' => $path));
			}
		}
	}
}

/**
* Writes a compiled template file
* @see http://wact.sourceforge.net/index.php/writeTemplateFile
* @param string filename
* @param string content to write to the file
* @return void
* @access protected
*/
function WriteFile($file, $data) {
	EnsureDirectoryExists(dirname($file));
	$fp=fopen($file, "wb");
	if (fwrite($fp, $data, strlen($data))){
		  fclose($fp);
	}
}

?>