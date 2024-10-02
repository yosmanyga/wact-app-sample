<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_COMPONENT
* @version $Id: calendar_month.inc.php,v 1.5 2004/11/20 18:09:48 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* Include PEAR::Calendar
*/

//CALENDAR_ROOT

if ( !defined('CALENDAR_ROOT') ) {
    define('CALENDAR_ROOT', ConfigManager::getOptionAsPath('config', 'pear', 'library_path') . 'Calendar/');
}
if (!@include_once CALENDAR_ROOT . 'Month/Weekdays.php') {
    RaiseError('runtime', 'LIBRARY_REQUIRED', array(
        'library' => 'PEAR::Calendar v0.4+',
        'path' => CALENDAR_ROOT));
}
require_once CALENDAR_ROOT . 'Day.php';
require_once CALENDAR_ROOT . 'Decorator/Textual.php';
require_once CALENDAR_ROOT . 'Decorator/Uri.php';
require_once CALENDAR_ROOT . 'Decorator/Wrapper.php';

require_once WACT_ROOT . 'template/components/html/html_base.inc.php';
//--------------------------------------------------------------------------------
/**
* Runtime calendar API
* @todo EXPERIMENTAL
* @see http://wact.sourceforge.net/index.php/CalendarMonthComponent
* @access public
* @package WACT_COMPONENT
*/
class CalendarMonthComponent extends HtmlBaseComponent {
	/**
	* Instance of PEAR::Calendar_Month_Weekdays
	* @var Calendar_Month_Weekdays
	* @access private
	*/
	var $Calendar;
	/**
	* Instance of PEAR::Calendar_Decorator_Textual
	* @var Calendar_Decorator_Textual
	* @access private
	*/
	var $Textual;
	/**
	* Instance of PEAR::Calendar_Decorator_Uri
	* @var Calendar_Decorator_Uri
	* @access private
	*/
	var $Uri;
	/**
	* Instance of PEAR::Calendar_Decorator_Wrapper
	* @var Calendar_Decorator_Wrapper
	* @access private
	*/
	var $Wrapper;
	
	var $baseUrl;
	/**
	* String for year GET variable
	* @var string
	* @access private
	*/
	var $yearUri = 'y';
	/**
	* String for month GET variable
	* @var string
	* @access private
	*/
	var $monthUri = 'm';
	/**
	* String for day GET variable
	* @var string
	* @access private
	*/
	var $dayUri = 'd';
	/**
	* Array of PEAR::Calendar_Day objects to be used in selection
	* @var array
	* @access private
	*/
	var $selection = array();
	
	function CalendarMonthComponent() {
        $this->baseUrl = $_SERVER['REQUEST_URI'];
        $pos = strpos($this->baseUrl, '?');
        if (is_integer($pos)) {
            $this->baseUrl = substr($this->baseUrl, 0, $pos);
        }
	}
	
	/**
	* Returns the URI string for the previous month
	* @return string
	* @access protected
	*/
	function prevLink() {
		return $this->getBaseUri().$this->Uri->prev('month');
	}
	/**
	* Returns the URI string for the next month
	* @return string
	* @access protected
	*/
	function nextLink() {
		return $this->getBaseUri().$this->Uri->next('month');
	}
	
	function dayLink($day) {
		return $this->getBaseUri().$this->Uri->this('day');
	}
	/**
	* Returns the year
	* @param string how to format: 'full', 'two' (digits) or 'hide'
	* @return string
	* @access protected
	*/
	function yearFormatted($format) {
		$formats = array('full','two','hide');
		if ( !in_array($format,$formats) ) {
			$format = 'full';
		}
		switch ( $format ) {
			case 'hide':
				return '';
			break;
			case 'two':
				return substr($this->Calendar->thisYear(),2);
			break;
			case 'full':
			case 'default':
				return $this->Calendar->thisYear();
			break;
		}
	}
	/**
	* Returns the month name formated
	* @param string how to format: 'long', 'short','two','one'
	* @return string
	* @access protected
	*/
	function monthName($format) {
		return $this->Textual->thisMonthName($format);
	}
	/**
	* Returns the headers for the days of the week
	* @param string how to format: 'long', 'short','two','one'
	* @return string
	* @access protected
	*/
	function dayHeaders($format) {
		return $this->Textual->orderedWeekdays($format);
	}
	/**
	* Sets a selection of PEAR::Calendar_Day objects
	* @param array
	* @return void
	* @access public
	*/
	function setSelection(& $selection) {
		$this->selection = & $selection;
	}
	/**
	* @return void
	* @access protected
	*/
	function prepare() {
		if ( !isset($_GET[$this->yearUri]) ) $_GET[$this->yearUri] = date('Y');
		if ( !isset($_GET[$this->monthUri]) ) $_GET[$this->monthUri] = date('n');

		$this->Calendar = & new Calendar_Month_Weekdays($_GET[$this->yearUri],$_GET[$this->monthUri]);
		$this->Textual = & new Calendar_Decorator_Textual($this->Calendar);
		$this->Uri = & new Calendar_Decorator_Uri($this->Calendar);
		$this->Uri->setFragments($this->yearUri,$this->monthUri);
		$this->Wrapper = & new Calendar_Decorator_Wrapper($this->Calendar);
		$selection = array();
		$selection[] = new Calendar_Day(date('Y'),date('n'),date('d'));
		if ( isset($_GET[$this->dayUri]) ) {
			$selection[] = new Calendar_Day($_GET[$this->yearUri],$_GET[$this->monthUri],$_GET[$this->dayUri]);
		}
		if ( count ($this->selection) > 0 ) {
			foreach ( array_keys($this->selection) as $key ) {
				$selection[] = & $this->selection[$key];
			}
		}
		$this->Wrapper->build($selection);
	}
	/**
	* Returns the PEAR::Calendar_Month_Weekdays object wrapped in a
	* Calendar_Decorator_Wrapper instance
	* @return Calendar_Decorator_Wrapper
	* @access protected
	*/
	function & getCalendar() {
		return $this->Wrapper;
	}
	
	/**
	* Return the URI to a specific page in the list.
	* @return string
	* @access public
	*/
    function getBaseUri() {

        $params = $_GET;
		if ( isset($params[$this->yearUri]) ) {
			unset($params[$this->yearUri]);
		}
		if ( isset($params[$this->monthUri]) ) {
			unset($params[$this->monthUri]);
		}
		if ( isset($params[$this->dayUri]) ) {
			unset($params[$this->dayUri]);
		}		

        $sep = '';
        $query = '';
        foreach ($params as $key => $value) {
            $query .= $sep . $key . '=' . urlencode($value);
            $sep = '&';
        }
        if (empty($query)) {
            return $this->baseUrl.'?';
        } else {
            return $this->baseUrl . '?' . $query . '&';
        }
        
    }	
}
?>