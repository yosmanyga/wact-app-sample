<?php
/**
 * @package WACT_UTIL
 * @version $Id: array_change_key_case.php,v 1.2 2004/03/17 12:11:10 harryf Exp $
 * Returns an array with all string keys lowercased or uppercased, the real
 * function only having been implemented with PHP 4.2.0+
 */
/**
 * Define case options
 */
define ('CASE_LOWER',1);
define ('CASE_UPPER',2);
/**
 * @see http://www.php.net/array_change_key_case
 * @see http://wact.sourceforge.net/index.php/PHPCompatibility
 * @param array
 * @param int constant CASE_LOWER (default) or CASE_UPPER
 * @return array
 * @access public
 */
function array_change_key_case($array, $changeCase = CASE_LOWER) {
	switch($changeCase) {
		case CASE_UPPER:
			$caseFunc = 'strtoupper';
		break;
		case CASE_LOWER:
		default:
			$caseFunc = 'strtolower';
		break;
	}
	$return = array();
	foreach($array as $key => $value) {
		$return[$caseFunc($key)] = $value;
	}
	return $return;
}
?>