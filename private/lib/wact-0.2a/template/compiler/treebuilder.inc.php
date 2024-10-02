<?php
//------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: treebuilder.inc.php,v 1.51 2004/11/18 05:05:25 jeffmoore Exp $
*/
//------------------------------------------------------------------------------

/**#@+
 *      ExpresionParser capture constants
 */
define('WACT_EXPPARSER_BEFORE_CONTENT', 1);
define('WACT_EXPPARSER_EXPRESSION', 2);
define('WACT_EXPPARSER_AFTER_CONTENT',  5);
/**#@-*/

//------------------------------------------------------------------------------
/**
* Acts on the ComponentTree in response to events within
* the SourceFileParser
* @todo get rid of reference to SourceFileParser
* @see http://wact.sourceforge.net/index.php/TreeBuilder
* @access public
* @package WACT_TEMPLATE
*/
class TreeBuilder {
	/**
	* Parent Component
	* @var CompilerComponent subclass of
	* @access public
	*/
	var $ParentComponent;
	/**
	* Current Component
	* @var CompilerComponent subclass of
	* @access public
	*/
	var $Component;

    var $variableReferencePattern;
    
	/**
	* Constructs TreeBuilder, setting up expression parsers
	* @access public
	*/
	function TreeBuilder(&$ComponentRoot) {
		$this->Component =& $ComponentRoot;
		$this->ParentComponent = NULL;

        $this->variableReferencePattern =
            // start at the beginning
            '/^' . 
            // Pick up the portion of the string before the variable reference
            '((?s).*?)' .
            // Beginning of a variable reference
            preg_quote('{$', '/') .
            // Collect the entire variable reference into one subexpression
            '(' . 
                // capture the contents of one or more fragments.
                '(' . 
                    // Anything thats not a quote or the end of the variable
                    // reference can be in a fragment
                    '[^"\'}]+' .
                    // OR
                    '|' .
                    // A string inside quotes is also a fragment
                    '(\'|").*?\4' .
                ')+' . 
            ')' .
            // end of a variable reference
            preg_quote('}', '/') .
            // Pick up the portion of the string after the variable reference
            // This portion may contain additional references; we only match
            // one at a time.
            '((?s).*)' .
            // Match until the end of the string
            '$/';
	}

	/**
	* Prepares the tree to accept a new tag component
	* @param string class name to create component with
	* @param string XML tag name of component
	* @param array attributes for tag
	* @param boolean whether the tag has contents
	* @return void
	* @access public
	*/
	function openBranch(&$TagInfo, $tag, $attrs, $isEmpty, &$Locator) {
		$this->ParentComponent =& $this->Component;
		
		$this->Component =& $this->createComponent($TagInfo, $tag, $Locator);
   	    $this->Component->emptyClosedTag = $isEmpty;
		$this->buildAttributes($attrs);
		
		$this->checkServerId($this->Component);
		$this->ParentComponent->addChild($this->Component);
		$this->Component->CheckNestingLevel();
	}

	/**
	* Create a new tag component
	* @param string class name to create component with
	* @param string XML tag name of component
	* @return void
	* @access private
	*/
	function &createComponent(&$TagInfo, $tag, &$Locator) {
	    $class = $TagInfo->TagClass;
		$component =& new $class();
        $component->SourceFile = $Locator->getPublicId();
        $component->StartingLineNo = $Locator->getLineNumber(); 
		$component->tag = $tag;
		$component->TagInfo =& $TagInfo;
		$properties = $GLOBALS['PropertyDictionary']->getPropertyList($tag);
		foreach ($properties as $property) {
		    $property->load();
		    $PropertyClass = $property->PropertyClass;
            $component->registerProperty(
                $property->Property, new $PropertyClass($component));

		}
		return $component;
	}

    function &createAttributeExpression($name, $expression) {
        if (strcasecmp($name, 'id') == 0 ) {
            RaiseError('compiler', 'ILLEGALVARREFINATTR', array(
                'tag' => $this->Component->tag,
                'attribute' => $name, 	
                'file' => $this->Component->SourceFile, 
                'line' => $this->Component->StartingLineNo));
        }

        return new AttributeExpression($name, $expression, $this->Component);
    }

    function &createCompoundAttribute($name, $value) {
        if (strcasecmp($name, 'id') == 0 ) {
            RaiseError('compiler', 'ILLEGALVARREFINATTR', array(
                'tag' => $this->Component->tag,
                'attribute' => $name, 	
                'file' => $this->Component->SourceFile, 
                'line' => $this->Component->StartingLineNo));
        }

        $attribute =& new CompoundAttribute($name);
        
        while (preg_match($this->variableReferencePattern, $value, $match)) {
            if (strlen($match[WACT_EXPPARSER_BEFORE_CONTENT]) > 0) {
                $attribute->addAttributeFragment(
                    new AttributeNode($name, $match[WACT_EXPPARSER_BEFORE_CONTENT]));
            }
            
            $attribute->addAttributeFragment(new AttributeExpression($name,
                $match[WACT_EXPPARSER_EXPRESSION], $this->Component));
            
            $value = $match[WACT_EXPPARSER_AFTER_CONTENT];
        }
        if (strlen($value) > 0) {
            $attribute->addAttributeFragment(new AttributeNode($name, $value));
        }
        
        return $attribute;
    }

	/**
	* Create AttributeNodes or ArtributeVariableReferences
	* @param array attributes found in tag
	* @return void
	* @access private
	*/
	function buildAttributes($Attributes) {
		foreach ( $Attributes as $name => $value ) {
            // if there is no expression (common case), shortcut this process
            if (strpos($value, '{$') === FALSE) {
                $attribute =& new AttributeNode($name, $value);
            } else {
                if (preg_match($this->variableReferencePattern, $value, $match)) {
                    if (strlen($match[WACT_EXPPARSER_AFTER_CONTENT]) == 0 && 
                        strlen($match[WACT_EXPPARSER_BEFORE_CONTENT]) == 0) {
                        $attribute =& $this->createAttributeExpression($name, 
                            $match[WACT_EXPPARSER_EXPRESSION]);
                    } else {
                        $attribute =& $this->createCompoundAttribute($name, $value);
                    }
                } else {
                    $attribute =& new AttributeNode($name, $value);
                }
            }

            $this->Component->addChildAttribute($attribute);
		}
	}

	/**
	* Make sure we never have a duplicate Server Id in the component tree we build.
	* Uses GetComponentTree() to fetch current instance of ComponentTree, where list
	* of used tag ids are stored
	* @param object current component
	* @return void
	* @access private
	*/
	function checkServerId(&$Component) {
		$Tree = & GetComponentTree();
		$ServerId = $Component->getServerId();
		if (in_array($ServerId,$Tree->tagIds) ) {
			RaiseError('compiler', 'DUPLICATEID', array(
				'ServerId' => $ServerId,
				'tag' => $Component->tag,
				'file' => $Component->SourceFile, 
				'line' => $Component->StartingLineNo));
		} else {
			$Tree->tagIds[]=$ServerId;
		}
	}

	/**
	* Handles the CDATA content within any tag
	* @param string content of tag
	* @param boolean whether parsing of text is currently forbidden
	* @return void
	* @access public
	*/
	function addContent(&$Locator, $text) {
        // if there is no expression (common case), shortcut this process
        if (strpos($text, '{$') === FALSE) {
            $this->addTextNode($text);
            return;
        }

        while (preg_match($this->variableReferencePattern, $text, $match)) {
            if (strlen($match[WACT_EXPPARSER_BEFORE_CONTENT]) > 0) {
                $this->addTextNode($match[WACT_EXPPARSER_BEFORE_CONTENT]);
            }

            $expression =& new OutputExpression($match[WACT_EXPPARSER_EXPRESSION]);
            $expression->SourceFile = $Locator->getPublicId();
            $expression->StartingLineNo = $Locator->getLineNumber(); 
            $this->Component->addChild($expression);
        
            $text = $match[WACT_EXPPARSER_AFTER_CONTENT];
        }
        if (strlen($text) > 0) {
            $this->addTextNode($text);
        }
	}

	/**
	* Creates TextNodes for plain text
	* @param string text to create node for
	* @return void
	* @access public
	*/
	function addTextNode($text) {
		$LastChild = & $this->Component->getLastChild();
		if ( is_a($LastChild, 'TextNode') ) {
			$LastChild->append($text);
		} else {
			$TextNode =& new TextNode($text);
			$this->Component->addChild($TextNode);
		}
	}

	/**
	* Deals with XML processing instructions. PHP instructions are ignored.
	* @param string target processor
	* @param string instruction
	* @return void
	* @access public
	*/
	function addProcessingInstructionNode($target, $instruction) {
	    // we can optimize here by not loading PHP node until we need it
	    // It will probably be rarely used in templates.
        require_once WACT_ROOT . 'template/compiler/phpnode.inc.php';
	
		// Pass through any PI's except PHP PI's
		$invalid_targets = array('php','PHP','=','');
		if ( !in_array($target, $invalid_targets) ) {
			$php = 'echo "<?'.$target.' '; // Whitespace assumption
			$php.= str_replace('"','\"',$instruction);
			$php.= '?>\n";'; // Newline assumption
			$this->Component->addChild(new PHPNode($php));
		}
	}

	/**
	* Finishes a branch in the component tree, restoring control
	* to the ParentComponent
	* @param boolean whether current component has a closing tag
	* @return void
	* @access public
	*/
	function closeBranch($hasClosingTag) {
		$this->Component->hasClosingTag = $hasClosingTag;
		$this->Component = & $this->ParentComponent;
		$this->ParentComponent = & $this->Component->parent;	
	}
}
?>