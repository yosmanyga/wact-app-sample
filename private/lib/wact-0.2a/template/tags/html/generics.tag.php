<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: generics.tag.php,v 1.5 2004/11/18 05:05:25 jeffmoore Exp $
* Purpose of the classes here are to provide a dictionary of
* the "special cases" that exist in HTML only so that the template
* parser will know what to do with them, when used as a component
*/

require_once WACT_ROOT . 'template/compiler/generictag.inc.php';

/**
* Register the tags
*/
$taginfo = new TagInfo('br', 'GenericTag');
$taginfo->setEndTag(ENDTAG_FORBIDDEN);
$taginfo->setDefaultLocation(LOCATION_CLIENT);
TagDictionary::registerTag($taginfo, __FILE__);

$taginfo = new TagInfo('hr', 'GenericTag');
$taginfo->setEndTag(ENDTAG_FORBIDDEN);
$taginfo->setDefaultLocation(LOCATION_CLIENT);
TagDictionary::registerTag($taginfo, __FILE__);

$taginfo = new TagInfo('img', 'GenericTag');
$taginfo->setEndTag(ENDTAG_FORBIDDEN);
$taginfo->setDefaultLocation(LOCATION_CLIENT);
TagDictionary::registerTag($taginfo, __FILE__);

$taginfo = new TagInfo('link', 'GenericTag');
$taginfo->setEndTag(ENDTAG_FORBIDDEN);
$taginfo->setDefaultLocation(LOCATION_CLIENT);
TagDictionary::registerTag($taginfo, __FILE__);

$taginfo = new TagInfo('p', 'GenericContainerTag');
$taginfo->setDefaultLocation(LOCATION_CLIENT);
TagDictionary::registerTag($taginfo, __FILE__);

$taginfo = new TagInfo('param', 'GenericTag');
$taginfo->setEndTag(ENDTAG_FORBIDDEN);
$taginfo->setDefaultLocation(LOCATION_CLIENT);
TagDictionary::registerTag($taginfo, __FILE__);

?>