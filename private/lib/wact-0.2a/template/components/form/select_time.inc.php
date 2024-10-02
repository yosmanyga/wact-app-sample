<?php
//--------------------------------------------------------------------------------
// Copyright 2004 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
 * @package WACT_COMPONENT
 * @version $Id: select_time.inc.php,v 1.3 2004/11/24 23:12:03 quipo Exp $
 */
//--------------------------------------------------------------------------------
/**
 * SelectSingleComponent definition
 */
require_once WACT_ROOT . 'template/components/form/form.inc.php';
require_once 'select.inc.php';

/**
 * Runtime form:selecttime API
 * @todo EXPERIMENTAL
 * @see http://wact.sourceforge.net/index.php/FormSelectTimeComponent
 * @access public
 * @package WACT_COMPONENT
 */
class FormSelectTimeComponent extends FormComponent
{
    var $selectHour   = null;
    var $selectMinute = null;
    var $selectSecond = null;
    var $selectedTime = array();

    var $setDefaultSelection = false;
    var $asArray = false;
    var $groupName;

    /**
     * the compiler complains if not defined...
     */
    function isVisible() {
        return true;
    }

	/**
	 * @param string 'name' attribute of the form:selecttime tag
	 */
	function setGroupName($name) {
	    $this->groupName = $name;
	}

    function setAsArray() {
        $this->asArray = $this->getAttribute('asArray');
    }

    /**
	 * @param mixed int|string unix timestamp or ISO-8601 timestamp
	 * @param CodeWriter
     * @return array
	 * @access private
	 */
	function parseTime($time=null)
	{
        if (is_integer($time)) {
            //$time = unix timestamp
            return array(
                'hour'   => date('H', $time),
		        'minute' => date('i', $time),
		        'second' => date('s', $time)
		    );
        }
        $len = (is_string($time)) ? strlen($time) : 0;
        if ($len == 14) {
            //$time = mysql timestamp YYYYMMDDHHMMSS
            return array(
		        'hour'   => (int)substr($time, 8, 2),
		        'minute' => (int)substr($time, 10, 2),
		        'second' => (int)substr($time, 12, 2),
		    );
        }

        if ($len == 19) {
            //$time = ISO-8601 timestamp YYYY-MM-DD HH:MM:SS
            return array(
		        'hour'   => (int)substr($time, 11, 2),
		        'minute' => (int)substr($time, 14, 2),
		        'second' => (int)substr($time, 17, 2),
		    );
        }
        //if everything failed, try with strtotime
        if (empty($time)) {
            $time = 'now';
        }
        $time = strtotime($time);
        if (!is_numeric($time) || $time == -1) {
            $time = strtotime('now');
        }
        return array(
		    'hour'   => date('H', $time),
		    'minute' => date('i', $time),
		    'second' => date('s', $time)
        );
	}

    /**
     * build SelectSimpleComponent object and set options for hours
     */
    function prepareHour()
    {
        $this->selectHour  = new SelectSingleComponent(); //SelectHour
        $this->addChild($this->selectHour);

        $use24hours = ($this->hasAttribute('use24hours') ? $this->getAttribute('use24hours') : true);

        $hours = array();
        $end = ($use24hours ? 24 : 12);
        for ($i=1; $i<=$end; $i++) {
            $hours[sprintf('%02d', $i)] = sprintf('%02d', $i);
        }
        $this->selectHour->setChoices($hours);
        if ($this->setDefaultSelection) {
            $this->selectHour->setSelection(($this->selectedTime['hour'] % $end));
        }

        //maintain selection through pages
        $FormComponent = &$this->findParentByClass('FormComponent');
        if ($h = $FormComponent->_getValue($this->groupName.'_Hour')) {
            $this->selectHour->setSelection($h);
        }
        if ($date = $FormComponent->_getValue($this->groupName)) {
            if (is_array($date) && array_key_exists('Hour', $date)) {
                $this->selectHour->setSelection($date['Hour']);
            } else {
                $this->selectedTime = $this->parseTime($date);
                $this->selectHour->setSelection($this->selectedTime['hour']);
            }
        }
    }

	/**
     * build SelectSimpleComponent object and set options for minutes
     */
    function prepareMinute()
    {

        $this->selectMinute = new SelectSingleComponent(); // new SelectMinute
        $this->addChild($this->selectMinute);

        $minutes = array();
        for ($i=1; $i<=60; $i++) {
            $minutes[sprintf('%02d', $i)] = sprintf('%02d', $i);
        }
        $this->selectMinute->setChoices($minutes);
        if ($this->setDefaultSelection) {
            $this->selectMinute->setSelection($this->selectedTime['minute']);
        }

        //maintain selection through pages
        $FormComponent = &$this->findParentByClass('FormComponent');
        if ($m = $FormComponent->_getValue($this->groupName.'_Minute')) {
            $this->selectMinute->setSelection($m);
        }
        if ($date = $FormComponent->_getValue($this->groupName)) {
            if (is_array($date) && array_key_exists('Minute', $date)) {
                $this->selectMinute->setSelection($date['Minute']);
            } else {
                $this->selectedTime = $this->parseTime($date);
                $this->selectMinute->setSelection($this->selectedTime['minute']);
            }
        }
    }

    /**
     * build SelectSimpleComponent object and set options for seconds
     */
    function prepareSecond()
    {
        $this->selectSecond = new SelectSingleComponent(); // new SelectSecond
        $this->addChild($this->selectSecond);

        $seconds = array();
        for ($i=1; $i<=60; $i++) {
            $seconds[sprintf('%02d', $i)] = sprintf('%02d', $i);
        }
        $this->selectSecond->setChoices($seconds);
        if ($this->setDefaultSelection) {
            $this->selectSecond->setSelection($this->selectedTime['second']);
        }

        //maintain selection through pages
        $FormComponent = &$this->findParentByClass('FormComponent');
        if ($s = $FormComponent->_getValue($this->groupName.'_Second')) {
            $this->selectSecond->setSelection($s);
        }
        if ($date = $FormComponent->_getValue($this->groupName)) {
            if (is_array($date) && array_key_exists('Second', $date)) {
                $this->selectSecond->setSelection($date['Second']);
            } else {
                $this->selectedTime = $this->parseTime($date);
                $this->selectSecond->setSelection($this->selectedTime['second']);
            }
        }
    }

    /**
     * override default behaviour when onInitial() is called
     */
    function setSelection($time=null) {
        if (is_null($time)) {
            $time = time();
        }
        $this->selectedTime = $this->parseTime($time);
        $this->setDefaultSelection = true;
    }

    /**
	 * @return SelectSingleComponent object
	 * @access protected
	 */
	function & getHour() {
		return $this->selectHour;
	}

	/**
	 * @return SelectSingleComponent object
	 * @access protected
	 */
	function & getMinute() {
		return $this->selectMinute;
	}

	/**
	 * @return SelectSingleComponent object
	 * @access protected
	 */
	function & getSecond() {
		return $this->selectSecond;
	}
}
?>
