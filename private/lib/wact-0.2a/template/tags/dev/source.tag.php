<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: source.tag.php,v 1.5 2004/11/18 04:22:48 jeffmoore Exp $
*/

/**
* Register the tag
*/
TagDictionary::registerTag(new TagInfo('dev:SOURCE', 'DevSourceTag'), __FILE__);

/**
* Displays the source code written into the compiled template, for the
* section of the template in contains.
* Note that position of this tag <i>will</i> matter. It cannot be round a
* an input tag in a form tag, for example, where the nesting level will
* result in an error. It may also result in a mess in terms of HTML
* @see http://wact.sourceforge.net/index.php/DevSourceTag
* @access protected
* @package WACT_TAG
*/
class DevSourceTag extends CompilerDirectiveTag {
	/**
	* Position in the CodeWriter::code string containing the compiled code,
	* at which the dev:source tag was inserted.
	* @var int
	* @access private
	*/
	var $startPos;
	/**
	* Writing mode the CodeWriter was in, when the dev:source tag was
	* inserted
	* @var int
	* @access private
	*/	
	var $startMode;
	/**
	* @return int PARSER_REQUIRE_PARSING
	* @access protected
	*/
	function preParse() {
		return PARSER_REQUIRE_PARSING;
	}

	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function preGenerate(&$code) {
		parent::preGenerate($code);
		$this->startPos = strlen($code->code);
		$this->startMode = $code->mode;
	}
	
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function postGenerate(&$code) {
		$source = substr($code->code,$this->startPos);

		if ( !$this->getBoolAttribute('raw') ) {
		
			// Could do this better,  perhaps with indents. Course that
			// needs some kind of parser... or use the Tokenizer.
			$tmp_source = str_replace('<?php',"\n<?php\n",$source);
			$tmp_source = str_replace(';',";\n",$tmp_source);
			$tmp_source = str_replace('{',"{\n",$tmp_source);
			$tmp_source = str_replace('}',"}\n",$tmp_source);
			$tmp_source = str_replace('?>',"\n?>\n",$tmp_source);

		} else {
			$tmp_source = $source;
		}
		
		// Done this way for backwards compatiblity for PHP < 4.2.0
		ob_start();
		highlight_string($tmp_source);
		$html_source = ob_get_contents();
		ob_end_clean();
		
		$html_source = '<div align="left"><hr /><h3>Source Dump:</h3>'
						.$html_source;
		$html_source .= '<hr /></div><div><h3>Component:</h3>';
		
		// Have to violate the API so highlighted source
		// is placed before real code, in case of PHP parse errors
		$code->code = substr($code->code,0,$this->startPos);
		if ( $this->startMode == 'CODE_WRITER_MODE_PHP' ) {
			$code->code .= '?>';
			$code->code .= $html_source;
			$code->code .= '<?php';
			$code->code .= $source;

		} else {
			$code->code .= $html_source;
			$code->code .= $source;
		}
		
		if ( $code->mode == 'CODE_WRITER_MODE_PHP' ) {
			$code->code .= '?><br /><hr /></div><?php';
		} else {
			$code->code .=	'<br /><hr /></div>';
		}
		parent::postGenerate($code);
	}
}
?>