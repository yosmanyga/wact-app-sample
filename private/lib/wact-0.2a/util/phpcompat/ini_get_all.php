<?php
/**
 * @package WACT_UTIL
 * @version $Id: ini_get_all.php,v 1.5 2004/11/20 18:09:49 jeffmoore Exp $
 * Provides a PHP implementations of floatval(),
 * only available since PHP 4.2.x, allowing older versions to use them.
 */
/**
* Include PEAR::XML_HTMLSax
*/
if ( !defined('PEAR_LIBRARY_PATH') ) {
    define('PEAR_LIBRARY_PATH', ConfigManager::getOptionAsPath('config', 'pear', 'library_path'));
}
if (!@include_once PEAR_LIBRARY_PATH . 'XML/HTMLSax3.php') {
    if ( !defined('XML_HTMLSAX3') ) {
        define('XML_HTMLSAX3', WACT_ROOT . ConfigManager::getOptionAsPath('compiler', 'XML_HTMLSAX', 'library_path'));
    }
    if (!@include_once XML_HTMLSAX3 . 'HTMLSax3.php') {
        RaiseError('runtime', 'LIBRARY_REQUIRED', array(
            'library' => 'PEAR::XML_HTMLSax3',
            'path' => XML_HTMLSAX3));
    }          
}

/**
* Sax handler for ini_get_all
* @package WACT_UTIL
*/
class ini_get_all_handler {

	var $inDirectives = FALSE;
	var $inTh = FALSE;
	var $inDirName = FALSE;
	var $inValue = FALSE;
	var $inDirectiveName = FALSE;
	var $directiveName = '';
	var $directive = array();
	var $directives = array();

	function open($parser, $tag, $attrs) {
		$tag = strtolower($tag);
		if ( $tag == 'th' ) {
			$this->inTh = TRUE;
			return;
		}
		if ( $this->inDirectives ) {
			if ( $tag == 'td' ) {
				if ( isset($attrs['bgcolor']) && $attrs['bgcolor'] == '#ccccff' ) {
					$this->inDirName = TRUE;
				} else if ( isset($attrs['align']) && $attrs['align'] == 'center' ) {
					$this->inValue = TRUE;
				}
			}
		}
	}

	function close($parser, $tag) {
		$tag = strtolower($tag);
		if ( $tag == 'th' ) {
			$this->inTh = FALSE;
			return;
		}
		if ( $tag == 'td' ) {
			if ( $this->inDirName ) {
				$this->inDirName = FALSE;
			} else if ($this->inValue) {
				$this->inValue = FALSE;
			}
			return;
		}
		if ( $this->inDirectives && $tag == 'table' ) {
			$this->inDirectives = FALSE;
			return;
		}
	}

	function data($parser, $data) {
		if ( $this->inTh && ($data == 'Directive') ) {
			$this->inDirectives = TRUE;;
			return;
		}
		if ( $this->inDirectives ) {
			$data = trim($data);
			if ( $this->inDirName && $data != '' ) {
				$this->directiveName = $data;
			} else if ( $this->inValue ) {
				if ( !isset($this->directive['local_value']) ) {
					$this->directive['local_value'] = $data;
				} else {
					$this->directive['global_value'] = $data;
					$this->directive['access'] = NULL;
					$this->directives[$this->directiveName] = $this->directive;
					$this->directiveName = '';
					$this->directive = array();
				}
			}
		}
	}

	function getDirectives() {
		return $this->directives;
	}
}
/**
 * Note this version does not return values for access level and the values
 * for directives are human readable (e.g. 'Off' vs NULL from ini_get_all)
 * It is only recommended for non - critical use (display to humans) -
 * DONT RELY ON IT!!!
 * Note also that it's geared for the HTML output from phpinfo found in PHP
 * 4.1.2 (not sure about compatibility with other versions and it's definately
 * changed by 4.3.x)
 * @see http://www.php.net/ini_get_all
 * @see http://wact.sourceforge.net/index.php/PHPCompatibility
 * @return array PHP ini settings
 * @access public
 */
function ini_get_all() {
	$Handler=& new ini_get_all_handler();
	$Parser=& new XML_HTMLSax3();
	$Parser->set_object($Handler);
	$Parser->set_element_handler('open','close');
	$Parser->set_data_handler('data');
	ob_start();
	phpinfo();
	$info = ob_get_contents();
	ob_end_clean();
	$Parser->parse($info);
	return $Handler->getDirectives();
}
?>