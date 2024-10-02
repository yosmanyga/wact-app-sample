<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: control.inc.php,v 1.6 2004/06/16 12:15:59 harryf Exp $
* @see http://www.w3.org/TR/html4/interact/forms.html
*/
//--------------------------------------------------------------------------------

/**
* Ancester tag class for input controls
* @access protected
* @package WACT_TAG
*/
class ControlTag extends ServerTagComponentTag {

	/**
	* Returns the identifying server ID. It's value it determined in the
	* following order;
	* <ol>
	* <li>The XML id attribute in the template if it exists</li>
	* <li>The XML name attribute in the template if it exists</li>
	* <li>The value of $this->ServerId</li>
	* <li>An ID generated by the getNewServerId() function</li>
	* </ol>
	* @see getNewServerId
	* @return string value identifying this component
	* @access protected
	*/
	function getServerId() {
		if ($this->hasAttribute('id')) {
			return $this->getAttribute('id');
		} else if ($this->hasAttribute('name')) {
			return str_replace('[]', '', $this->getAttribute('name'));
		} else if (!empty($this->ServerId)) {
			return $this->ServerId;
		} else {
			$this->ServerId = getNewServerId();
			return $this->ServerId;
		}
	}

	/**
	* @return void
	* @access protected
	*/
	function CheckNestingLevel() {
	    if ($this->findParentByClass(get_class($this))) {
            RaiseError('compiler', 'BADSELFNESTING', array(
                'tag' => $this->tag,
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
	    }
		if (!$this->findParentByClass('FormTag')) {
            RaiseError('compiler', 'MISSINGENCLOSURE', array(
                'tag' => $this->tag,
                'EnclosingTag' => 'form',
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
		}
	}

	/**
	* @return void
	* @access protected
	*/
	function prepare() {

		if (!$this->getBoolAttribute('name')) {
			if ( $this->getBoolAttribute('id') ) {
				$this->setAttribute('name',$this->getAttribute('id'));
			} else {
				RaiseError('compiler', 'NAMEREQUIRED', array(
					'tag' => $this->tag,
					'file' => $this->SourceFile,
					'line' => $this->StartingLineNo));
			}
		}

		parent::prepare();
	}

	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateConstructor(&$code) {
		parent::generateConstructor($code);
		if ($this->hasAttribute('errorclass')) {
			$code->writePHP($this->getComponentRefCode() . '->errorclass = ');
		    $code->writePHPLiteral($this->getAttribute('errorclass'));
		    $code->writePHP(';');
		}
		if ($this->hasAttribute('errorstyle')) {
			$code->writePHP($this->getComponentRefCode() . '->errorstyle = ');
			$code->writePHPLiteral($this->getAttribute('errorstyle'));
			$code->writePHP(';');
		}
		if ($this->hasAttribute('displayname')) {
			$code->writePHP($this->getComponentRefCode() . '->displayname = ');
			$code->writePHPLiteral($this->getAttribute('displayname'));
			$code->writePHP(';');
		}
	}

}

?>