<?php
//--------------------------------------------------------------------------------
// Copyright 2004 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: select_time.tag.php,v 1.11 2004/11/21 02:42:30 jeffmoore Exp $
*/

/**
* Includes
*/
require_once WACT_ROOT . 'template/tags/form/select.tag.php';
require_once WACT_ROOT . 'template/tags/core/block.tag.php';

/**
* Register the tags
*/
$taginfo =& new TagInfo('form:selecttime', 'SelectTimeTag');
$taginfo->setCompilerAttributes(array('errorclass', 'errorstyle', 'displayname'));
$taginfo->setKnownParent('FormTag');
TagDictionary::registerTag($taginfo, __FILE__);

$taginfo =& new TagInfo('form:selecthour', 'SelectHourTag');
$taginfo->setCompilerAttributes(array('errorclass', 'errorstyle', 'displayname'));
$taginfo->setKnownParent('FormTag');
TagDictionary::registerTag($taginfo, __FILE__);

$taginfo =& new TagInfo('form:selectminute', 'SelectMinuteTag');
$taginfo->setCompilerAttributes(array('errorclass', 'errorstyle', 'displayname'));
$taginfo->setKnownParent('FormTag');
TagDictionary::registerTag($taginfo, __FILE__);

$taginfo =& new TagInfo('form:selectsecond', 'SelectSecondTag');
$taginfo->setCompilerAttributes(array('errorclass', 'errorstyle', 'displayname'));
$taginfo->setKnownParent('FormTag');
TagDictionary::registerTag($taginfo, __FILE__);

/**
 * Compile time component for building runtime select time components
 * @see http://wact.sourceforge.net/index.php/SelectTimeTag
 * @access protected
 * @package WACT_TAG
 */
class SelectHourTag extends SelectTag
{
    /**
	 * File to include at runtime
	 * @var string path to runtime component relative to WACT_ROOT
	 * @access private
	 */
	var $runtimeIncludeFile = 'template/components/form/select_time.inc.php';

	/**
	 * Name of runtime component class
	 * @var string
	 * @access private
	 */
	var $runtimeComponentName = 'FormSelectTimeComponent';

    /**
     * @var object
     * @access protected
	 */
    var $SelectHourObjectRefCode;

    /**
     * @var object
     * @access protected
	 */
    var $selComponent;

    /**
	 * @param CodeWriter
	 * @return void
 	 * @access protected
	 */
	function preGenerate(&$code) {
		if ($this->hasAttribute('name')) {
		    $this->removeAttribute('name');
		}
		$code->writeHTML('<select name="');
		$code->writePHP('echo '.$this->selComponent.'->groupName;');
		$code->writePHP('if ('.$this->selComponent.'->asArray)');
		$code->writePHP('{ echo "[Hour]"; } else { echo "_Hour"; }');
		$code->writeHTML('"');
		$this->generateAttributeList($code, array('name', 'groupName', 'asArray'));
		$code->writeHTML('>');
	}

	/**
	 * @param CodeWriter
	 * @return void
 	 * @access protected
	 */
	function postGenerate(&$code) {
		$code->writeHTML('</select>');
	}

    /**
	 * @param CodeWriter
	 * @return void
	 * @access protected
	 */
	function generateContents(&$code) {
		$code->writePHP('$'.$this->SelectHourObjectRefCode.'->renderContents();');
	}
}

//--------------------------------------------------------------------------------

/**
 * Compile time component for building runtime select time components
 * @see http://wact.sourceforge.net/index.php/SelectTimeTag
 * @access protected
 * @package WACT_TAG
 */
class SelectMinuteTag extends SelectTag
{
    /**
	 * File to include at runtime
	 * @var string path to runtime component relative to WACT_ROOT
	 * @access private
	 */
	var $runtimeIncludeFile = 'template/components/form/select_time.inc.php';

	/**
	 * Name of runtime component class
	 * @var string
	 * @access private
	 */
	var $runtimeComponentName = 'FormSelectTimeComponent';

    /**
     * @var object
     * @access protected
	 */
    var $SelectMinuteObjectRefCode;

    /**
     * @var object
     * @access protected
	 */
    var $selComponent;

    /**
	 * @param CodeWriter
	 * @return void
 	 * @access protected
	 */
	function preGenerate(&$code)
	{
		//$code->writePHP($this->getComponentRefCode() . '->prepare();');

		if ($this->hasAttribute('name')) {
		    $this->removeAttribute('name');
		}

		$code->writeHTML('<select name="');
		$code->writePHP('echo '.$this->selComponent.'->groupName;');
		$code->writePHP('if ('.$this->selComponent.'->asArray)');
		$code->writePHP('{ echo "[Minute]"; } else { echo "_Minute"; }');
		$code->writeHTML('"');
		$this->generateAttributeList($code, array('name', 'groupName', 'asArray'));
		$code->writeHTML('>');
	}

	/**
	 * @param CodeWriter
	 * @return void
 	 * @access protected
	 */
	function postGenerate(&$code) {
		$code->writeHTML('</select>');
	}

    /**
	 * @param CodeWriter
	 * @return void
	 * @access protected
	 */
	function generateContents(&$code) {
		$code->writePHP('$'.$this->SelectMinuteObjectRefCode.'->renderContents();');
	}
}

//--------------------------------------------------------------------------------

/**
 * Compile time component for building runtime select time components
 * @see http://wact.sourceforge.net/index.php/SelectTimeTag
 * @access protected
 * @package WACT_TAG
 */
class SelectSecondTag extends SelectTag
{
    /**
	 * File to include at runtime
	 * @var string path to runtime component relative to WACT_ROOT
	 * @access private
	 */
	var $runtimeIncludeFile = 'template/components/form/select_time.inc.php';

	/**
	 * Name of runtime component class
	 * @var string
	 * @access private
	 */
	var $runtimeComponentName = 'FormSelectTimeComponent';

    /**
     * @var object
     * @access protected
	 */
    var $SelectSecondObjectRefCode;

    /**
     * @var object
     * @access protected
	 */
    var $selComponent;

    /**
	 * @param CodeWriter
	 * @return void
 	 * @access protected
	 */
	function preGenerate(&$code)
	{
		//discard
		if ($this->hasAttribute('name')) {
		    $this->removeAttribute('name');
		}
		$code->writeHTML('<select name="');
		$code->writePHP('echo '.$this->selComponent.'->groupName;');
		$code->writePHP('if ('.$this->selComponent.'->asArray)');
		$code->writePHP('{ echo "[Second]"; } else { echo "_Second"; }');
		$code->writeHTML('"');
		$this->generateAttributeList($code, array('name', 'groupName'));
		$code->writeHTML('>');
	}

	/**
	 * @param CodeWriter
	 * @return void
 	 * @access protected
	 */
	function postGenerate(&$code) {
		$code->writeHTML('</select>');
	}

    /**
	 * @param CodeWriter
	 * @return void
	 * @access protected
	 */
	function generateContents(&$code) {
        $code->writePHP('$'.$this->SelectSecondObjectRefCode.'->renderContents();');
	}
}

//--------------------------------------------------------------------------------

/**
 * Compile time component for building runtime select time components
 * @see http://wact.sourceforge.net/index.php/SelectTimeTag
 * @access protected
 * @package WACT_TAG
 */
class SelectTimeTag extends CoreBlockTag  //ControlTag
{
	/**
 	 * File to include at runtime
	 * @var string path to runtime component relative to WACT_ROOT
	 * @access private
	 */
    var $runtimeIncludeFile = 'template/components/form/select_time.inc.php';

	/**
	 * Name of runtime component class
	 * @var string
	 * @access private
	 */
	var $runtimeComponentName = 'FormSelectTimeComponent';

    /**
	 * @param CodeWriter
	 * @return void
     * @access protected
	 */
    function generateHour(&$code)
    {
        $SelectHourObjectRefCode  = getNewServerId();
        $SelectHour = & $this->findChildByClass('SelectHourTag');
        $SelectHour->selComponent = $this->getComponentRefCode();
		$SelectHour->SelectHourObjectRefCode = $SelectHourObjectRefCode;

		$SelectHour->setAttribute('groupName', $this->getAttribute('name'));

		$code->writePHP($this->getComponentRefCode() . '->prepareHour();');

		$code->writePHP('$'. $SelectHourObjectRefCode . '='.
		    $this->getComponentRefCode() . '->getHour();');

        $SelectHour->preGenerate($code);
		$SelectHour->generateContents($code);
		$SelectHour->postGenerate($code);
    }

    /**
	 * @param CodeWriter
	 * @return void
     * @access protected
	 */
    function generateMinute(&$code)
    {
        $SelectMinuteObjectRefCode = getNewServerId();
		$SelectMinute = & $this->findChildByClass('SelectMinuteTag');
        $SelectMinute->selComponent = $this->getComponentRefCode();
		$SelectMinute->SelectMinuteObjectRefCode = $SelectMinuteObjectRefCode;

		$SelectMinute->setAttribute('groupName', $this->getAttribute('name'));

		$code->writePHP($this->getComponentRefCode() . '->prepareMinute();');
		$code->writePHP('$'. $SelectMinuteObjectRefCode . '='.
		    $this->getComponentRefCode() . '->getMinute();');

        $SelectMinute->preGenerate($code);
		$SelectMinute->generateContents($code);
		$SelectMinute->postGenerate($code);
    }

    /**
	 * @param CodeWriter
	 * @return void
     * @access protected
	 */
    function generateSecond(&$code)
    {
        $SelectSecondObjectRefCode = getNewServerId();
        $SelectSecond = & $this->findChildByClass('SelectSecondTag');
        $SelectSecond->selComponent = $this->getComponentRefCode();
        $SelectSecond->SelectSecondObjectRefCode = $SelectSecondObjectRefCode;

        $SelectSecond->setAttribute('groupName', $this->getAttribute('name'));

        $code->writePHP($this->getComponentRefCode() . '->prepareSecond();');

		$code->writePHP('$'. $SelectSecondObjectRefCode . '='.
		    $this->getComponentRefCode() . '->getSecond();');

        $SelectSecond->preGenerate($code);
		$SelectSecond->generateContents($code);
		$SelectSecond->postGenerate($code);
    }

	/**
	 * @param CodeWriter
	 * @return void
	 * @access protected
	 */
	function generateContents(&$code)
	{
		$functionMap = array(
		    'selecthourtag'   => 'generateHour',
		    'selectminutetag' => 'generateMinute',
		    'selectsecondtag' => 'generateSecond'
		);

		$code->writePHP($this->getComponentRefCode() . '->setGroupName("'.$this->getAttribute('name').'");');
		$code->writePHP($this->getComponentRefCode() . '->setAsArray();');

        foreach ($this->children as $key => $child) {
            $childClass = strtolower(get_class($child));
            if (in_array($childClass, array_keys($functionMap))) {
                $this->$functionMap[$childClass]($code);
            } else {
                $this->children[$key]->generate($code);
            }
        }
    }
}
?>
