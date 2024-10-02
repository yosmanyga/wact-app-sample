<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: codewriter.inc.php,v 1.17 2004/06/30 09:53:02 harryf Exp $
*/
//--------------------------------------------------------------------------------
/**
* Places the CodeWriter in PHP mode
*/
define('CODE_WRITER_MODE_PHP', 1);
/**
* Places the CodeWriter in HTML mode
*/
define('CODE_WRITER_MODE_HTML', 2);
//--------------------------------------------------------------------------------
/**
* Provides an API for generating the compiled template.
* @see http://wact.sourceforge.net/index.php/CodeWriter
* @access public
* @abstract
* @package WACT_TEMPLATE
*/
class CodeWriter {
	/**
	* String containing the compiled template
	* @var string
	* @access private
	*/
	var $code = '';
	/**
	* The current state of the writer.
	* @var int (default CODE_WRITER_MODE_HTML);
	* @access private
	*/
	var $mode = CODE_WRITER_MODE_HTML;
	/**
	* A prefix to add to the compiled template construct and render functions
	* @var string
	* @access private
	*/
	var $FunctionPrefix = '';
	/**
	* A suffix to add to the compiled template construct and render functions
	* @var int (default 1)
	* @access private
	*/
	var $FunctionSuffix = 1;
	/**
	* List of files to write include statements for in the compiled template,
	* such as runtime component class files.
	* @var array
	* @access private
	*/
	var $includeList = array();
	/**
	* Count of the current number of temporary variables in the template
	* @var int (default 1)
	* @access private
	*/
	var $tempVarName = 1;
	/**
	* Constructs CodeWriter, initializing the internal code string
	* @access public
	*/
	function CodeWriter() {
		$this->code = '';
	}

	/**
	* Puts the writer into PHP mode, writing an opening PHP processing
	* instruction to the template. Does nothing if writer is already
	* in PHP mode
	* @return void
	* @access private
	*/
	function switchToPHP() {
		if ($this->mode == CODE_WRITER_MODE_HTML) {
			$this->mode = CODE_WRITER_MODE_PHP;
			$this->code .= '<?php ';
		}
	}

	/**
	* Puts the writer into HTML mode, writing an closing PHP processing
	* instruction to the template. Does nothing if writer is already in
	* HTML mode
	* @return void
	* @access private
	*/
	function switchToHTML($Context = NULL) {
		if ($this->mode == CODE_WRITER_MODE_PHP) {
			$this->mode = CODE_WRITER_MODE_HTML;
			if ($Context === "\n") {
    			$this->code .= " ?>\n";
    	    } else {
    			$this->code .= ' ?>';
    	    }
		}
	}
		
	/**
	* Writes some PHP to the compiled template
	* @param string PHP to write
	* @return void
	* @access public
	*/
	function writePHP($text) {
		$this->switchToPHP();
		$this->code .= $text;
	}

	/**
	* Write PHP Literal String.  Make sure that escape characters are proper
	* for source code escaping of string literal.
	* @param string PHP to write
	* @param boolean optional - text requires escape, defaults to true
	* @return void
	* @access public
	*/
	function writePHPLiteral($text, $escape=true) {
		$this->switchToPHP();
		if ($escape) {
			$this->code .= "'" . $this->escapeLiteral($text) . "'";
		} else {
			$this->code .= "'" . $text . "'";
		}
	}

	/**
	* Escape a string in preperation for writing a PHP Literal String.
	* Make sure that escape characters are proper
	* for source code escaping of string literal.
	* @param string text to escape
	* @return string
	* @access public
	*/
	function escapeLiteral($text) {
		$text = str_replace('\'', "\\'", $text);
		if ( substr($text, -1) == '\\') {
		    $text .= '\\';
		}
		return $text;
	}


	/**
	* Writes some HTML to the compiled template
	* @param string HTML to write
	* @return void
	* @access public
	*/
	function writeHTML($text) {
		$this->switchToHTML(substr($text,0,1));
		$this->code .= $text;
	}

	/**
	* Returns the finished compiled template, adding the include directives
	* at the start of the template
	* @return string
	* @access public
	*/
	function getCode() {
		$this->switchToHTML();
		$includecode = '';
		foreach($this->includeList as $includefile) {
			$includecode .= "require_once '$includefile';\n";
		}
	        if (!empty($includecode)) {
        	    $pattern = '/' . preg_quote('<?php ', '/') . '/';
	            if (preg_match($pattern, $this->code)) {
        	        return preg_replace($pattern, '<?php ' . $includecode, $this->code, 1);
	            } else {
        	        return '<?php ' . $includecode . '?>';
	            }
        	} else {
    			return $this->code;
	        }
	}

	/**
	* Adds an include file (e.g a runtime component class file) to the
	* internal list. Checks that file has not already been included.
	* <br />Note that the path to the file to be included will need to
	* be in PHP's runtime include path.
	* @param string PHP script path/name
	* @return void
	* @access public
	*/
	function registerInclude($includefile) {
		$includefile = str_replace('//','/',$includefile);
		if (!in_array($includefile, $this->includeList)) {
			$this->includeList[] = $includefile;
		}
	}

	/**
	* Begins writing a PHP function to the compiled template, using the
	* FunctionPrefix and the FunctionSuffix, the latter being post incremented
	* by one.
	* @param string parameter string for the function declaration
	* @return string name of the function of form "tpl[Prefix:hash][Suffix:int]
	* @access public
	*/
	function beginFunction($ParamList) {
		$funcname = 'tpl' . $this->FunctionPrefix . $this->FunctionSuffix++;
		$this->writePHP('function ' . $funcname . $ParamList ." {\n");
		return $funcname;
	}

	/**
	* Finish writing a PHP function to the compiled template
	* @return void
	* @access public
	*/
	function endFunction() {
		$this->writePHP(" }\n");
	}

	/**
	* Sets the function prefix
	* @param string prefix for function names to be written
	* @return void
	* @access public
	*/
	function setFunctionPrefix($prefix) {
		$this->FunctionPrefix = $prefix;
	}

	/**
	* Utility method, which generates a unique variable name
	* for custom use within TagComponents.
	* @return string
	* @access public
	*/
	function getTempVariable() {
		$var = $this->tempVarName++;
		if ($var > 675) {
			return chr(65 + ($var/26)/26) . chr(65 + ($var/26)%26) . chr(65 + $var%26);
		} elseif ($var > 26) {
			return chr(65 + ($var/26)%26) . chr(65 + $var%26);
		} else {
			return chr(64 + $var);
		}
	}

	/**
	* Utility method, which generates a unique variable name, prefixed with a $
	* for custom use within TagComponents.
	* @return string
	* @access public
	*/
	function getTempVarRef() {
		return '$'.$this->getTempVariable();
	}
}
?>
