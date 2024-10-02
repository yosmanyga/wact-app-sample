<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_UTIL
* @version $Id: uri.inc.php,v 1.3 2004/07/17 21:45:53 harryf Exp $
*/
//--------------------------------------------------------------------------------
/**
* Adds a named value to a URI query string. If the value already
* exists it will be replaced.
* For example;
* <pre>
* $uri = 'index.php?action=edit';
* // Now $uri == 'index.php?action=edit&page=1'
* $uri = setUriParameter($uri, 'page', 1);
*
* // Now $uri == 'index.php?action=edit&page=2'
* $uri = setUriParameter($uri, 'page', 2);
* </pre>
* @param string URI
* @param string variable name
* @param string variable value
* @return string modified URI
* @todo Support for arrays in URI string?
* @package WACT_UTIL
*/
function setUriParameter($uri, $name, $value) {

	$BaseURI = $uri;
	$HasParams = FALSE;

	$querypos = strpos($uri, "?");
	if (is_integer($querypos)) {
		$HasParams = TRUE;

		$start = strpos($uri, $name . "=", $querypos);
		if (is_integer($start)) {
			$end = strpos($uri, "&", $start);
			if (is_integer($end)) {
				$BaseURI = substr($uri, 0, $start) . substr($uri, $end+1);
			} else {
				if ($start == $querypos+1) {
					$HasParams = FALSE;
				}
				$BaseURI = substr($uri, 0, $start-1);
			}
		}
	}
	if (is_null($value)) {
		return $BaseURI;
	} else {
		return $BaseURI . ( $HasParams ? "&" : "?") . $name . "=" . $value;
	}
}

?>