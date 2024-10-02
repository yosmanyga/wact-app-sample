<?php
/**
 * @package WACT_UTIL
 * @version $Id: str_split.php,v 1.1 2004/03/04 22:15:56 quipo Exp $
 * Provides a PHP implementations of str_split(),
 * only available since PHP 5.0.x, allowing older versions to use them.
 */
/**
 * @see http://www.php.net/str_split
 * @see http://wact.sourceforge.net/index.php/PHPCompatibility
 * @param string
 * @param int chunk length
 * @return array (or false if chunk length is < 1)
 * @access public
 * @author http://www.pgregg.com/projects/php/code/str_split.phps
 */
function str_split($string, $length=1) {
	if ($length < 1) {
	    return false;
	}
	preg_match_all('/('.str_repeat('.', $length).')/Uims', $string, $matches);
    return $matches[1];
}
?>