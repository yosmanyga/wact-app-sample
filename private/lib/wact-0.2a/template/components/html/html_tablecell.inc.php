<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_COMPONENT
* @version $Id: html_tablecell.inc.php,v 1.3 2004/11/12 21:25:08 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* Load the parent block component
*/
require_once WACT_ROOT . 'template/components/html/html_base.inc.php';
/**
* @see http://wact.sourceforge.net/index.php/HtmlTableCellComponent
* @access public
* @package WACT_COMPONENT
*/
class HtmlTableCellComponent extends HtmlBaseComponent {

}
/**
* @see http://wact.sourceforge.net/index.php/HtmlTableHeaderComponent
* @access public
* @package WACT_COMPONENT
*/
class HtmlTableHeaderComponent extends HtmlTableCellComponent {

}
?>