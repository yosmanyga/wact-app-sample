<?php
//--------------------------------------------------------------------------------
// Copyright 2004 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
 * @package WACT_UTIL
 * @version $Id: clone_php5.php,v 1.4 2004/07/24 04:49:57 jsweat Exp $
 * implement a common mechanism to allow php4 and php5 to copy objects
 */
/**
 * @see http://wact.sourceforge.net/index.php/PHPCompatibility
 * @param object the object to be cloned
 * @return object	a copy of the object
 * @access public
 */
function clone_obj($object) {
	return clone $object;
}
?>