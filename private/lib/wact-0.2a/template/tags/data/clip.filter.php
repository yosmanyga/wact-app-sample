<?php
//--------------------------------------------------------------------------------
// Copyright 2004 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_FILTER
* @author	Jason E. Sweat < jsweat_php AT yahoo DOT com >
* @version $Id: clip.filter.php,v 1.4 2004/11/18 04:22:48 jeffmoore Exp $
*/

FilterDictionary::registerFilter(
    new FilterInfo('clip', 'ClipFilter', 1, 4), __FILE__);

/**
* substr wraper
*
* plus some nice features about wrapping at a word boundary
* parameters are as follows:
* - length - integer - required - how long to make the string
* - start - integer - optional - where to start (0 offset)
* - terminator - string - optional - what to append to the end, i.e. "..."
* - word boundary - char - anything but first letter "n" treated as yes, trim at a word boundary
* @package WACT_FILTER
* @author	Jason E. Sweat < jsweat_php AT yahoo DOT com >
*/
class ClipFilter extends CompilerFilter {
	/**#@+
	 * @access private
	 */
	var $str;
	var $strlen;
	var $start;
	var $len;
	var $suffix;
	var $match;
	/**#@-*/
	
	/**
	* Return this value as a PHP value
	* @return String
	* @access public
	*/
	function getValue() {
	    if ($this->isConstant()) {
			$value = $this->base->getValue();
			switch (count($this->parameters)) {
			case 1:
				return substr($value, 0, $this->parameters[0]->getValue());
				break;
			case 2:
				return substr($value, $this->parameters[1]->getValue(), $this->parameters[0]->getValue());
				break;
			case 3:
				$suffix = (strlen($value) > $this->parameters[0]->getValue() + $this->parameters[1]->getValue())
					? $this->parameters[2]->getValue() 
					: '';
				return substr($value, $this->parameters[1]->getValue(), $this->parameters[0]->getValue()).$suffix;
				break;
			case 4:
				if (strtoupper(substr($this->parameters[3]->getValue(),0,1)) != 'N') {
					preg_match('~^(.{0,'.$this->parameters[0]->getValue().'}(?U)\w*)\b~ism', substr($value, $this->parameters[1]->getValue()), $match);
					$suffix = (strlen($match[1]) < $this->parameters[0]->getValue())
						? ''
						: $this->parameters[2]->getValue();
					return $match[1].$suffix;
				} else {
					preg_match('~^(.{0,'.$this->parameters[0]->getValue().'})~ism', substr($value, $this->parameters[1]->getValue()), $match);
					$suffix = (strlen($match[1]) < $this->parameters[0]->getValue())
						? ''
						: $this->parameters[2]->getValue();
					return $match[1].$suffix;
				}
				break;
			default:
			    die("Internal error");
			}
	    } else {
            RaiseError('compiler', 'UNRESOLVED_BINDING');
	    }
    }
    
	/**
	* Generate setup code for an expression reference
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generatePreStatement(&$code) {
		parent::generatePreStatement($code); 
		switch (count($this->parameters)) {
		case 3:
			$this->str = $code->getTempVarRef();
			$this->strlen = $code->getTempVarRef();
			$this->len = $code->getTempVarRef();
			$this->start = $code->getTempVarRef();
			$this->suffix = $code->getTempVarRef();
			
			$code->writePHP($this->str.'=');
			$this->base->generateExpression($code);
			$code->writePHP(';');
			
			$code->writePHP($this->strlen.'=strlen('.$this->str.');');
			
			$code->writePHP($this->len.'=');
			$this->parameters[0]->generateExpression($code);
			$code->writePHP(';');
			
			$code->writePHP($this->start.'=');
			$this->parameters[1]->generateExpression($code);
			$code->writePHP(';');
			
			$code->writePHP($this->suffix.'=('.$this->strlen.'>'.$this->start.'+'.$this->len.')?');
			$this->parameters[2]->generateExpression($code);
			$code->writePHP(':\'\';');
	    	break;
		case 4:
			$this->str = $code->getTempVarRef();
			$this->strlen = $code->getTempVarRef();
			$this->len = $code->getTempVarRef();
			$this->start = $code->getTempVarRef();
			$this->suffix = $code->getTempVarRef();
			$this->match = $code->getTempVarRef();
			$code->writePHP($this->str.'=');
			$this->base->generateExpression($code);
			$code->writePHP(';'.$this->strlen.'=strlen('.$this->str.');');
			$code->writePHP($this->len.'=');
			$this->parameters[0]->generateExpression($code);
			$code->writePHP(';'.$this->start.'=');
			$this->parameters[1]->generateExpression($code);
			$code->writePHP(';if (strtoupper(substr(');
				$this->parameters[3]->generateExpression($code);
				$code->writePHP(',0,1))!="N") {');
 					$code->writePHP('preg_match("~^(.{0,'.$this->len.'}\w*)\b~ims", substr('.$this->str.','.$this->start.'), '.$this->match.');');
 			$code->writePHP('}else{');
 					$code->writePHP('preg_match("~^(.{0,'.$this->len.'})~ims", substr('.$this->str.','.$this->start.'), '.$this->match.');}');
			$code->writePHP($this->str.'='.$this->match.'[1];');
			$code->writePHP($this->strlen.'=strlen('.$this->str.');');
			$code->writePHP($this->suffix.'=('.$this->strlen.'>='.$this->len.')?');
			$this->parameters[2]->generateExpression($code);
			$code->writePHP(':"";');
	    	break;
		default:
			//okay
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
	    switch (count($this->parameters)) {
	    case 1:
			$code->writePHP('substr(');
			$this->base->generateExpression($code);
			$code->writePHP(',0,');
			$this->parameters[0]->generateExpression($code);
			$code->writePHP(')');
	    	break;
	    case 2:
			$code->writePHP('substr(');
			$this->base->generateExpression($code);
			$code->writePHP(',');
			$this->parameters[1]->generateExpression($code);
			$code->writePHP(',');
			$this->parameters[0]->generateExpression($code);
			$code->writePHP(')');
	    	break;
	    case 3:
			$code->writePHP('substr('.$this->str.','.$this->start.','.$this->len.').'.$this->suffix);
	    	break;
	    case 4:
			$code->writePHP($this->str.'.'.$this->suffix);
	    	break;
	    default:
	    	//error
	    }
    }

}

?>