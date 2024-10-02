<?php
//--------------------------------------------------------------------------------
// Copyright 2004 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: select_date.tag.php,v 1.11 2004/11/21 02:42:30 jeffmoore Exp $
*/

/**
* Includes
*/
require_once WACT_ROOT . 'template/tags/form/select.tag.php';
require_once WACT_ROOT . 'template/tags/core/block.tag.php';

/**
* Register the tags
*/
$taginfo =& new TagInfo('form:selectdate', 'SelectDateTag');
$taginfo->setCompilerAttributes(array('errorclass', 'errorstyle', 'displayname'));
$taginfo->setKnownParent('FormTag');
TagDictionary::registerTag($taginfo, __FILE__);

$taginfo =& new TagInfo('form:selectyear', 'SelectYearTag');
$taginfo->setCompilerAttributes(array('errorclass', 'errorstyle', 'displayname'));
$taginfo->setKnownParent('FormTag');
TagDictionary::registerTag($taginfo, __FILE__);

$taginfo =& new TagInfo('form:selectmonth', 'SelectMonthTag');
$taginfo->setCompilerAttributes(array('errorclass', 'errorstyle', 'displayname'));
$taginfo->setKnownParent('FormTag');
TagDictionary::registerTag($taginfo, __FILE__);

$taginfo =& new TagInfo('form:selectday', 'SelectDayTag');
$taginfo->setCompilerAttributes(array('errorclass', 'errorstyle', 'displayname'));
$taginfo->setKnownParent('FormTag');
TagDictionary::registerTag($taginfo, __FILE__);

/**
 * Compile time component for building runtime select date components
 * @see http://wact.sourceforge.net/index.php/SelectDateTag
 * @access protected
 * @package WACT_TAG
 */
class SelectYearTag extends SelectTag
{
    /**
	 * File to include at runtime
	 * @var string path to runtime component relative to WACT_ROOT
	 * @access private
	 */
	var $runtimeIncludeFile = 'template/components/form/select_date.inc.php';

	/**
	 * Name of runtime component class
	 * @var string
	 * @access private
	 */
	var $runtimeComponentName = 'FormSelectDateComponent';

    /**
     * @var object
     * @access protected
	 */
    var $selectYearObjectRefCode;

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
		$code->writePHP('{ echo "[Year]"; } else { echo "_Year"; }');
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
	function generateContents(&$code)
	{
		$code->writePHP('$'.$this->selectYearObjectRefCode.'->renderContents();');
	}
}

//--------------------------------------------------------------------------------

/**
 * Compile time component for building runtime select date components
 * @see http://wact.sourceforge.net/index.php/SelectDateTag
 * @access protected
 * @package WACT_TAG
 */
class SelectMonthTag extends SelectTag
{
    /**
	 * File to include at runtime
	 * @var string path to runtime component relative to WACT_ROOT
	 * @access private
	 */
	var $runtimeIncludeFile = 'template/components/form/select_date.inc.php';

	/**
	 * Name of runtime component class
	 * @var string
	 * @access private
	 */
	var $runtimeComponentName = 'FormSelectDateComponent';

    /**
     * @var object
     * @access protected
	 */
    var $selectMonthObjectRefCode;

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
		if ($this->hasAttribute('name')) {
		    $this->removeAttribute('name');
		}

		$code->writeHTML('<select name="');
		$code->writePHP('echo '.$this->selComponent.'->groupName;');
		$code->writePHP('if ('.$this->selComponent.'->asArray)');
		$code->writePHP('{ echo "[Month]"; } else { echo "_Month"; }');
		$code->writeHTML('"');
		$this->generateAttributeList($code, array('name', 'groupName', 'asArray', 'format'));
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
	function generateContents(&$code)
	{
		$format = ($this->hasAttribute('format') ? $this->getAttribute('format') : 'long');
        $code->writePHP('$'.$this->selectMonthObjectRefCode.'->setFormat("'.$format.'");');

        //$value_format = ($this->hasAttribute('value_format') ? $this->getAttribute('value_format') : 'numeric');
        //$code->writePHP('$'.$this->selectMonthObjectRefCode.'->setValueFormat("'.$value_format.'");');

        $code->writePHP('$'.$this->selectMonthObjectRefCode.'->fillChoices();');
        $code->writePHP('$'.$this->selectMonthObjectRefCode.'->renderContents();');
	}
}

//--------------------------------------------------------------------------------

/**
 * Compile time component for building runtime select date components
 * @see http://wact.sourceforge.net/index.php/SelectDateTag
 * @access protected
 * @package WACT_TAG
 */
class SelectDayTag extends SelectTag
{
    /**
	 * File to include at runtime
	 * @var string path to runtime component relative to WACT_ROOT
	 * @access private
	 */
	var $runtimeIncludeFile = 'template/components/form/select_date.inc.php';

	/**
	 * Name of runtime component class
	 * @var string
	 * @access private
	 */
	var $runtimeComponentName = 'FormSelectDateComponent';

    /**
     * @var object
     * @access protected
	 */
    var $selectDayObjectRefCode;

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
		$code->writePHP('{ echo "[Day]"; } else { echo "_Day"; }');
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
	function generateContents(&$code)
	{
        $code->writePHP('$'.$this->selectDayObjectRefCode.'->renderContents();');
	}
}

//--------------------------------------------------------------------------------

/**
 * Compile time component for building runtime select date components
 * @see http://wact.sourceforge.net/index.php/SelectDateTag
 * @access protected
 * @package WACT_TAG
 */
class SelectDateTag extends CoreBlockTag  //ControlTag
{
	/**
 	 * File to include at runtime
	 * @var string path to runtime component relative to WACT_ROOT
	 * @access private
	 */
    var $runtimeIncludeFile = 'template/components/form/select_date.inc.php';

	/**
	 * Name of runtime component class
	 * @var string
	 * @access private
	 */
	var $runtimeComponentName = 'FormSelectDateComponent';

    /**
     * @param CodeWriter
	 * @return void
	 * @access protected
	 */
    function generateYear(&$code)
    {
        $selectYearObjectRefCode  = getNewServerId();
        $SelectYear = & $this->findChildByClass('SelectYearTag');
        $SelectYear->selComponent = $this->getComponentRefCode();
		$SelectYear->selectYearObjectRefCode = $selectYearObjectRefCode;

		$SelectYear->setAttribute('groupName', $this->getAttribute('name'));

		$code->writePHP($this->getComponentRefCode() . '->prepareYear();');

		$code->writePHP('$'. $selectYearObjectRefCode . '='.
		    $this->getComponentRefCode() . '->getYear();');

        $SelectYear->preGenerate($code);
		$SelectYear->generateContents($code);
		$SelectYear->postGenerate($code);
    }

    /**
     * @param CodeWriter
	 * @return void
	 * @access protected
	 */
    function generateMonth(&$code)
    {
        $selectMonthObjectRefCode = getNewServerId();
		$SelectMonth = & $this->findChildByClass('SelectMonthTag');
        $SelectMonth->selComponent = $this->getComponentRefCode();
		$SelectMonth->selectMonthObjectRefCode = $selectMonthObjectRefCode;

		$SelectMonth->setAttribute('groupName', $this->getAttribute('name'));

		$code->writePHP($this->getComponentRefCode() . '->prepareMonth();');
		$code->writePHP('$'. $selectMonthObjectRefCode . '='.
		    $this->getComponentRefCode() . '->getMonth();');

        $SelectMonth->preGenerate($code);
		$SelectMonth->generateContents($code);
		$SelectMonth->postGenerate($code);
    }

    /**
     * @param CodeWriter
	 * @return void
	 * @access protected
	 */
    function generateDay(&$code)
    {
        $selectDayObjectRefCode = getNewServerId();
        $SelectDay = & $this->findChildByClass('SelectDayTag');
        $SelectDay->selComponent = $this->getComponentRefCode();
        $SelectDay->selectDayObjectRefCode = $selectDayObjectRefCode;

        $SelectDay->setAttribute('groupName', $this->getAttribute('name'));

        $code->writePHP($this->getComponentRefCode() . '->prepareDay();');

		$code->writePHP('$'. $selectDayObjectRefCode . '='.
		    $this->getComponentRefCode() . '->getDay();');

        $SelectDay->preGenerate($code);
		$SelectDay->generateContents($code);
		$SelectDay->postGenerate($code);
    }

	/**
	 * @param CodeWriter
	 * @return void
	 * @access protected
	 */
	function generateContents(&$code)
	{
		$functionMap = array(
		    'selectyeartag'  => 'generateYear',
		    'selectmonthtag' => 'generateMonth',
		    'selectdaytag'   => 'generateDay'
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
