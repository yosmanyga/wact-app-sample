<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: script.tag.php,v 1.5 2004/11/18 04:22:47 jeffmoore Exp $
*/

/**
* Register the tag
*/
$taginfo =& new TagInfo('core:SCRIPT', 'CoreScriptTag');
$taginfo->setEndTag(ENDTAG_FORBIDDEN);
TagDictionary::registerTag($taginfo, __FILE__);

/**
* Acts as a placeholder for writing JavaScript. Allows JavaScript to be
* written both at compile time and runtime. Normally this tag would be
* placed inside the head tag of an HTML page but for other markups, e.g.
* XUL, it may appear otherwise. It is the responsibility of the template
* designer to place this tag correctly.
* When writing complile time Javascript to it, add the JavaScript using
* the components preParse() method. Within preParse use findParentByClass
* to get the root of the component tree then findChildByClass to find
* the CoreScriptTag.
* @see http://www.juicystudio.com/tutorial/javascript/embedding.asp
* @see http://wact.sourceforge.net/index.php/CoreScriptTag
* @see InputAutoCompleteTag
* @access protected
* @package WACT_TAG
*/
class CoreScriptTag extends ServerComponentTag {
	/**
	* File to include at runtime
	* @var string path to runtime component relative to WACT_ROOT
	* @access private
	*/
	var $runtimeIncludeFile = 'template/components/core/script.inc.php';
	/**
	* Name of runtime component class
	* @var string
	* @access private
	*/
	var $runtimeComponentName = 'CoreScriptComponent';

	/**
	* Compile time JavaScript to write to template
	* @var string
	* @access private
	*/
	var $javascript = '';

	/**
	* Write some JavaScript into the container
	* @param string JavaScript to write to compiled template
	* @return void
	* @access public
	*/
	function writeJavaScript($javascript) {
		$this->javascript.=$javascript;
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateContents(&$code) {
		parent::generateContents($code);
		if ( !$type = $this->getAttribute('type') ) {
			$type = 'text/javascript';
		}
		$code->writeHTML('<script type="'.$type.'">');
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
				$code->writeHTML("\n<!--//<![CDATA[\n");
			break;
			case 'cdata':
				$code->writeHTML("\n<![CDATA[\n");
			break;
			default:
				$code->writeHTML("\n<!--\n");
			break;
		}

		$code->writeHTML($this->javascript);
		$code->writePHP('echo '.$this->getComponentRefCode() . '->readJavaScript();');

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
	}
}
?>