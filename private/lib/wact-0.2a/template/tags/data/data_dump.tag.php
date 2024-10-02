<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: data_dump.tag.php,v 1.5 2004/11/18 04:22:48 jeffmoore Exp $
*/

/**
* Register tag
*/
TagDictionary::registerTag(new TagInfo('data:DUMP', 'DataDumpTag'), __FILE__);

/**
* The compile time component to dump a result set
* @author Jason E. Sweat
* @see http://wact.sourceforge.net/index.php/ResultsetDumpTag
* @access protected
* @package WACT_TAG
*/
class DataDumpTag extends ServerComponentTag {
	/**
	* File to include at runtime
	* @var string path to runtime component relative to WACT_ROOT
	* @access private
	*/
	var $runtimeIncludeFile = 'template/components/list/list.inc.php';
	//var $runtimeIncludeFile = 'template/components/resultset_dump.inc.php';
	/**
	* Name of runtime component class
	* @var string
	* @access private
	*/
	var $runtimeComponentName = 'ListComponent';
	//var $runtimeComponentName = 'ResultsetDumpComponent';

	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function preGenerate(&$code) {
			parent::preGenerate($code);
			$code->writePHP($this->getComponentRefCode() . '->prepare();');
	}

	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateContents(&$code) {
		$code->writeHTML('<pre style="text-align:left">');
		$code->writePHP('echo get_class('.$this->getComponentRefCode().'->DataSet), "\n";');
		$code->writePHP('if (is_a('.$this->getComponentRefCode().'->DataSet, "arraydataset")) {');
		$code->writePHP('var_dump('.$this->getComponentRefCode().'->DataSet->exportDataSetAsArray());');
		$code->writePHP('} elseif (is_a('.$this->getComponentRefCode().'->DataSet, "AdodbRecordSet")) {');
		$code->writePHP('var_dump('.$this->getComponentRefCode().'->DataSet->QueryId->GetArray());');
		$code->writePHP('} else { var_dump('.$this->getComponentRefCode().'->DataSet);}');
		$code->writeHTML('</pre>');
	}

	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function postGenerate(&$code) {
	}

}


#?>
