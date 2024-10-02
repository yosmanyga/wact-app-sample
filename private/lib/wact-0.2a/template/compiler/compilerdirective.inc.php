<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: compilerdirective.inc.php,v 1.4 2003/09/23 14:37:44 harryf Exp $
*/
//--------------------------------------------------------------------------------
/**
* Compiler directive tags do not have a corresponding runtime server Component,
* but they do render their contents into the compiled template.
* @see http://wact.sourceforge.net/index.php/CompilerDirectiveTag
* @access public
* @abstract
* @package WACT_TEMPLATE
*/
class CompilerDirectiveTag extends CompilerComponent {
}
?>