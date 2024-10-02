<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: parserstate.inc.php,v 1.42 2004/12/02 03:27:26 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* Load array_change_key_case as needed
*/
if (!function_exists('array_change_key_case')) {
	require_once WACT_ROOT . 'util/phpcompat/array_change_key_case.php';
}

/**
* Base state handler for the SourceFileParser. Handles plain text
* @see http://wact.sourceforge.net/index.php/BaseParsingState
* @access public
* @package WACT_TEMPLATE
*/
class BaseParsingState {
	/**
	* Instance of SourceFileParser
	* @var SourceFileParser
	* @access private
	*/
	var $Parser;

	/**
	* Used to locate position within document
	* @access private
	*/
	var $Locator;

	/**
	* Instance of TreeBuilder
	* @var TreeBuilder
	* @access private
	*/
	var $TreeBuilder;

	/**
	* @param SourceFileParser
	* @access protected
	*/
	function BaseParsingState(&$Parser, &$TreeBuilder) {
		$this->Parser = & $Parser;
		$this->TreeBuilder = & $TreeBuilder;
		$this->Locator = NULL; // to suppress 4.1.2 warnings
	}

	/**
	* @param Locator
	* @access protected
	*/
	function setDocumentLocator(&$locator) {
	    $this->Locator =& $locator;
	}

	/**
	* Attribute syntax Error Handler
	* @param string tag content
	* @access public
	*/
	function invalidAttributeSyntax() {
        RaiseError('compiler', 'INVALID_ATTRIBUTE_SYNTAX', array(
            'file' => $this->Locator->getPublicId(), 
            'line' => $this->Locator->getLineNumber()));
	}

}

/**
* Handler for component parsing state
* @see http://wact.sourceforge.net/index.php/ComponentParsingState
* @access public
* @access WACT_TEMPLATE
*/
class ComponentParsingState extends BaseParsingState {
	/**
	* Instance of TagDictionary
	* @var TagDictionary
	* @access private
	*/
	var $TagDictionary;
	
	/**
	* @param SourceFileParser
	* @access public
	*/
	function ComponentParsingState(&$Parser, &$TreeBuilder, &$TagDictionary) {
		parent::BaseParsingState($Parser, $TreeBuilder);
		$this->TagDictionary =& $TagDictionary;
	}

    function getAttributeString($attrs) {
        $attrib_str = '';
		foreach ( $attrs as $key => $value ) {
			if (strcasecmp($key, PARSER_TRIGGER_ATTR_NAME) == 0 ) {
				continue;
			}

			$attrib_str .= ' ' . $key;
			if (!is_null($value)) {
			    if (strpos($value, '"') === FALSE) {
    				$attrib_str .= '="' . $value . '"';
    			} else {
    				$attrib_str .= '=\'' . $value . '\'';
    			}
			}
		}
		return $attrib_str;
    }

	/**
	* Handle opening tags
	* @param string tag name
	* @param array tag attributes
	* @access public
	*/
	function startElement($tag, $attrs) {

        $lower_attributes = array_change_key_case($attrs, CASE_LOWER);
        if ( isset($lower_attributes[PARSER_TRIGGER_ATTR_NAME]) && 
                strpos($lower_attributes[PARSER_TRIGGER_ATTR_NAME], '{$') !== FALSE) {
            RaiseError('compiler', 'ILLEGALVARREFINATTR', array(
                'tag' => $tag,
                'attribute' => $name, 	
                'file' => $this->Locator->getPublicId(), 
                'line' => $this->Locator->getLineNumber()));
        }

        $TagInfo =& $this->TagDictionary->findComponent($tag, $lower_attributes, FALSE, $this->TreeBuilder->Component);
		if (is_object($TagInfo)) {
		    $TagInfo->load();

			// Assign current component to parent reference
			$this->TreeBuilder->openBranch($TagInfo, $tag, $attrs, FALSE, $this->Locator);
			
			// Switch to literal state as required
			if ( $this->TreeBuilder->Component->preParse() == PARSER_FORBID_PARSING) {
				$this->Parser->changeToLiteralParsingState($tag);
			}

			// Cleanup for components that have no closing tag
			if ( $TagInfo->EndTag == ENDTAG_FORBIDDEN ) {
				$this->TreeBuilder->closeBranch(FALSE);
				$this->Parser->changeToComponentParsingState();
			}
		} else {
		    if (strcasecmp($this->TreeBuilder->Component->tag, $tag) == 0 ) {
				$this->TreeBuilder->Component->plainTagCount++;
			}
            $this->TreeBuilder->addContent($this->Locator, 
                '<' . $tag . $this->getAttributeString($attrs) . '>');
		}
    }

	/**
	* Handle closing tags
	* @param string tag name
	* @access public
	*/
	function endElement($tag) {
        $TagInfo =& $this->TagDictionary->getTagInfo($tag);
        if (is_object($TagInfo) && $TagInfo->EndTag == ENDTAG_FORBIDDEN) {
            RaiseError('compiler', 'UNEXPECTEDCLOSE2', array(
                'tag' => $TagInfo->Tag,
                'file' => $this->Locator->getPublicId(), 
                'line' => $this->Locator->getLineNumber()));
        }
        if ( strcasecmp($this->TreeBuilder->Component->tag, $tag) == 0 ) {
            // See bug 906138 - it's just a plain tag not the close of a ServerTagComponentTag
            if ($this->TreeBuilder->Component->plainTagCount != 0 ) {
                $this->TreeBuilder->Component->plainTagCount--;
           		$this->TreeBuilder->addTextNode('</' . $tag . '>');
            } else {
                $this->TreeBuilder->closeBranch(TRUE);
            }
        } else {
       		$this->TreeBuilder->addTextNode('</' . $tag . '>');
        }
    }

	/**
	* Handle empty tags
	* @param string tag name
	* @param array tag attributes
	* @access public
	*/
	function emptyElement($tag, $attrs) {
        $lower_attributes = array_change_key_case($attrs, CASE_LOWER);
        if ( isset($lower_attributes[PARSER_TRIGGER_ATTR_NAME]) && 
                strpos($lower_attributes[PARSER_TRIGGER_ATTR_NAME], '{$') !== FALSE) {
            RaiseError('compiler', 'ILLEGALVARREFINATTR', array(
                'tag' => $this->Component->tag,
                'attribute' => $name, 	
                'file' => $this->Locator->getPublicId(), 
                'line' => $this->Locator->getLineNumber()));
        }

        $TagInfo =& $this->TagDictionary->findComponent($tag, $lower_attributes, TRUE, $this->TreeBuilder->Component);
		if (is_object($TagInfo)) {
		    $TagInfo->load();

			// Assign current component to parent reference
			$this->TreeBuilder->openBranch($TagInfo, $tag, $attrs, TRUE, $this->Locator);
			
			// Switch to literal state as required
			if ( $this->TreeBuilder->Component->preParse() == PARSER_FORBID_PARSING) {
				$this->Parser->changeToLiteralParsingState($tag);
			}

			// Cleanup for components that have no closing tag
			if ( $TagInfo->EndTag == ENDTAG_FORBIDDEN ) {
				$this->Parser->changeToComponentParsingState();
				$this->TreeBuilder->closeBranch(FALSE);
			} else {
                $this->TreeBuilder->closeBranch(TRUE);
			}
		} else {
            $this->TreeBuilder->addContent($this->Locator, 
                '<' . $tag . $this->getAttributeString($attrs) . ' />');
		}
    }

	/**
	* Handle tag content
	* @param string tag content
	* @access public
	*/
	function characters($text) {
		$this->TreeBuilder->addContent($this->Locator, $text);
	}

	/**
	* Handle tag content
	* @param string tag content
	* @access public
	*/
	function cdata($text) {
		$this->TreeBuilder->addContent($this->Locator, '<![CDATA[' . $text . ']]>');
	}

	/**
	* Handle processing instructions
	* @param string target processor
	* @param string instruction
	* @access public
	*/
	function processingInstruction($target, $instruction) {
	    $this->TreeBuilder->addProcessingInstructionNode($target, $instruction);
	}

	/**
	* Handle JSP / ASP markup
	* @param string content
	* @access public
	*/
	function jasp($text) {
        // Name of method is not good in this case
        $this->TreeBuilder->addContent($this->Locator, '<%' . $text . '%>');
	}
	
	/**
	* Handle XML escape sequences
	* @param string content of escape
	* @access public
	*/
	function escape($text) {
    	$this->TreeBuilder->addContent($this->Locator, '<!' . $text . '>');
	}

	/**
	* Handle doctype
	* @param string content of doctype
	* @access public
	*/
	function doctype($text) {
    	$this->TreeBuilder->addContent($this->Locator, '<!' . $text . '>');
	}

	/**
	* Handle XML comments
	* @param string content of comment
	* @access public
	*/
	function comment($text) {
        // Name of method is not good in this case
    	$this->TreeBuilder->addContent($this->Locator, '<!--' . $text . '-->');
	}

	/**
	* Handle EOF Error
	* @param string tag content
	* @access public
	*/
	function unexpectedEOF($text) {
	    // Ignore the error and treat the rest of the file like data
		$this->TreeBuilder->addContent($this->Locator, $text);
	}

	/**
	* Entity syntax Error Handler
	* @param string tag content
	* @access public
	*/
	function invalidEntitySyntax($text) {
	    // Ignore the error and treat the rest of the file like data
		$this->TreeBuilder->addContent($this->Locator, $text);
	}

}

/**
* Handler for the literal parsing state
* @see http://wact.sourceforge.net/index.php/LiteralParsingState
* @access public
* @access WACT_TEMPLATE
*/
class LiteralParsingState extends BaseParsingState {
	/**
	* Name of the literal tag
	* @var string
	* @access public
	*/
	var $literalTag;
	
	/**
	* @param SourceFileParser
	* @access public
	*/
	function LiteralParsingState(&$Parser, &$TreeBuilder) {
		parent::BaseParsingState($Parser, $TreeBuilder);
	}

    function getAttributeString($attrs) {
        $attrib_str = '';
		foreach ( $attrs as $key => $value ) {
			$attrib_str .= ' ' . $key;
			if (!is_null($value)) {
			    if (strpos($value, '"') === FALSE) {
    				$attrib_str .= '="' . $value . '"';
    			} else {
    				$attrib_str .= '=\'' . $value . '\'';
    			}
			}
		}
		return $attrib_str;
    }

	/**
	* Handle opening tags
	* @param string tag name
	* @param array tag attributes
	* @access public
	*/
	function startElement($tag, $attrs) {
        $this->TreeBuilder->addTextNode('<' . $tag . $this->getAttributeString($attrs) . '>');
	}

	/**
	* Handle closing tags
	* @param string tag name
	* @param boolean empty tag or not
	* @access public
	*/
	function endElement($tag) {
		if ( $this->literalTag == $tag ) {
			$this->TreeBuilder->closeBranch(TRUE);
			$this->Parser->changeToComponentParsingState();
		} else {
    		$this->TreeBuilder->addTextNode('</' . $tag . '>');
		}
	}

	/**
	* Handle empty tags
	* @param string tag name
	* @param array tag attributes
	* @access public
	*/
    function emptyElement($tag, $attrs) { 
        $this->TreeBuilder->addTextNode('<' . $tag . $this->getAttributeString($attrs) . ' />');
    }

	/**
	* Handle tag content
	* @param string tag content
	* @access public
	* @abstract
	*/
	function characters($text) {
		$this->TreeBuilder->addTextNode($text);
	}

	/**
	* Handle tag content
	* @param string tag content
	* @access public
	*/
	function cdata($text) {
		$this->TreeBuilder->addTextNode('<![CDATA[' . $text . ']]>');
	}

	/**
	* Handle processing instructions
	* @param string target processor
	* @param string instruction
	* @access public
	*/
	function processingInstruction($target, $instruction) {
		$this->TreeBuilder->addTextNode('<?' . $target . ' ' . $instruction . '?>');
	}

	/**
	* Handle JSP / ASP markup
	* @param string content
	* @access public
	*/
	function jasp($text) {
		$this->TreeBuilder->addTextNode('<%' .  $text . '%>');
	}	
	
	/**
	* Handle XML escape sequences
	* @param string content of escape
	* @access public
	*/
	function escape($text) {
		$this->TreeBuilder->addTextNode('<!' . $text . '>');
	}

	/**
	* Handle XML comments
	* @param string content of comment
	* @access public
	*/
	function comment($text) {
		$this->TreeBuilder->addTextNode('<!--' . $text . '-->');
	}

	/**
	* Handle doctype
	* @param string content of escape
	* @access public
	*/
	function doctype($text) {
		$this->TreeBuilder->addTextNode('<!' . $text . '>');
	}
	
	/**
	* Handle EOF error
	* @param string tag content
	* @access public
	* @abstract
	*/
	function unexpectedEOF($text) {
	    // Ignore the error and treat the rest of the file like data
		$this->TreeBuilder->addTextNode($text);
	}

	/**
	* Entity syntax Error Handler
	* @param string tag content
	* @access public
	* @abstract
	*/
	function invalidEntitySyntax($text) {
	    // Ignore the error and treat the rest of the file like data
		$this->TreeBuilder->addTextNode($text);
	}

}
?>