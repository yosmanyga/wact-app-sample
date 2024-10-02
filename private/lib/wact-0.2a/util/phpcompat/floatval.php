<?php
/**
 * @package WACT_UTIL
 * @version $Id: floatval.php,v 1.1 2004/03/04 22:15:56 quipo Exp $
 * Provides a PHP implementations of floatval(),
 * only available since PHP 4.2.x, allowing older versions to use them.
 */
/**
 * @see http://www.php.net/floatval
 * @see http://wact.sourceforge.net/index.php/PHPCompatibility
 * @param mixed variable to extract float from
 * @return float float variable
 * @access public
 */
function floatval($var) {
	return (float)$var;
}
?>