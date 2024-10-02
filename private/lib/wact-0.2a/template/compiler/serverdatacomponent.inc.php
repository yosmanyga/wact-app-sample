<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TEMPLATE
* @version $Id: serverdatacomponent.inc.php,v 1.13 2004/11/12 21:25:06 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* include parent class
*/
require_once WACT_ROOT . 'template/compiler/servercomponent.inc.php';
/**
* Server tag component tags are ServerComponentTags which also correspond to
* an HTML tag. Makes it easier to implement instead of extending from the
* ServerComponentTag class
* @see http://wact.sourceforge.net/index.php/ServerTagComponentTag
* @access public
* @abstract
* @package WACT_TEMPLATE
*/
class ServerDataComponentTag extends ServerComponentTag {

    var $DataSourceReferenceVariable;

	/**
	* Calls the parent preGenerate() method then writes
	* "$DataSpace->prepare();" to the compiled template.
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function preGenerate(&$code) {
		parent::preGenerate($code);
		
		if ($this->hasAttribute('from')) {
            $code->writePHP($this->getComponentRefCode() . '->registerDataSource(');
		    $parent_datasource =& $this->getParentDataSource();
            $code->writePHP('Template::_dereference(' . $parent_datasource->getDataSourceRefCode() . ',');
            $code->writePHPLIteral($this->getAttribute('from'));
            $code->writePHP('));');
		}
		
        $code->writePHP($this->getComponentRefCode() . '->prepare();');

		$this->DataSourceReferenceVariable = $code->getTempVariable();
        $code->writePHP('$' . $this->DataSourceReferenceVariable . '=&' . $this->getComponentRefCode() . '->_datasource;');
	}

	/**
	* @return ListListTag this instance
	* @access protected
	*/
	function &getDataSource() {
		return $this;
	}

	/**
	* @return string PHP runtime variable reference to component
	* @access protected
	*/
	function getDataSourceRefCode() {
	    return '$' . $this->DataSourceReferenceVariable;
	}

	/**
	* @return Boolean Indicating whether or not this component is a DataSource
	*/
	function isDataSource() {
	    return TRUE;
	}

}
?>