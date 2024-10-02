<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_COMPONENT
* @version $Id: outputcache.inc.php,v 1.6 2004/11/20 18:09:48 jeffmoore Exp $
*/
/**
* Include PEAR::Cache_Lite
*/
if ( !defined('PEAR_LIBRARY_PATH') ) {
    define('PEAR_LIBRARY_PATH', ConfigManager::getOptionAsPath('config', 'pear', 'library_path'));
}
if (!@include_once PEAR_LIBRARY_PATH . 'Cache/Lite.php') {
    if (!@include_once WACT_ROOT . PEAR_LIBRARY_PATH . 'Cache/Lite.php') {
        RaiseError('runtime', 'LIBRARY_REQUIRED', array(
            'library' => 'PEAR::Cache_Lite',
            'path' => PEAR_LIBRARY_PATH));
    }
}

//--------------------------------------------------------------------------------
/**
* The block tag can be used to show or hide the contents of the block.
* The BlockComponent provides an API which allows the block to be shown
* or hidden at runtime.
* @see http://wact.sourceforge.net/index.php/OutputCacheComponent
* @see http://pear.php.net/Cache_Lite
* @access public
* @package WACT_COMPONENT
*/
class OutputCacheComponent extends DataSourceComponent {
	/**
	* Whether caching is on or off
	* @var int 0 or 1 for enabled / disabled caching
	* @access private
	*/
	var $caching;
	/**
	* Instance of PEAR::Cache_Lite
	* @var Cache_Lite
	* @access private
	*/
	var $cache;
	/**
	* Name of compiled template file
	* @var string
	* @access private
	*/
	var $codefile;
	/**
	* Name of a DataSource variable which defines seperate cacheable content
	* such as the contents of $_GET['page']
	* @var string
	* @access private
	*/
	var $cacheby='';
	/**
	* A group by which to identify to the file
	* @var string
	* @access private
	*/
	var $cachegroup=false;
	/**
	* Rendered HTML stored here
	* @var mixed
	* @access private
	*/
	var $output = '';
	/**
	* Store name of cache directory for error reporting
	* @var string
	* @access private
	*/
	var $cacheDir = '';
	/**
	* Constructs the OutputCacheComponent
	* @param string name of compiled template file
	* @param int number of seconds after which cache file expires
	* @param string DataSource variable name defining seperate cacheable content
	* @param string cache group - identifies a group of cache files
	* @access public
	*/
	function OutputCacheComponent($codefile,$expires=3600,$cacheby='',$cachegroup=false) {
		$this->codefile = $codefile;
		$tmp_options = ConfigManager::getSection('config','output_cache');
		$options = array();
		if ( isset ($tmp_options['caching']) && $tmp_options['caching'] == 0 ) {
			$options['caching'] = false;
			$this->caching = $tmp_options['caching'];
		} else {
			$options['caching'] = true;
			$this->caching = 1;
		}
		$options['lifeTime'] = $expires;
		if ( isset ($tmp_options['cacheBase']) && isset ($tmp_options['cacheDir']) ) {
			$options['cacheDir'] = $tmp_options['cacheBase'].$tmp_options['cacheDir'];
		} else {
			RaiseError('runtime', 'CACHE_LOCATION', array());
		}
		$this->cacheDir = $options['cacheDir'];
		$availableOptions = '{fileNameProtection}{memoryCaching}{onlyMemoryCaching}{memoryCachingLimit}{fileLocking}{writeControl}{readControl}{readControlType}{pearErrorMode}';
		foreach ($tmp_options as $key => $value ) {
			if (strpos('>'.$availableOptions, '{'.$key.'}')) {
				$options[$key] = $value;
			}
		}
		$this->cache =& new Cache_Lite($options);
		$this->cacheby = $cacheby;
		$this->cachegroup = $cachegroup;
	}
	/**
	* Returns the ID used by Cache_Lite to identify the cache file
	* @return void
	* @access public
	*/
	function getCacheId() {
		if ( $this->get($this->cacheby) ) {
			return $this->codefile.$this->get($this->cacheby);
		} else {
			return $this->codefile;
		}
	}
	/**
	* Returns the name of the cache group
	* @param string
	* @return void
	* @access public
	*/
	function getCacheGroup() {
		if ( $this->get($this->cachegroup) ) {
			return $this->get($this->cachegroup);
		} else {
			return 'default';
		}
	}
	/**
	* Returns the filename name of the cache file.
	* It's potentially "dangerous" as it has to access private parts of
	* PEAR::Cache_Lite
	* @param string
	* @return void
	* @access private
	*/
	function getCacheFileName() {
		$this->cache->_setFileName($this->getCacheId(),$this->getCacheGroup());
		return $this->cache->_file;
	}
	/**
	* Determine whether template is cached
	* @return boolean true means template is cached
	* @access public
	*/
	function isCached() {
		if ( $this->caching == 1 ) {
			if ( $this->output = $this->cache->get($this->getCacheId(),$this->getCacheGroup()) ) {
				return true;
			}
		}
		return false;
	}
	/**
	* Cache output for this template
	* @param string parsed template output
	* @return void
	* @access protected
	*/
	function cache($output) {
		$this->output = $output;
		if ( !$this->cache->save($output,$this->getCacheId(),$this->getCacheGroup()) && $this->caching == 1 ) {
			RaiseError('runtime', 'CACHE_WRITE', array('cacheDir' => realpath($this->cacheDir)));
		}
	}
	/**
	* Delete this cache file
	* @return void
	* @access public
	*/
	function flush() {
		$this->cache->remove($this->getCacheId(),$this->getCacheGroup());
	}
	/**
	* Flush all the cache files in this group.
	* @param mixed (optional) group name as string or nothing
	* @return void
	* @access public
	*/
	function flushGroup() {
		$this->cache->clean($this->getCacheGroup());
	}
	/**
	* Returns the time the cache was last modified to help with
	* issueing HTTP Client Side Caching headers<br />
	* Note: accesses PEAR::Cache_Lite private variable $_file
	* @return int
	* @access public
	*/
	function lastModified() {
		$file = $this->getCacheFileName();
		if ( file_exists($file) ) {
			return filemtime($file);
		} else {
			return time();
		}
	}
	/**
	* Returns the output to be displayed
	* @return mixed either string or false is template not parsed or cached
	* @access public
	*/
	function render() {
		echo $this->output;
	}
}
?>
