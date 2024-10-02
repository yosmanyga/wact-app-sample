<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: attributenode.inc.php,v 1.8 2004/11/15 16:21:09 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------

/**
* Stores literal attributes found inside tag components
* @see http://wact.sourceforge.net/index.php/AttributeNode
* @access public
* @package WACT_TEMPLATE
*/
class AttributeNode {
    var $name;
    var $value;

    function AttributeNode($name, $value) {
        $this->name = $name;
        $this->value = $value;
    }

    /**
    * Can a PHP value for this reference be calculated at compile time?
    * @return Boolean
    * @access public
    */
    function isConstant() {
        return TRUE;
    }

    /**
    * Return the value of this attribute
    * @return String
    * @access public
    */
    function getValue() {
        static $table;
        if (!isset($table)) {
            $table = array_flip(get_html_translation_table( HTML_SPECIALCHARS, ENT_QUOTES ));
        }
        /* special case for HTML tags like <option selected> where selected attribute has value NULL */
        if ( !is_null($this->value) ) {
            /* translate entities to their real values */
            return strtr($this->value, $table);
        }
    }
    
    /**
    * Generate the attribute value portion of this attribute
    * @param CodeWriter
    * @return void
    * @access protected
    */
    function generateFragment(&$code) {
        $code->writeHTML(htmlspecialchars($this->getValue(), ENT_QUOTES));
    }
    
    /**
    * Generate the code
    * @param CodeWriter
    * @return void
    * @access protected
    */
    function generate(&$code) {
        $code->writeHTML(' ' . $this->name);
        if (!is_null($this->value)) {
            $code->writeHTML('="');
            $this->generateFragment($code);
            $code->writeHTML('"');
        }
    }

	/**
	* Generate setup code for an expression reference
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generatePreStatement(&$code) {
    }
    
	/**
	* Generate the code to read the data value at run time
	* Must generate only a valid PHP Expression.
	* @param CodeWriter
	* @return void
	* @access protected
	*/
    function generateExpression(&$code) {
        $code->writePHPLiteral($this->getValue());
    }

	/**
	* Generate tear down code for an expression reference
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generatePostStatement(&$code) {
    }

    /**
    * Prepare this attribute to be used
    * @return void
    */
    function prepare() {
    }
    
}

/**
* Used to store attribute expressions found inside tag components
* @see http://wact.sourceforge.net/index.php/AttributeNode
* @access public
* @package WACT_TEMPLATE
*/
class AttributeExpression {

    var $name;
    var $expression;
    
    function AttributeExpression($name, $expression, &$context) {
        $this->name = $name;
        $this->expression =& $this->createExpression($expression, $context);
    }

    function &createExpression($expression, &$context) {
        return new Expression($expression, $context, 'raw');
    }

    /**
    * Can a PHP value for this reference be calculated at compile time?
    * @return Boolean
    * @access public
    */
    function isConstant() {
        return $this->expression->isConstant();
    }

    /**
    * Return the value of this attribute, usually for further prossing.
    * @return String
    * @access public
    */
    function getValue() {
        // PARCHE ////////////////////////////////////////////////
        if (isset($GLOBALS['responseModel'])) {
			$responseModel = $GLOBALS['responseModel'];
			if ($responseModel->get($this->expression->expression)) {
				return $responseModel->get($this->expression->expression);
			}
		}
        //////////////////////////////////////////////////////////
        return $this->expression->getValue();
    }

    /**
    * Generate the attribute value portion of this attribute
    * @param CodeWriter
    * @return void
    * @access protected
    */
    function generateFragment(&$code) {
        if ($this->isConstant()) {
            $value = $this->getValue();
            if (!is_null($value)) {
                $code->writeHTML(htmlspecialchars($value, ENT_QUOTES));
            }
        } else {
            $this->expression->generatePreStatement($code);
            $code->writePHP('echo htmlspecialchars(');
            $this->expression->generateExpression($code);
            $code->writePHP(', ENT_QUOTES);');
            $this->expression->generatePostStatement($code);
        }
    }

    /**
    * Generate the code to output this attribute as part of a tag.
    * @param CodeWriter
    * @return void
    * @access protected
    */
    function generate(&$code) {
        $code->writeHTML(' ' . $this->name);
        $code->writeHTML('="');
        $this->generateFragment($code);
        $code->writeHTML('"');
    }

	/**
	* Generate setup code for an expression reference
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generatePreStatement(&$code) {
        $this->expression->generatePreStatement($code);
    }
    
	/**
	* Generate the code to read the data value at run time
	* Must generate only a valid PHP Expression.
	* @param CodeWriter
	* @return void
	* @access protected
	*/
    function generateExpression(&$code) {
        $this->expression->generateExpression($code);
    }

	/**
	* Generate tear down code for an expression reference
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generatePostStatement(&$code) {
        $this->expression->generatePostStatement($code);
    }

    /**
    * Prepare this attribute to be used
    * @return void
    */
    function prepare() {
        return $this->expression->prepare();
    }

}

class CompoundAttribute {
    var $name;
    var $fragments = array();
    
    
    function CompoundAttribute($name) {
        $this->name = $name;
    }
    
    /**
    * Add a fragment of an attribute
    * @param Attribute Attribute fragment object
    * @return void
    * @access public
    */
    function addAttributeFragment(&$fragment) {
        $this->fragments[] =& $fragment;
    }    

    /**
    * Can a PHP value for this reference be calculated at compile time?
    * @return Boolean
    * @access public
    */
    function isConstant() {
        $isConstant = TRUE;
		foreach( array_keys($this->fragments) as $key) {
			$isConstant = $isConstant && $this->fragments[$key]->isConstant();
		}
		return $isConstant;
    }

    /**
    * Return the value of this attribute
    * @return String
    * @access public
    */
    function getValue() {
        $value = "";
		foreach( array_keys($this->fragments) as $key) {
			$value .= $this->fragments[$key]->getValue();
		}
		return $value;
    }    

    /**
    * Generate the code
    * @param CodeWriter
    * @return void
    * @access protected
    */
    function generate(&$code) {
        $code->writeHTML(' ' . $this->name);
        $code->writeHTML('="');
 		foreach( array_keys($this->fragments) as $key) {
			$this->fragments[$key]->generateFragment($code);
		}
        $code->writeHTML('"');
    }

	/**
	* Generate setup code for an expression reference
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generatePreStatement(&$code) {
		foreach( array_keys($this->fragments) as $key) {
			$this->fragments[$key]->generatePreStatement($code);
		}
    }

	/**
	* Generate the code to read the data value at run time
	* Must generate only a valid PHP Expression.
	* @param CodeWriter
	* @return void
	* @access protected
	*/
    function generateExpression(&$code) {
        $code->writePHP('(');
        $separator = '';
		foreach( array_keys($this->fragments) as $key) {
			$code->writePHP($separator);
			$this->fragments[$key]->generateExpression($code);
			$separator = ".";
		}
        $code->writePHP(')');
    }

	/**
	* Generate tear down code for an expression reference
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generatePostStatement(&$code) {
		foreach( array_keys($this->fragments) as $key) {
			$this->fragments[$key]->generatePostStatement($code);
		}
    }

    /**
    * Prepare this attribute to be used
    * @return void
    */
    function prepare() {
		foreach( array_keys($this->fragments) as $key) {
			$this->fragments[$key]->prepare();
		}
    }

}

?>
