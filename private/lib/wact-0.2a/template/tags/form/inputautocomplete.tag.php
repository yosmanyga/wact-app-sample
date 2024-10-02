<?php
//--------------------------------------------------------------------------------
// Copyright 2004 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
 * @package WACT_TAG
 * @version $Id: inputautocomplete.tag.php,v 1.13 2004/11/18 04:22:48 jeffmoore Exp $
 */
//--------------------------------------------------------------------------------
/**
 * Includes
 */
require_once WACT_ROOT . 'template/tags/form/input.tag.php';

/**
* Register the tags
*/
$taginfo =& new TagInfo('form:INPUTAUTOCOMPLETE', 'InputAutocompleteTag');
$taginfo->setEndTag(ENDTAG_FORBIDDEN);
$taginfo->setCompilerAttributes(array('errorclass', 'errorstyle', 'displayname'));
$taginfo->setKnownParent('FormTag');
TagDictionary::registerTag($taginfo, __FILE__);

/**
* EXPERIMATAL!
* Allows text input tags with support for autocompletion (using JavaScript)
* @see http://wact.sourceforge.net/index.php/InputAutoCompleteTag
* @todo i18n for message displayed to browsers that dont support this JavaScript
* @todo Check it works with stuff like wrap tags
* @access protected
* @package WACT_TAG
*/
class InputAutoCompleteTag extends ControlTag {
	/**
	 * File to include at runtime
	 * @var string path to runtime component relative to WACT_ROOT
	 * @access private
	 */
	var $runtimeIncludeFile = 'template/components/form/inputautocomplete.inc.php';

	/**
	 * Name of runtime component class
	 * @var string
	 * @access private
	 */
	var $runtimeComponentName = 'InputAutoCompleteComponent';

	/**
	* Stores the name of a JavaScript variable reference
	* @var string
	* @access private
	*/
	var $javascriptvarref;

	function preParse() {
	static $added=false;
	if (!$added) {
		$javascript = <<<EOD
function wactjavascript_autoComplete (dataArray, input, evt) {
  if (input.value.length == 0) {
    return;
  }
  //allow backspace to work in IE
  if (typeof input.selectionStart == 'undefined' && evt.keyCode == 8) { input.value = input.value.substr(0,input.value.length-1); }
  var match = false;
  for (var i = 0; i < dataArray.length; i++) {
    if ((match = dataArray[i].toLowerCase().indexOf(input.value.toLowerCase()) == 0)) {
      break;
    }
  }
  if (match) {
    var typedText = input.value;
    if (typeof input.selectionStart != 'undefined') {
      switch (evt.keyCode) {
       case 37: //left arrow
       case 39: //right arrow
       case 33: //page up  
       case 34: //page down  
       case 36: //home  
       case 35: //end
       case 13: //enter
       case 9: //tab
       case 27: //esc
       case 16: //shift  
       case 17: //ctrl  
       case 18: //alt  
       case 20: //caps lock
       case 8: //backspace  
       case 46: //delete 
        return;
       case 38: //up arrow 
       	if (i > 0) { input.value = dataArray[i-1]; }
       	return; 
       case 40: //down arrow
       	if (i < dataArray.length - 1) { input.value = dataArray[i+1]; }
       	return; 
       break;
      }
      input.value = dataArray[i];
      input.setSelectionRange(typedText.length, input.value.length);
    }
    else if (input.createTextRange) {
      if (evt.keyCode == 16) {
        return;
      }
      if (evt.keyCode == 38) {
      	if (i > 0) { input.value = dataArray[i-1]; return; }
      }
      if (evt.keyCode == 40) {
      	if (i < dataArray.length - 1) { input.value = dataArray[i+1]; return; }
      }
      input.value = dataArray[i];
      var range = input.createTextRange();
      range.moveStart('character', typedText.length);
      range.moveEnd('character', input.value.length);
      range.select();
    }
    else {
      if (confirm("Are you looking for '" + dataArray[i] + "'?")) {
        input.value = dataArray[i];
      }
    }
  }
}
EOD;
		$ComponentTree = & $this->findParentByClass('ComponentTree') ;
		if ( !$ComponentTree ) {
			RaiseError(
				'compiler',
				'COMPONENTNOTFOUND',
				array(
					'ServerId' => 'ComponentTree',
					'file' => $this->SourceFile
				)
			);
		}
		$JContainer = & $ComponentTree->findChildByClass('CoreScriptTag');
		if ( !$JContainer ) {
			RaiseError(
				'compiler',
				'COMPONENTNOTFOUND',
				array(
					'ServerId' => 'CoreScriptTag',
					'file' => $this->SourceFile
				)
			);
		}
		$JContainer->writeJavaScript($javascript);
		$added=true;
		} 
		return PARSER_REQUIRE_PARSING;
	}

	/**
	* @return void
	* @access protected
	*/
	function preGenerate() {
		$this->tag = 'input';
		$this->javascriptvarref = 'wactjavascript_'.$this->getServerId().'_choices';
	}

	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateConstructor(&$code) {
	
		$attrValue = 'wactjavascript_autoComplete('.$this->javascriptvarref.',this,event)';
		$this->setAttribute('type','text');
		$this->setAttribute('onkeyup',$attrValue);
		$this->setAttribute('autocompletion','off');
		parent::generateConstructor($code);
	}

	/**
	 * @param CodeWriter
	 * @return void
 	 * @access protected
	 */
	function generateContents(&$code)
	{
		$code->writeHTML('<script type="text/javascript">');

		if ( $escapestyle = $this->getAttribute('escapestyle') ) {
			$escapestyles = array('comment','cdata','both');
			$escapestyle = strtolower($escapestyle);
			if ( !in_array($escapestyle,$escapestyles) ) {
				$escapestyle = 'comment';
			}
		} else {
			$escapestyle = 'comment';
		}
		switch ( $escapestyle ) {
			case 'both':
				$code->writeHTML("<!--//<![CDATA[\n");
			break;
			case 'cdata':
				$code->writeHTML("<![CDATA[\n");
			break;
			default:
				$code->writeHTML("<!--\n");
			break;
		}

		$code->writeHTML('var '.$this->javascriptvarref.' = [');
		$listVar = $code->getTempVariable();
		$itemVar = $code->getTempVariable();
		$code->writePHP('$'.$listVar.'='.$this->getComponentRefCode().'->getAutocompleteList();');
		$code->writePHP('foreach ($'.$listVar.' as $'.$itemVar.') {');
		$code->writePHP('echo "\"$'.$itemVar.'\",";');
		$code->writePHP('}');
		$code->writeHTML('];');
		
		switch ( $escapestyle ) {
			case 'both':
				$code->writeHTML("\n//]]>-->\n");
			break;
			case 'cdata':
				$code->writeHTML("\n]]>\n");
			break;
			default:
				$code->writeHTML("\n//-->\n");
			break;
		}

		$code->writeHTML('</script>');
		parent::preGenerate($code);
		parent::generateContents($code);
	}
}
?>