<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: outputcache.tag.php,v 1.4 2004/11/18 04:22:47 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* Register the tag
*/
$taginfo =& new TagInfo('cache:OUTPUTCACHE', 'CacheOutputCacheTag');
$taginfo->setCompilerAttributes(array('expires', 'cacheby', 'cachegroup'));
TagDictionary::registerTag($taginfo, __FILE__);

/**
* Compile time component for instructing output to be cached in a file
* at runtime.
* @see http://wact.sourceforge.net/index.php/CoreBlockTag
* @access protected
* @package WACT_TAG
*/
class CacheOutputCacheTag extends ServerComponentTag {
	/**
	* File to include at runtime
	* @var string path to runtime component relative to WACT_ROOT
	* @access private
	*/
	var $runtimeIncludeFile = '/template/components/cache/outputcache.inc.php';
	/**
	* Name of runtime component class
	* @var string
	* @access private
	*/
	var $runtimeComponentName = 'OutputCacheComponent';
	/**
	* Name of runtime variable reference where cached content is stored
	* @var string
	* @access private
	*/
	var $contentref;
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateConstructor(&$code) {
		$code->registerInclude(WACT_ROOT . $this->runtimeIncludeFile);
		$args = '__FILE__.\'' . $this->getServerId() . '\'';
        if ($this->hasAttribute('expires')) {
			$args .= ',' . $this->getAttribute('expires') . '';
	    } else {
	        $args .= ',3600';
	    }
        if ($this->hasAttribute('cacheby')) {
			$args .= ',\'' . $this->getAttribute('cacheby') . '\'';
	    } else {
	        $args .= ',\'\'';
	    }
        if ($this->hasAttribute('cachegroup')) {
			$args .= ',\'' . $this->getAttribute('cachegroup') . '\'';
		} else {
		    $args .= ',false';
        }
		$code->writePHP($this->parent->getComponentRefCode() .
			'->addChild(new ' . $this->runtimeComponentName . 
			'('.$args.'), \'' . $this->getServerId() . '\');');
		CompilerComponent::generateConstructor($code);
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function preGenerate(&$code) {
		$this->contentref = getNewServerId();
		parent::preGenerate($code);
		$code->writePHP('if (!'.$this->getComponentRefCode() . '->isCached()) {');
		$code->writePHP('ob_start();');
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function postGenerate(&$code) {
		$code->writePHP($this->getComponentRefCode().'->cache(ob_get_contents());ob_end_clean();');
		$code->writePHP('}');
		$code->writePHP($this->getComponentRefCode().'->render();');
		parent::postGenerate($code);
	}
}
?>
