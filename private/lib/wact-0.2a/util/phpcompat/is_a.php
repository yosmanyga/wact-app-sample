<?php
/**
 * @package WACT_UTIL
 * @version $Id: is_a.php,v 1.1 2004/03/04 22:15:56 quipo Exp $
 * Provides a PHP implementations of is_a(),
 * only available since PHP 4.2.x, allowing older versions to use them.
 */
/**
 * @see http://www.php.net/is_a
 * @see http://wact.sourceforge.net/index.php/PHPCompatibility
 * @param object the object to be examined
 * @param string name of class to test for
 * @return boolean TRUE is object is of type of subclass of supplied class name
 * @access public
 */
function is_a($object, $classname) {
	return ((strtolower($classname) == get_class($object))
		or (is_subclass_of($object, $classname)));
}
?>