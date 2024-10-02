<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: compileall.inc.php,v 1.9 2004/11/12 21:25:06 jeffmoore Exp $
* Procedural script which declares the CompileAll() function used to
* re-compile all templates in a directory
*/
/**
* Include files
*/
require_once WACT_ROOT . 'template/compiler/templatecompiler.inc.php';
require_once WACT_ROOT . 'template/compiler/varfilecompiler.inc.php';
require_once TMPL_FILESCHEME_PATH . 'compilersupport.inc.php';
/**
* Instruct error handlers not to die on error
*/
if ( !defined('WACT_ERROR_CONTINUE') ) {
	define ('WACT_ERROR_CONTINUE',1);
}
/**
* Invokes compiling of all templates below the directory where the function
* is called from. This simply calls the CompileEntireFileScheme function
* @see CompileEntireFileScheme
* @see http://wact.sourceforge.net/index.php/CompileAll
* @return void
* @access public
*/
function CompileAll() {
	CompileEntireFileScheme();
}
?>