<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: css.tag.php,v 1.4 2004/11/18 04:22:47 jeffmoore Exp $
*/

/**
* Register the tag
*/
$taginfo =& new TagInfo('core:CSS', 'CoreCssTag');
$taginfo->setEndTag(ENDTAG_FORBIDDEN);
TagDictionary::registerTag($taginfo, __FILE__);

/**
* Acts as a placeholder for writing CSS. Allows CSS to be
* written both at compile time and runtime. Normally this tag would be
* placed inside the head tag of an HTML page but for other markups, e.g.
* XUL, it may appear otherwise. It is the responsibility of the template
* designer to place this tag correctly.
* When writing complile time CSS to it, add the CSS using
* the components preParse() method. Within preParse use findParentByClass
* to get the root of the component tree then findChildByClass to find
* the CoreCssTag.
* @see http://wact.sourceforge.net/index.php/CoreCssTag
* @access protected
* @package WACT_TAG
*/
class CoreCssTag extends ServerComponentTag {
	/**
	* File to include at runtime
	* @var string path to runtime component relative to WACT_ROOT
	* @access private
	*/
	var $runtimeIncludeFile = 'template/components/core/css.inc.php';
	/**
	* Name of runtime component class
	* @var string
	* @access private
	*/
	var $runtimeComponentName = 'CoreCssComponent';

	/**
	* Compile time CSS to write to template
	* @var string
	* @access private
	*/
	var $css = '';

	/**
	* Write some CSS into the container
	* @param string CSS to write to compiled template
	* @return void
	* @access public
	*/
	function writeCss($css) {
		$this->css.=$css;
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateContents(&$code) {
		parent::generateContents($code);
		$code->writeHTML('<style type="text/css"');
		$medias = array (
			'screen','tty','tv','projection','handheld',
			'print','braille','aural','all'
		);
		$media = $this->getAttribute('media');
		if ( $media && in_array($media,$medias) ) {
			$code->writeHTML(' media="'.$media.'"');
		}
		$code->writeHTML('>');

		if ( $escapestyle = $this->getAttribute('escapestyle') ) {
			$escapestyles = array('comment','cdata','both','none');
			$escapestyle = strtolower($escapestyle);
			if ( !in_array($escapestyle,$escapestyles) ) {
				$escapestyle = 'comment';
			}
		} else {
			$escapestyle = 'comment';
		}
		switch ( $escapestyle ) {
			case 'none':
			break;
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

		$code->writeHTML($this->css);
		$code->writePHP('echo '.$this->getComponentRefCode() . '->readCSS();');

		switch ( $escapestyle ) {
			case 'none':
			break;
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

		$code->writeHTML('</style>');
	}
}
?>