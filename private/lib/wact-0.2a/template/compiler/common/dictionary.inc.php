<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: dictionary.inc.php,v 1.3 2004/11/20 18:09:47 jeffmoore Exp $
*/

// this is a dependency we should seek to remove
// It is here because of our dependence on a particular file scheme, and the
// TMPL_INCLUDE constant
require_once WACT_ROOT . 'template/template.inc.php';

/**
* @access protected
* @package WACT_TEMPLATE
*/
class CompilerArtifactDictionary {
	/**
	* A Path used to search for compiler artifacts
	* @var array
	* @access private
	*/
    var $SearchPath;
    
    function CompilerArtifactDictionary() {
        $this->SearchPath = ConfigManager::getOptionAsPath('compiler', 'tags', 'path');
    }    

    function scanDirectory($scandir, $extension) {
        $size = strlen($extension);
        if (is_dir($scandir)) {
            if ($dir = opendir($scandir)) {
                while (($file = readdir($dir)) !== FALSE) {
                    if (substr($file, -$size, $size) == $extension) {
                        require_once $scandir . '/' . $file;
                    }
                }
                closedir($dir);
            }
        }
    
    }

	function buildDictionary($extension) {
        foreach (explode(';', $this->SearchPath) as $tagpath) {
            $this->scanDirectory($tagpath, $extension);
        }
	}

	function &_getInstance($instance, $dictionaryClass, $type) {
	    if (!isset($GLOBALS[$instance])) {
    		$cachefile = ResolveTemplateCompiledFileName('/' . $type . '.cache', TMPL_INCLUDE);
    		
			if (!ConfigManager::getOption('compiler', 'dictionary', 'forcescan')) {
                if (file_exists($cachefile)) {
                    $GLOBALS[$instance] =& unserialize(readTemplateFile($cachefile));
                }
    		}
    		
    		if (!isset($GLOBALS[$instance]) || !is_object($GLOBALS[$instance])) {
                $GLOBALS[$instance] =& new $dictionaryClass();
                $GLOBALS[$instance]->buildDictionary('.' . $type . '.php');

                $destfile = ResolveTemplateCompiledFileName('/' . $type . '.cache', TMPL_INCLUDE);
                writeTemplateFile($destfile, serialize($GLOBALS[$instance]));
            }
        }
  		return $GLOBALS[$instance];
	}

}
?>