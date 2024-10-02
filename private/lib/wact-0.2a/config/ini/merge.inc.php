<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package CONFIG
* @version $Id: merge.inc.php,v 1.1 2004/11/20 18:40:35 jeffmoore Exp $
*/

/**
* Define global configuration settings array
*/
$GLOBALS['ConfigurationSettings'] = array();

class ConfigManager {

function LoadConfigFile($file) {
    $configvalues = NULL;

    if (defined('WACT_CONFIG_DIRECTORY')) {
        $loadOrder = array(WACT_ROOT, WACT_CONFIG_DIRECTORY);
    } else {
        $loadOrder = array(WACT_ROOT, WACT_MAIN_DIR);
    }

    foreach ( $loadOrder as $baseDir ) {
    	$inifile = $baseDir . '/' . $file . '.ini';

    	if ( !file_exists($inifile) ) {
    		continue;
    	}

        $loopconfigvalues = parse_ini_file($inifile, TRUE);

        if (is_null($configvalues)) {
          	$configvalues = $loopconfigvalues;
        } else {
           	foreach ($loopconfigvalues as $section => $options ) {
           		if ( !array_key_exists($section, $configvalues) ) {
           			$configvalues[$section] = $options;
           		} else {
           			foreach ($options as $opt => $value ) {
           				$configvalues[$section][$opt] = $value;
           			}
           		}
           	}
        }
    }

    if (!is_array($configvalues)) {
        RaiseError('compiler', 'CONFIGFILENOTFOUND', array(
            'file' => $file));
    }
    $GLOBALS['ConfigurationSettings'][$file] = $configvalues;
}
    
    /**
    * Fetches a configuration option depending on supplied arguments.
    * @see http://www.php.net/parse_ini_file
    * @param string module of ini file
    * @param string section of ini
    * @param string name of element in section
    * @return string value of element
    * @access public
    */
    function getOption($module, $section, $key) {
        if (!isset($GLOBALS['ConfigurationSettings'][$module])) {
            ConfigManager::loadConfigFile($module);
        }
        if (isset($GLOBALS['ConfigurationSettings'][$module][$section][$key])) {
            return $GLOBALS['ConfigurationSettings'][$module][$section][$key];
        } else {
            return NULL;
        }
    }

    function getOptionAsPath($module, $section, $key) {
    
       $path = ConfigManager::getOption($module, $section, $key);
       if (!is_null($path)) {
           $constPos = strpos($path, '%');
           while (is_integer($constPos)) {
               $constant = substr($path, $constPos+1, strpos($path, '%', $constPos+1)-1);
               if (defined($constant)) {
                   $path = str_replace("%$constant%", constant($constant), $path);
               }
               $constPos = strpos($path, '%');
           }
       }
       return $path;
    }
    
    function getSection($module, $section) {
        if (!isset($GLOBALS['ConfigurationSettings'][$module])) {
            ConfigManager::loadConfigFile($module);
        }
        if (isset($GLOBALS['ConfigurationSettings'][$module][$section])) {
            return $GLOBALS['ConfigurationSettings'][$module][$section];
        } else {
            return NULL;
        }
    }
    
}


?>