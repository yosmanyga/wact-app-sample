<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: compilersupport.inc.php,v 1.17 2004/11/20 18:09:48 jeffmoore Exp $
* Contains the multilang filescheme compiler side functions
* @see http://wact.sourceforge.net/index.php/FileScheme
*/

require_once WACT_ROOT . 'util/filewrite.inc.php';

//--------------------------------------------------------------------------------
/**
* Determines the full path to a source template file.
* @see http://wact.sourceforge.net/index.php/ResolveTemplateSourceFileName
* @param string template file name
* @param operation (TMPL_INCLUDE default) - deprecated?
* @param string absolute path to executing script.
* @return mixed either string full path to source file or NULL
* @access protected
* @ignore
*/
function ResolveTemplateSourceFileName($file, $operation = TMPL_INCLUDE, $context = NULL) {

    if (gettype(strpos($file, "..")) == "integer" || gettype(strpos($file, "//")) == "integer") {
        return NULL; // Invalid path
    }

    if (substr($file, 0, 1) == '/') {
        $root = (ConfigManager::getOptionAsPath('config', 'filescheme', 'templateroot'));
        if (isset($root)) {
            $fileroot = $root . '/source';
        } else {
            $fileroot = WACT_MAIN_DIR . '/templates/source';
        }
        
        switch ($operation) {
            case TMPL_INCLUDE:
                $filename = $fileroot . '/templates' . $file;
                break;
            case TMPL_IMPORT:
                $filename = $fileroot . '/lang/' . $GLOBALS['CurrentLanguage'] . $file;
                break;
            default:
                return NULL; // Invalid Operation
        }

        if (!file_exists($filename)) {
            $filename = WACT_ROOT . 'default' . $file;
        }

    } else {
        if (!is_null($context)) {
            $filename = dirname($context) . '/' . $file;
        } else {
            return NULL; // Absolute path required
        }
    }

    if (!file_exists($filename)) {
        return NULL; // Not found
    }
    
    return $filename;
}

/**
* Compiles all source templates below the source scheme directory
* including subdirectories
* @param string root directory name
* @param string path relative to root
* @return void
* @access protected
* @ignore
*/
function _RecursiveCompileAll($Root, $Path) {
    if ($dh = opendir($Root . $Path)) {
        while (($file = readdir($dh)) !== FALSE) {
            if (substr($file, 0, 1) == '.') {
                continue;
            }
            if (is_dir($Root . $Path . $file)) {
                _RecursiveCompileAll($Root, $Path . $file . '/');
                continue;
            }
            if (substr($file, -5, 5) == '.html') {
                CompileTemplateFile($Path . $file);
            } else if (substr($file, -5, 5) == '.vars') {
                CompileVarFile($Path . $file);
            }
        }
        closedir($dh);
    }
}

/**
* Writes a compiled template file
* @see http://wact.sourceforge.net/index.php/writeTemplateFile
* @param string filename
* @param string content to write to the file
* @return void
* @access protected
* @ignore
*/
function writeTemplateFile($file, $data) {
    WriteFile($file, $data);
}

/**
* Compiles all templates in the scheme
* @see http://wact.sourceforge.net/index.php/CompileEntireFileScheme
* @return void
* @access protected
* @todo add support for accumulating error messages.
* @ignore
*/
function CompileEntireFileScheme() {
    // Need to add support for accumulating error messages.

    $root = (ConfigManager::getOptionAsPath('config', 'filescheme', 'templateroot'));
    if (isset($root)) {
        $SourceRoot = $root . '/source';
    } else {
        $SourceRoot = WACT_MAIN_DIR . '/templates/source';
    }

    if ($dh = opendir($SourceRoot . '/lang')) {
        while (($file = readdir($dh)) !== FALSE) {
            if (substr($file, 0, 1) == '.') {
                continue;
            }
            if (is_dir($SourceRoot . '/lang/' . $file) && $file != 'CVS') {
                $GLOBALS['CurrentLanguage'] = $file;
    
                _RecursiveCompileAll($SourceRoot . '/templates', '/');
                _RecursiveCompileAll($SourceRoot . '/lang/' . $file, '/');
                _RecursiveCompileAll(WACT_ROOT . 'default', '/');
    
            }
        }           
        closedir($dh);
    }
    
}
?>