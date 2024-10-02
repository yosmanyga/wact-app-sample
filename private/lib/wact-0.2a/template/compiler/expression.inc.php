<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: expression.inc.php,v 1.29 2004/11/18 04:22:46 jeffmoore Exp $
*/

require_once WACT_ROOT . 'template/compiler/expressionlexer.inc.php';

//--------------------------------------------------------------------------------
/**
* Represents a single Expression found in the template. Responsibly for parsing
* the expression and building a filter chain for the expression (if expression
* contained filter syntax)
* @access protected
* @package WACT_TEMPLATE
*/
class Expression {
	
	/**
	* Tree of compiler filters
	* @var object subclass of CompileFilter
	* @access private
	*/
	var $filterChain;

	/**
	* used for error messages
	* @var string
	* @access private
	*/
	var $expression;

	/**
	* @param string expression
	* @param object Compiler Component that attribute was found in
	* @param string (optional) default filter to apply
	* @access protected
	*/
	function Expression($expression, &$ComponentContext, $DefaultFilter = 'raw') {
		$this->expression = $expression;
		$ApplyDefaultFilter = TRUE;
		if (preg_match('/^(.*)\s*\|\s*raw$/is', $expression, $match)) {
			$ApplyDefaultFilter = FALSE;
			$expression = $match[1];
		}
		if ($DefaultFilter == 'raw') {
			$ApplyDefaultFilter = FALSE;
		}
		
		$pos = strpos($expression, "|");
		if ($pos === FALSE) {
			$base =& $this->createValue($expression, $ComponentContext);
		} else {
			$dbe = trim(substr($expression, 0, $pos));
			$filters = trim(substr($expression, $pos + 1));
			$base =& $this->createFilterChain(
				$filters,
				$this->createValue($dbe, $ComponentContext),
				$ComponentContext);
		}
		
		if ($ApplyDefaultFilter) {
			$fd =& FilterDictionary::getInstance();
			$filterInfo =& $fd->getFilterInfo($DefaultFilter);
			if (is_object($filterInfo)) {
			    $filterInfo->load();
				$filter_class = $filterInfo->FilterClass;

                // Don't apply the default filter if the last filter in the
                // chain is already that filter.
				if (strcasecmp(get_class($base), $filter_class) == 0) {
        			$this->filterChain =& $base;
				} else {
                    $filter =& new $filter_class();
                    $filter->registerBase($base);
                    $this->filterChain =& $filter;
				}
			} else {
				RaiseError('compiler', 'MISSING_FILTER', array(
					'filter' => $DefaultFilter,
					'file' => $ComponentContext->SourceFile,
					'line' => $ComponentContext->StartingLineNo));
			}
		} else {
			$this->filterChain =& $base;
		}
	}

	/**
	* Parses an expression and returns an object representing the expression
	* @param string expression
	* @param object Compiler Component that attribute was found in
	* @return object ConstantProperty or DataBindingExpression
	* @access private
	*/
	function &createValue($expression, &$ComponentContext) {
		$Parser = & new ExpressionValueParser($expression);

		switch ( $Parser->ValueType ) {
			// NULL and BOOLEAN values left out on purpose
			case EXPRESSION_VALUE_INT:
			case EXPRESSION_VALUE_FLOAT:
			case EXPRESSION_VALUE_STRING:
				return new ConstantProperty($Parser->Value);
			break;

			case EXPRESSION_VALUE_DATABINDING:
			default:
				return new DataBindingExpression($expression, $ComponentContext);
			break;

		}

	}

	/**
	* Parses an expression, building a chain of filters for it
	* @param string expression
	* @param object base filter to start with (CompilerFilter)
	* @param object Compiler Component that attribute was found in
	* @return object filter with chain of filters attached
	* @access private
	*/
	function &createFilterChain($expr, $Value, &$ComponentContext) {

		$Fd =& FilterDictionary::getInstance();

		$FFp = & new ExpressionFilterFindingParser($expr);

		$Base = & $Value;

		if ( count($FFp->Filters) == 0 ) {
			return $Value;
		}

		foreach ( $FFp->Filters as $filter_expr ) {

			$Fp = & new ExpressionFilterParser($filter_expr);

			if ( is_null($Fp->Name) ) {
				RaiseError('compiler', 'INVALID_FILTER_SPEC', array(
					'file' => $ComponentContext->SourceFile,
					'line' => $ComponentContext->StartingLineNo));
				return $Value;
			}

			$FilterInfo =& $Fd->getFilterInfo($Fp->Name);

			if (!is_object($FilterInfo)) {
				RaiseError('compiler', 'MISSING_FILTER', array(
					'filter' => $Fp->Name,
					'file' => $ComponentContext->SourceFile,
					'line' => $ComponentContext->StartingLineNo));
				return $Value;
			}
			
            $FilterInfo->load();
			$filter_class = $FilterInfo->FilterClass;
			$Filter =& new $filter_class();

			if ( !is_null($Fp->Args) ) {

				$numArgs = count($Fp->Args);

				if ( $numArgs < $FilterInfo->MinParameterCount ) {
					RaiseError('compiler', 'MISSING_FILTER_PARAMETER', array(
						'filter' => $Fp->Name,
						'file' => $ComponentContext->SourceFile,
						'line' => $ComponentContext->StartingLineNo));
					return $Value;
				}

				if ($numArgs > $FilterInfo->MaxParameterCount) {
					RaiseError('compiler', 'TOO_MANY_PARAMETERS', array(
						'filter' => $Fp->Name,
						'file' => $ComponentContext->SourceFile,
						'line' => $ComponentContext->StartingLineNo));
					return $Value;
				}

				foreach ( $Fp->Args as $value_expr ) {

					$Filter->registerParameter($this->createValue($value_expr, $ComponentContext));

				}
			}

			$Filter->registerBase($Base);

			$Base = & $Filter;

		}

		return $Base;

	}

	/**
	* Does this expression refer to a constant value (at compile time)?
	* @return Boolean
	* @access public
	*/
	function isConstant() {
  		return $this->filterChain->isConstant();
	}

	/**
	* Return the value of this expression
	* @return String
	* @access public
	*/
	function getValue() {
		return $this->filterChain->getValue();
	}

	/**
	* Generate setup code for an expression reference
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generatePreStatement(&$code) {
		$this->filterChain->generatePreStatement($code);
	}

	/**
	* Generate the code to read the data value at run time
	* Must generate only a valid PHP Expression.
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateExpression(&$code) {
		$this->filterChain->generateExpression($code);
	}

	/**
	* Generate tear down code for an expression reference
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generatePostStatement(&$code) {
		$this->filterChain->generatePostStatement($code);
	}

	/**
	* Calls the prepare method on the root of the filter chain
	* @return void
	* @access protected
	*/
	function prepare() {
		return $this->filterChain->prepare();
	}

}

//--------------------------------------------------------------------------------
/**#@+
 * Value parser constants
 */
define ('EXPRESSION_VALUE_DATABINDING',0);
define ('EXPRESSION_VALUE_INT',1);
define ('EXPRESSION_VALUE_FLOAT',2);
define ('EXPRESSION_VALUE_STRING',3);
//--------------------------------------------------------------------------------
/**
* Searches expression strings for constant values
* WARNING: this parser defaults to data binding expressions. That means if it
* doest recognise a integer, float or string constant, what it calls a data binding
* expression may not in fact be a data binding expression. It assumes that
* Expression::createValue asks it a parse a valid value string
* @package WACT_TEMPLATE
* @access protected
*/
class ExpressionValueParser {
	/**
	* @var int constant identifying type of value
	* @access protected
	*/
	var $ValueType = EXPRESSION_VALUE_DATABINDING;

	/**
	* @var mixed constant value (string/int/float)
	* @access protected
	*/
	var $Value;

	/**
	* Invokes the Lexer to parse the expression
	* @param string expression to parse
	*/
	function ExpressionValueParser($expression) {
		$Lexer = & ExpressionValueParser::getLexer();
		$Lexer->parse($expression);
	}

	/**
	* Lexer callback - accepts the default data binding
	* expression - called if no constants found
	* @param string parsed expression
	* @param int expression lexer state
	* @access private
	* @return boolean TRUE
	*/
	function acceptDatabinding($expression,$state) {
		switch ( $state ) {
			case EXPRESSION_LEXER_UNMATCHED:
				// This doesnt actually get used in Expression::getValue
				$this->Value = $expression;
			break;
		}
		return TRUE;
	}

	/**
	* Lexer callback - called for integer constants
	* @param string matched integer
	* @param int expression lexer state
	* @access private
	* @return boolean TRUE
	*/
	function acceptInteger($int,$state) {
		switch ( $state ) {
			case EXPRESSION_LEXER_SPECIAL:
				$this->ValueType = EXPRESSION_VALUE_INT;
				$this->Value = intval($int);
			break;
		}

		return TRUE;
	}

	/**
	* Lexer callback - called for float constants
	* @param string matched float
	* @param int expression lexer state
	* @access private
	* @return boolean TRUE
	*/
	function acceptFloat($float,$state) {
		switch ( $state ) {
			case EXPRESSION_LEXER_SPECIAL:
				$this->ValueType = EXPRESSION_VALUE_FLOAT;
				if (!function_exists('floatval')) {
					require_once WACT_ROOT . 'util/phpcompat/floatval.php';
				}
				$this->Value = floatval($float);
			break;
		}
		return TRUE;
	}

	/**
	* Lexer callback - called for string constants
	* @param string matched string
	* @param int expression lexer state
	* @access private
	* @return boolean TRUE
	*/
	function acceptString($string,$state) {
		switch ( $state ) {
			case EXPRESSION_LEXER_SPECIAL:
				$this->ValueType = EXPRESSION_VALUE_STRING;

				// Strip the quotes
				// hack but saves introducing further Lexer complexity
				$string = substr($string,1,strlen($string)-2);

				$this->Value = $string;
			break;
		}
		return TRUE;
	}

	/**
	* Creates the Lexer. Ideally this should be a static instance for
	* performance but Lexer left in strange state after parsing if
	* static
	* @return ExpressionLexer
	* @access private
	*/
	function & getLexer() {
		$Lexer = new ExpressionLexer($this,'databinding');

		$Lexer->addSpecialPattern('^-?\d+$','databinding','integer');
		$Lexer->addSpecialPattern('^-?\d+\.\d+$','databinding','float');
		$Lexer->addSpecialPattern('^".*"$','databinding','doublequote');
		$Lexer->addSpecialPattern('^\'.*\'$','databinding','singlequote');

		$Lexer->mapHandler('databinding','acceptDatabinding');
		$Lexer->mapHandler('integer','acceptInteger');
		$Lexer->mapHandler('float','acceptFloat');
		$Lexer->mapHandler('doublequote','acceptString');
		$Lexer->mapHandler('singlequote','acceptString');

		return $Lexer;
	}
}

//--------------------------------------------------------------------------------
/**
* Searches expression strings for filters
* WARNING: this parser expects the initial variable / value to have been stripped
* as happens in the Expression constructor
* @package WACT_TEMPLATE
* @access protected
*/
class ExpressionFilterFindingParser {

	/**
	* List of values found in expression, marked by filter delimiter
	* @var array
	* @access protected
	*/
	var $Filters = array();

	/**
	* Current value
	* @var mixed NULL when no value or string as value is built
	* @access private
	*/
	var $filter = NULL;

	/**
	* Invokes the Lexer to parse the expression
	* @param string expression to parse
	*/
	function ExpressionFilterFindingParser($expression) {
		$Lexer = & ExpressionFilterFindingParser::getLexer();
		$Lexer->parse($expression);

		// Make sure final value added to values
		if ( !is_null($this->filter) ) {
			$this->Filters[] = $this->filter;
		}
	}

	/**
	* Lexer callback - called for value strings
	* @param string
	* @param int expression lexer state
	* @access private
	* @return boolean TRUE
	*/
	function acceptFilter($filter,$state) {
		switch ( $state ) {
			case EXPRESSION_LEXER_UNMATCHED:
				if ( is_null($this->filter) ) {
					$this->filter = $filter;
				} else {
					$this->filter .= $filter;
				}
			break;
			case EXPRESSION_LEXER_SPECIAL:
				if ( is_null($this->filter) ) {
					$this->filter = $filter;
				} else {
					$this->filter .= $filter;
				}
			break;
		}
		return TRUE;
	}

	/**
	* Lexer callback - called every time a filter delimiter
	* is found. Populates the Values array with the current
	* value
	* @access private
	* @return boolean TRUE
	*/
	function addFilter() {
		if ( !is_null ($this->filter) ) {
			$this->Filters[] = $this->filter;
			$this->filter = NULL;
		}
		return TRUE;
	}
	
	/**
	* Creates the Lexer.
	* @return ExpressionLexer
	* @access private
	*/
	function & getLexer() {
		$Lexer = new ExpressionLexer($this,'filter');

		$Lexer->addSpecialPattern('\|','filter','add');
//		$Lexer->addSpecialPattern('".*"','filter','doublequote');
//		$Lexer->addSpecialPattern('\'.*\'','filter','singlequote');
		$Lexer->addSpecialPattern('"[^"]*"','filter','doublequote');
		$Lexer->addSpecialPattern("'[^']*'",'filter','singlequote');

		$Lexer->mapHandler('filter','acceptFilter');
		$Lexer->mapHandler('doublequote','acceptFilter');
		$Lexer->mapHandler('singlequote','acceptFilter');
		$Lexer->mapHandler('add','addFilter');

		return $Lexer;
	}
}

/**
* Parses a single filter expression
* WARNING: this parser expects strings parsed by ExpressionFilterFindingParser
* @package WACT_TEMPLATE
* @access protected
*/
class ExpressionFilterParser {

	/**
	* Name of the filter
	* @var string
	* @access protected
	*/
	var $Name = NULL;

	/**
	* List of arguments
	* @var array
	* @access protected
	*/
	var $Args = NULL;

	/**
	* Current argument
	* @var string
	* @access private
	*/
	var $arg = NULL;

	/**
	* Invokes the Lexer to parse the expression
	* @param string expression to parse
	*/
	function ExpressionFilterParser($expression) {
		$Lexer = & ExpressionFilterParser::getLexer();
		$Lexer->parse($expression);

		// Make sure remaining argument added
		if ( !is_null ($this->Args) && !is_null($this->arg) ) {
			$this->Args[] = $this->arg;
		}
	}

	/**
	* Lexer callback - if the : delimiter is found, prepares the Args array
	* @access private
	* @return boolean TRUE
	*/
	function initArgs() {
		$this->Args = array();
		return TRUE;
	}

	/**
	* Lexer callback - if the , delimiter is found, adds the current arg
	* to the Args array
	* @access private
	* @return boolean TRUE
	*/
	function addArg() {
		if ( !is_null($this->Args) && !is_null($this->arg) ) {
			$this->Args[] = $this->arg;
			$this->arg = NULL;
		}
		return TRUE;
	}

	/**
	* Lexer callback - accepts a single argument
	* @param string
	* @param int expression lexer state
	* @access private
	* @return boolean TRUE
	*/
	function accept($expression,$state) {

		switch ( $state ) {

			case EXPRESSION_LEXER_UNMATCHED:
				if ( !is_null($this->Args) ) {
					if ( is_null($this->arg) ) {
						$this->arg = $expression;
					} else {
						$this->arg .= $expression;
					}
				} else {
					if ( is_null($this->Name) ) {
						$this->Name = $expression;
					}
				}
			break;

			case EXPRESSION_LEXER_SPECIAL:
				if ( !is_null($this->Args) ) {
					if ( is_null($this->arg) ) {
						$this->arg = $expression;
					} else {
						$this->arg .= $expression;
					}
				}
			break;
		}

		return TRUE;
	}

	/**
	* Creates the Lexer.
	* @return ExpressionLexer
	* @access private
	*/
	function & getLexer() {
		$Lexer = new ExpressionLexer($this,'value');

		$Lexer->addSpecialPattern(':','value','args');
		$Lexer->addSpecialPattern(',','value','arg');

		$Lexer->addSpecialPattern('"[^"]*"','value','doublequote');
		$Lexer->addSpecialPattern("'[^']*'",'value','singlequote');

		$Lexer->addPattern('\s','value');

		$Lexer->mapHandler('value','accept');
		$Lexer->mapHandler('args','initArgs');
		$Lexer->mapHandler('arg','addArg');
		$Lexer->mapHandler('doublequote','accept');
		$Lexer->mapHandler('singlequote','accept');


		return $Lexer;
	}

}
?>