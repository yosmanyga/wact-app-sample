<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_UTIL
* @version $Id: dataspacemapper.inc.php,v 1.5 2004/11/27 01:46:02 jeffmoore Exp $
*/

/**
* A DataSpace filter which remaps the variables in a dataspace to new names,
* according to an ini file containing {newname}={oldname} pairs.
* @see http://wact.sourceforge.net/index.php/DataSpaceMapper
* @access public
* @package WACT_UTIL
*/
class DataSpaceMapper {
	/**
	* The contents of the ini file
	* @var array associative array
	* @access private
	*/
	var $map=array();

	/**
	* DataSpaceMapper constructor
	* @param string name of ini file containing the map
	* @access public
	*/
	function DataSpaceMapper($map) {
		$this->map = $map;
	}

	/**
	* Filters a DataSpace, remapping variables. Called from a DataSpace
	* @param array reference to DataSpace variables
	* @access protect
	*/
	function doFilter(&$vars) {
		foreach ( $this->map as $ref => $name ) {
			if ( isset($vars[$name]) ) {
				if ( isset($vars[$ref]) ) {
					RaiseError('runtime','MAP_VAR_EXISTS',array(
						'mapfrom'=>$name,
						'mapto'=>$ref
						));
				} else {
					$vars[$ref] = $vars[$name];
					$vars[$name]=NULL;
				}
			}
		}
	}
}
?>