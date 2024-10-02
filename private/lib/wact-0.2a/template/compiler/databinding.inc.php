<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: databinding.inc.php,v 1.13 2004/06/23 23:11:20 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------

/**
* 
* @access protected
* @package WACT_TEMPLATE
*/
class DataBindingExpression  {
	/**
	* Name of the element to bind to.
	* @var string
	* @access private
	*/
	var $name;
	
	/**
	* @access private
	*/
	var $context;
	
	var $path;
	var $DataSourceReferenceVariable;
	
	var $prepared = FALSE;

	/**
	* used for error messages
	* @access private
	*/
	var $expression;

    function DataBindingExpression($expression, &$ComponentContext) {
        $this->expression = $expression;
        $this->context =& $ComponentContext;
    }

	function autoPrepare() {
	    if ($this->prepared) {
	        return;
	    }
	    $originalcontext =& $this->context;
	    $expression = $this->expression;
	    $prefix = substr($expression, 0, 1);
	    if ($prefix == "#") {
    		$this->context =& $this->context->getRootDataSource();
            $expression = substr($expression, 1);
	    } else if ($prefix == "^") {
            while ($prefix == "^") {
                $this->context =& $this->context->getParentDataSource();
                $expression = substr($expression, 1);
                $prefix = substr($expression, 0, 1);
            }
	    }

        $pos = strpos($expression, '.');
        if (is_integer($pos)) {
            $path = array();
    	    while (preg_match('/^(\w+)\.((?s).*)$/', $expression, $match)) {
                $path[] = $match[1];
                $expression = $match[2];
            }
            $this->path = $path;
        }
        
        if (preg_match("/^\w+$/", $expression)) {
            $this->name = $expression;
        } else {
            RaiseError('compiler', 'BAD_BINDING', array(
                'expression' => $this->expression,
                'file' => $originalcontext->SourceFile,
                'line' => $originalcontext->StartingLineNo));
        }
        
        /* pre-defined properties will never be found inside a child context */
        if (is_object($this->context)) {
            $this->property =& $this->context->getProperty($this->name);
            if (is_object($this->property)) {
                $this->property->activate();
            }
        }
        $this->prepared = TRUE;
    }

    function prepare() {
        $this->autoPrepare();
    }
	
	/**
	* Does this expression refer to a constant value (at compile time)?
	* @return Boolean
	* @access public
	*/
	function isConstant() {
	    $this->autoPrepare();
	    if (is_null($this->context)) {
	        return TRUE;
	    }
        if (is_object($this->property)) {
            return $this->property->isConstant();
        }
        return FALSE;
	}

	/**
	* Return the value of this expression
	* @return String
	* @access public
	*/
	function getValue() {
	    $this->autoPrepare();
	    if (is_null($this->context)) {
	        return NULL;
	    }
        if (is_null($this->property) || !$this->property->isConstant()) {
            RaiseError('compiler', 'UNRESOLVED_BINDING', array(
                'expression' => $this->expression,
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
        } else {
            return $this->property->getValue();
        }
	}

	/**
	* Generate setup code for an expression reference
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generatePreStatement(&$code) {
	    $this->autoPrepare();
	    if (is_object($this->context)) {
            if (is_object($this->property)) {
                $this->property->generatePreStatement($code);
            }
            if (isset($this->path)) {
                $path = $this->path;
                $key = array_shift($path);
                $this->DataSourceReferenceVariable = $code->getTempVariable();
    		    $parent_datasource =& $this->context->getDataSource();
                $code->writePHP('$' . $this->DataSourceReferenceVariable . '=&Template::_dereference(' . $parent_datasource->getDataSourceRefCode() . ',');
                $code->writePHPLIteral($key);
                $code->writePHP(');');
                foreach ($path as $key) {
                    $DataSourceReferenceVariable = $code->getTempVariable();
                    $code->writePHP('$' . $DataSourceReferenceVariable . '=&Template::_dereference($' . $this->DataSourceReferenceVariable . ',');
                    $code->writePHPLIteral($key);
                    $code->writePHP(');');
                    $this->DataSourceReferenceVariable = $DataSourceReferenceVariable;
                }
            }
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
	    $this->autoPrepare();
	    if (is_null($this->context)) {
            $code->writePHP('NULL');
	    } else {
            if (is_object($this->property)) {
                $this->property->generateExpression($code);
            } else {
                if (isset($this->DataSourceReferenceVariable)) {
                    $code->writePHP('$' . $this->DataSourceReferenceVariable . '->get(');
                    $code->writePHPLiteral($this->name);
                    $code->writePHP(')');
                } else {
                    $code->writePHP($this->context->getDataSourceRefCode() . '->get(');
                    $code->writePHPLiteral($this->name);
                    $code->writePHP(')');
                }
            }
        }
	}

	/**
	* Generate tear down code for an expression reference
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generatePostStatement(&$code) {
	    $this->autoPrepare();
	    if (is_object($this->context)) {
            if (is_object($this->property)) {
                $this->property->generatePostStatement($code);
            }
        }
    }
	
}

?>