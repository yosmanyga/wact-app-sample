<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: sourcefileparser.inc.php,v 1.103 2004/11/20 17:23:17 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* Define compile component states which determine parse behaviour
*/
define('PARSER_REQUIRE_PARSING', TRUE);
define('PARSER_FORBID_PARSING', FALSE);
define('PARSER_ALLOW_PARSING', NULL);

//--------------------------------------------------------------------------------
/**
* Includes
*/
require_once WACT_ROOT . 'template/compiler/treebuilder.inc.php';
require_once WACT_ROOT . 'template/compiler/parserstate.inc.php';
require_once WACT_ROOT . 'template/compiler/htmlparser.inc.php';

//--------------------------------------------------------------------------------
/**
* The source template parser is the core of the WACT template engine
* @see http://wact.sourceforge.net/index.php/SourceFileParser
* @access public
* @package WACT_TEMPLATE
*/
class SourceFileParser {
	/**
	* Path and filename of source template
	* @var string
	* @access private
	*/
	var $SourceFile;
	/**
	* Initial instance of ComponentParsingState
	* @var ComponentParsingState
	* @access private
	*/
	var $ComponentParsingState;
	/**
	* Initial instance of LiteralParsingState
	* @var LiteralParsingState
	* @access private
	*/
	var $LiteralParsingState;
	/**
	* The current active state
	* @var BaseParsingState subclass
	* @access public
	*/
	var $State;
	
	//----------------------------------------------------------------------------
	/**
	* Constructs SourecFileParser. Uses readTemplateFile() to get the contents
	* of the template.
	* @see readTemplateFile
	* @param string path and filename of source template
	* @access protected
	*/
	function SourceFileParser($sourcefile) {
		$this->SourceFile = $sourcefile;
	}

	/**
	* Creates Build a filter chain
	* @return HTMLParser
	* @access protected
	*/
	function &buildFilterChain($saxfilters) {
		$Chain =& $this;

        // Build a filter chain
        if ( !empty($saxfilters) ) {
            foreach (explode(':', $saxfilters) as $saxfilter) {
                $saxfilterfile = strtolower($saxfilter).'saxfilter.inc.php';
                $saxfilterclass = $saxfilter . 'SaxFilter';
                if ( !include_once WACT_ROOT . 'template/compiler/saxfilters/'.$saxfilterfile ) {
                    RaiseError('compiler', 'SAXFILTER_NOTFOUND', array(
                        'path' => WACT_ROOT . 'template/compiler/saxfilters/'.$saxfilterfile,
                        'name' => ucfirst($saxfilter)));
                } else {
                    $NewFilter =& new $saxfilterclass();
                    $NewFilter->setChildSaxFilter($Chain);
                    $Chain =& $NewFilter;
                }
            }
        }

		return $Chain;
	}

	/**
	* Used to parse the source template.
	* Initially invoked by the CompileTemplate function,
	* the first component argument being a ComponentTree.
	* Uses the TagDictionary to spot compiler components
	* @see CompileTemplate
	* @see ComponentTree
	* @param object compile time component
	* @return void
	* @access protected
	*/
	function parse(&$ComponentRoot) {
		global $TagDictionary;

		$TreeBuilder =& new TreeBuilder($ComponentRoot);

		$this->ComponentParsingState =& new ComponentParsingState($this, $TreeBuilder, $TagDictionary);
		$this->LiteralParsingState =& new LiteralParsingState($this, $TreeBuilder);
		$this->changeToComponentParsingState();
		
		$Chain =& $this->buildFilterChain(ConfigManager::getOption('compiler', 'parser', 'saxfilters'));
		$parser =& new HTMLParser($Chain);
		$parser->parse(readTemplateFile($this->SourceFile), $this->SourceFile);

		if ( $ComponentRoot->getServerId() != $TreeBuilder->Component->getServerId() ) {
			RaiseError('compiler', 'MISSINGCLOSE', array(
				'tag' => $TreeBuilder->Component->tag,
				'file' => $TreeBuilder->Component->SourceFile, 
				'line' => $TreeBuilder->Component->StartingLineNo));
		}
	}
	
	/**
	* Switch to component parsing state
	* @return void
	* @access public
	*/
	function changeToComponentParsingState() {
		$this->State =& $this->ComponentParsingState;
	}

	/**
	* Switch to literal parsing state
	* @param string tag name marking the literal component
	* @return void
	* @access public
	*/
	function changeToLiteralParsingState($tag) {
		$this->State = & $this->LiteralParsingState;
		$this->State->literalTag = $tag;
	}	

	/**
	* Document Locator Handler
	* @param Locator
	* @return void
	* @access private
	*/
	function setDocumentLocator(&$locator) {
		$this->LiteralParsingState->setDocumentLocator($locator);
		$this->ComponentParsingState->setDocumentLocator($locator);
	}

	/**
	* Sax Open Handler
	* @param string tag name
	* @param array attributes
	* @return void
	* @access private
	*/
	function startElement($tag, $attrs) {
		$this->State->startElement($tag, $attrs);
	}

	/**
	* Sax Close Handler
	* @param string tag name
	* @return void
	* @access private
	*/
	function endElement($tag) {
		$this->State->endElement($tag);
	}

	/**
	* Sax Open Handler
	* @param string tag name
	* @param array attributes
	* @return void
	* @access private
	*/
	function emptyElement($tag, $attrs) {
		$this->State->emptyElement($tag, $attrs);
	}

	/**
	* Sax Data Handler
	* @param string text content in tag
	* @return void
	* @access private
	*/
	function characters($text) {
        $this->State->characters($text);
	}

	/**
	* Sax CDATA Handler
	* @param string text content in tag
	* @return void
	* @access private
	*/
	function cdata($text) {
        $this->State->cdata($text);
	}

	/**
	* Sax Processing Instruction Handler
	* @param string target processor (e.g. php)
	* @param string text content in PI
	* @return void
	* @access private
	*/
	function processingInstruction($target, $instruction) {
		$this->State->processingInstruction($target, $instruction);
	}

	/**
	* Sax XML Escape Handler
	* @param string text content in escape
	* @return void
	* @access private
	*/
	function escape($text) {
		$this->State->escape($text);
	}

	/**
	* Sax XML Comment Handler
	* @param string text content in comment
	* @return void
	* @access private
	*/
	function comment($text) {
		$this->State->comment($text);
	}

	/**
	* Sax XML doctype Handler
	* @param string text content in doctype
	* @return void
	* @access private
	*/
	function doctype($text) {
		$this->State->doctype($text);
	}
	
	/**
	* Sax JSP / ASP markup Handler
	* @param string text content in JASP tags
	* @return void
	* @access private
	*/
	function jasp($text) {
		$this->State->jasp($text);
	}	

	/**
	* Sax EOF Handler
	* @param string text content in tag
	* @return void
	* @access private
	*/
	function unexpectedEOF($text) {
        $this->State->unexpectedEOF($text);
	}

	/**
	* Sax Entity syntax Error Handler
	* @param string text content in tag
	* @return void
	* @access private
	*/
	function invalidEntitySyntax($text) {
        $this->State->invalidEntitySyntax($text);
	}

	/**
	* Sax Attribute syntax Error Handler
	* @param string text content in tag
	* @return void
	* @access private
	*/
	function invalidAttributeSyntax() {
        $this->State->invalidAttributeSyntax();
	}

}

?>
