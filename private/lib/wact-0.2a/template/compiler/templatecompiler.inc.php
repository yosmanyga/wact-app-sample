<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: templatecompiler.inc.php,v 1.42 2004/11/29 21:56:03 jeffmoore Exp $
* This file is used to load the compile time component tags. Modify for new
* tag files, using the loadTags() function.
*/

/**
* Include all the compile time base components plus the compiler
*/
require_once WACT_ROOT . 'template/compiler/tagdictionary.inc.php';

require_once WACT_ROOT . 'template/compiler/property/dictionary.inc.php';
require_once WACT_ROOT . 'template/compiler/property/property.inc.php';
require_once WACT_ROOT . 'template/compiler/property/constant.inc.php';

require_once WACT_ROOT . 'template/compiler/filter/dictionary.inc.php';
require_once WACT_ROOT . 'template/compiler/filter/filter.inc.php';

require_once WACT_ROOT . 'template/compiler/compilercomponent.inc.php';
require_once WACT_ROOT . 'template/compiler/compilerdirective.inc.php';
require_once WACT_ROOT . 'template/compiler/silentcompilerdirective.inc.php';
require_once WACT_ROOT . 'template/compiler/servercomponent.inc.php';
require_once WACT_ROOT . 'template/compiler/servertagcomponent.inc.php';
require_once WACT_ROOT . 'template/compiler/serverdatacomponent.inc.php';
require_once WACT_ROOT . 'template/compiler/textnode.inc.php';
require_once WACT_ROOT . 'template/compiler/attributenode.inc.php';
require_once WACT_ROOT . 'template/compiler/componenttree.inc.php';

require_once WACT_ROOT . 'template/compiler/outputexpression.inc.php';

require_once WACT_ROOT . 'template/compiler/sourcefileparser.inc.php';
require_once WACT_ROOT . 'template/compiler/codewriter.inc.php';

require_once WACT_ROOT . 'template/compiler/databinding.inc.php';
require_once WACT_ROOT . 'template/compiler/expression.inc.php';

require_once TMPL_FILESCHEME_PATH . '/compilersupport.inc.php';

define('WACT_TAG_ROOT', WACT_ROOT . 'template/tags/');

/**
* Create the Global Dictionaries
*/
TagDictionary::getInstance();
PropertyDictionary::getInstance();
FilterDictionary::getInstance();

/**
* Creates a new ID for a server component, if one wasn't found. Called from
* CompilerComponent::getServerId()
* @see http://wact.sourceforge.net/index.php/getNewServerId
* @see CompilerComponent
* @return string id for server component e.g. id000(x)
* @access protected
*/
function getNewServerId() {
	static $ServerIdCounter = 1;
	return 'id00' . $ServerIdCounter++;
}

/**
* Compiles a template file. Uses the file scheme to location the source,
* instantiates the CodeWriter and ComponentTree (as the root) component then
* instantiates the SourceFileParser to parse the template.
* Creates the initialize and render functions in the compiled template.
* @see http://wact.sourceforge.net/CompileTemplateFile
* @see ComponentTree
* @see CodeWriter
* @see SourceFileParser
* @param string name of source template
* @return void
*/
function CompileTemplateFile($filename) {
	$destfile = ResolveTemplateCompiledFileName($filename, TMPL_INCLUDE);
	$sourcefile = ResolveTemplateSourceFileName($filename, TMPL_INCLUDE);
	if (empty($sourcefile)) {
        RaiseError('compiler', 'MISSINGFILE2', array(
            'srcfile' => $filename));
	}

	$code =& new CodeWriter();
	$code->setFunctionPrefix(md5($destfile));

	$Tree =& GetComponentTree(TRUE);
	$Tree->SourceFile = $sourcefile;

	$sfp =& new SourceFileParser($sourcefile);
	$sfp->parse($Tree);

	$Tree->prepare();

	$renderfunc = $code->beginFunction('(&$root)');
	$Tree->generate($code);
	$code->endFunction();

	$constructfunc = $code->beginFunction('(&$root)');
	$Tree->generateConstructor($code);
	$code->endFunction();

	$code->writePHP('$GLOBALS[\'TemplateRender\'][$this->codefile] = \'' . $renderfunc . '\';');
	$code->writePHP('$GLOBALS[\'TemplateConstruct\'][$this->codefile] = \'' . $constructfunc . '\';');

	writeTemplateFile($destfile, $code->getCode());

}

/**
* Used to maintain a single instance of ComponentTree for a single
* template file.
* Called from CompileTemplateFile and TreeBuilder::checkServerId()
* @param boolean (default = FALSE) whether to create a new instance
* @return ComponentTree
* @access public
*/
function & GetComponentTree($newInstance = FALSE) {
	static $Tree = NULL;
	if ( $newInstance || is_null($Tree) ) {
		$Tree = new ComponentTree();
	}
	return $Tree;
}

?>