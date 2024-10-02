<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_VALIDATION
* @version $Id: numeric.inc.php,v 1.8 2004/11/16 01:55:38 jeffmoore Exp $
*/

require_once WACT_ROOT . 'validation/rule.inc.php';

/**
* Check that a field is a valid numeric value and its precision falls
* within allowable parameters.
* @see http://wact.sourceforge.net/index.php/NumericPrecisionRule
* @access public
* @package WACT_VALIDATION
*/
class NumericPrecisionRule extends SingleFieldRule {
	/**
	* Allowable Number of Decimal Digits
	* @var integer
	* @access private
	*/
	var $DecimalDigits;
	/**
	* Allowable Number of Whole Digits
	* @var integer
	* @access private
	*/
	var $WholeDigits;

	/**
	* Constructs NumericPrecisionRule
	* @param string fieldname to validate
	* @param integer maximum digits allowed for whole portion of this number
	* @param integer Maximum digits allowed for decimal portion of this number
	* @access public
	*/
	function NumericPrecisionRule($fieldname, $WholeDigits, $DecimalDigits = NULL) {
		parent :: SingleFieldRule($fieldname);

		if (is_null($DecimalDigits)) {
		    $this->DecimalDigits = 0;
		} else {
		    $this->DecimalDigits = $DecimalDigits;
		}
		$this->WholeDigits = $WholeDigits;
	}

	/**
	* Performs validation of a single value
	* @param string value to validate
	* @access protected
	* @return void
	*/
    function Check($value) {
        if (preg_match('/^[+-]?(\d*)\.?(\d*)$/', $value, $match)) {
            if (strlen($match[1]) > $this->WholeDigits) {
                $this->Error('TOO_MANY_WHOLE_DIGITS',
                    array('maxdigits' => $this->WholeDigits,
                        'digits' => strlen($match[1])));
            }
            if (strlen($match[2]) > $this->DecimalDigits) {
                $this->Error('TOO_MANY_DECIMAL_DIGITS',
                    array('maxdigits' => $this->DecimalDigits,
                        'digits' => strlen($match[2])));
            }
        } else {
            $this->Error('NOT_NUMERIC');
        }
	}
}

/**
* Make sure field value is within a specific numeric range
* @see http://wact.sourceforge.net/index.php/NumericRangeRule
* @access public
* @package WACT_VALIDATION
*/
class NumericRangeRule extends SingleFieldRule {
    /**
    * Minumum value
    * @var int
    * @access private
    */
    var $min;
    /**
    * Maximum value
    * @var int
    * @access private
    */
    var $max;

    /**
    * Constructs SizeRangeRule
    * @param string fieldname to validate
    * @param int Minumum value
    * @param int Maximum value (optional)
    * @access public
    */
    function NumericRangeRule($fieldname, $min, $max = NULL) {
        parent :: SingleFieldRule($fieldname);
        if (is_null($max)) {
            $this->minLength = NULL;
            $this->max = $min;
        } else {
            $this->min = $min;
            $this->max = $max;
        }
    }

	/**
	* Performs validation of a single value
	* @param string value to validate
	* @access protected
	* @return void
	*/
    function Check($value) {
        if (!function_exists('floatval')) {
            require_once WACT_ROOT . 'util/phpcompat/floatval.php';
        }
        if (!is_null($this->min) && (floatval($value) < $this->min)) {
            $this->Error('RANGE_TOO_SMALL', array('min' => $this->min));
        } else if (floatval($value) > $this->max) {
            $this->Error('RANGE_TOO_BIG', array('max' => $this->max));
        }
    }
}

?>