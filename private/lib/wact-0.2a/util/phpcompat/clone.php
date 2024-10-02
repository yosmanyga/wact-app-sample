<?php
//--------------------------------------------------------------------------------
// Copyright 2004 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
 * @package WACT_UTIL
 * @version $Id: clone.php,v 1.6 2004/11/15 04:53:04 jeffmoore Exp $
 * implement a common mechanism to allow php4 and php5 to copy objects
 */
if (version_compare(phpversion(), '5', '>=')) {
	require WACT_ROOT . 'util/phpcompat/clone_php5.php';
} else {
	/**
	 * @see http://wact.sourceforge.net/index.php/PHPCompatibility
	 * @param object the object to be cloned
	 * @return object	a copy of the object
	 * @access public
	 */
	function clone_obj($object) {
		return $object;
	}
}
?>