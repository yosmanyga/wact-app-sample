<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: bbcode.tag.php,v 1.5 2004/11/18 04:22:49 jeffmoore Exp $
*/

/**
* Register the tag
*/
$taginfo =& new TagInfo('html:BBCODE', 'HtmlBBCodeTag');
$taginfo->setEndTag(ENDTAG_FORBIDDEN);
TagDictionary::registerTag($taginfo, __FILE__);

/**
* Parse the 
* @todo Provide mechanism for automatically pulling text from runtime DataSources
* @todo Attributes controlling htmlentity use and nl2br use
* @see http://wact.sourceforge.net/index.php/HtmlBBCodeTag
* @access protected
* @package WACT_TAG
*/
class HtmlBBCodeTag extends ServerComponentTag {
	/**
	* File to include at runtime
	* @var string path to runtime component relative to WACT_ROOT
	* @access private
	*/
	var $runtimeIncludeFile = '/template/components/html/bbcode.inc.php';
	/**
	* Name of runtime component class
	* @var string
	* @access private
	*/
	var $runtimeComponentName = 'HtmlBBCodeComponent';
	
	/**
	* Tag options - partly defined by PEAR::HTML_BBCodeParser plus
	* addition output control options. See wiki.
	* @var array
	* @access private
	*/
	var $options = array();

	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function preGenerate(&$code) {
		parent::preGenerate($code);
		if ( $quotestyle = $this->getAttribute('quotestyle') ) {
			$quotestyles = array('single','double');
			if ( in_array($quotestyle,$quotestyles) ) {
				$this->options['quotestyle']=$quotestyle;
			} else {
				$this->options['quotestyle'] = 'single';
			}
		} else {
			$this->options['quotestyle'] = 'single';
		}
		if ( $quotewhat = $this->getAttribute('quotewhat') ) {
			$quotewhats = array('all','none','strings');
			if ( in_array($quotewhat,$quotewhats) ) {
				$this->options['quotewhat']=$quotewhat;
			} else {
				$this->options['quotewhat'] = 'all';
			}
		} else {
			$this->options['quotewhat'] = 'all';
		}		
		$open = $this->getAttribute('open');
		if ( isset($open) && $open != '<' ) {
			$this->options['open']=$open;
		} else {
			$this->options['open']='[';
		}
		$close = $this->getAttribute('close');
		if ( isset($open) && $open != '>' ) {
			$this->options['close']=$close;
		} else {
			$this->options['close']=']';
		}
		if ( $this->getAttribute('xmlclose') ) {
			$this->options['xmlclose'] = $this->getBoolAttribute('xmlclose');
		} else {
			$this->options['xmlclose'] = TRUE;
		}
		if ( $filters = $this->getAttribute('filters') ) {
			$filters = explode(',',$filters);
			$filters = array_map('strtolower',$filters);
			$knownfilters = array('basic','extended','links',
								  'images','lists','email');
			$sep = '';
			foreach ( $filters as $filter ) {
				if (in_array($filter,$knownfilters)) {
					$this->options['filters'].=$sep.ucfirst($filter);
					$sep = ',';
				}
			}
		} else {
			$this->options['filters'] = 'Basic,Extended,Links,Images,Lists,Email';
		}
		if ( NULL !== ($wordwrap = $this->getAttribute('wordwrap')) ) {
			if ( $wordwrap == (int)$wordwrap && $wordwrap > 0 ) {
				$this->options['wordwrap'] = $wordwrap;
			} else {
				$this->options['wordwrap'] = FALSE;
			}
		} else {
			$this->options['wordwrap'] = FALSE;
		}		
		if ( $this->getAttribute('nl2br') ) {
			$this->options['nl2br'] = $this->getBoolAttribute('nl2br');
		} else {
			$this->options['nl2br'] = TRUE;
		}
		if ( $this->getAttribute('htmlentities') ) {
			$this->options['htmlentities'] = $this->getBoolAttribute('htmlentities');
		} else {
			$this->options['htmlentities'] = TRUE;
		}
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateContents(&$code) {
		parent::generateContents($code);
		$code->writePHP($this->getComponentRefCode().
			'->options=unserialize(\''.serialize($this->options).'\');');
		$code->writePHP('echo '.$this->getComponentRefCode().'->display();');
	}
}
?>