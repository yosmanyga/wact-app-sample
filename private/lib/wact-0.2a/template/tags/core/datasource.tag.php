<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: datasource.tag.php,v 1.4 2004/11/18 04:22:47 jeffmoore Exp $
*/

/**
* Register the tag
*/
$taginfo =& new TagInfo('core:DATASOURCE', 'CoreDataSourceTag');
$taginfo->setCompilerAttributes(array('from'));
TagDictionary::registerTag($taginfo, __FILE__);

/**
* Datasources act is "namespaces" for a template.
* @see http://wact.sourceforge.net/index.php/CoreDataSourceTag
* @access protected
* @package WACT_TAG
*/
class CoreDataSourceTag extends ServerDataComponentTag {
	/**
	* File to include at runtime
	* @var string path to runtime component relative to WACT_ROOT
	* @access private
	*/
	var $runtimeIncludeFile;
	/**
	* Name of runtime component class
	* @var string
	* @access private
	*/
	var $runtimeComponentName = 'DataSourceComponent';

}
?>