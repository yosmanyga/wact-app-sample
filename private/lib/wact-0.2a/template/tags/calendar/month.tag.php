<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_COMPONENT
* @version $Id: month.tag.php,v 1.18 2004/11/18 04:22:47 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* Include parent classes
*/
require_once WACT_ROOT . 'template/tags/core/block.tag.php';
require_once WACT_ROOT . 'template/tags/html/table.tag.php';
require_once WACT_ROOT . 'template/tags/html/tablecaption.tag.php';
require_once WACT_ROOT . 'template/tags/html/tablerow.tag.php';
require_once WACT_ROOT . 'template/tags/html/tablecell.tag.php';
require_once WACT_ROOT . 'template/tags/html/anchor.tag.php';

/**
* Register the tags in this file
*/
TagDictionary::registerTag(new TagInfo('calendar:MONTH', 'CalendarMonthTag'), __FILE__);
TagDictionary::registerTag(new TagInfo('calendar:TITLE', 'CalendarTitleStyleTag'), __FILE__);
TagDictionary::registerTag(new TagInfo('calendar:dayheader', 'CalendarDayHeaderStyleTag'), __FILE__);
TagDictionary::registerTag(new TagInfo('calendar:DAYSTYLE', 'CalendarDayStyleTag'), __FILE__);
TagDictionary::registerTag(new TagInfo('calendar:SELECTEDDAYSTYLE', 'CalendarSelectedDayStyleTag'), __FILE__);
TagDictionary::registerTag(new TagInfo('calendar:EMPTYDAYSTYLE', 'CalendarEmptyDayStyleTag'), __FILE__);
TagDictionary::registerTag(new TagInfo('calendar:NEXTPREV', 'CalendarNextPrevStyleTag'), __FILE__);

/**
* Compile time tag for generating monthly calendars
* @todo EXPERIMENTAL
* @see http://wact.sourceforge.net/index.php/CalendarMonthTag
* @access protected
* @package WACT_TAG
*/
class CalendarMonthTag extends CoreBlockTag {
	/**
	* File to include at runtime
	* @var string path to runtime component relative to WACT_ROOT
	* @access private
	*/
	var $runtimeIncludeFile = 'template/components/calendar/calendar_month.inc.php';
	/**
	* Name of runtime component class
	* @var string
	* @access private
	*/
	var $runtimeComponentName = 'CalendarMonthComponent';
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function preGenerate(&$code) {
		parent::preGenerate($code);
		if ( $yearUri = $this->getAttribute('yearuri') ) {
			$code->writePHP($this->getComponentRefCode() . '->yearUri=');
			$code->writePHPLiteral($yearUri);
			$code->writePHP(';');
		}
		if ( $monthUri = $this->getAttribute('monthuri') ) {
			$code->writePHP($this->getComponentRefCode() . '->monthUri=');
			$code->writePHPLiteral($monthUri);
			$code->writePHP(';');
		}
		if ( $dayUri = $this->getAttribute('dayuri') ) {
			$code->writePHP($this->getComponentRefCode() . '->dayUri=');
			$code->writePHPLiteral($dayUri);
			$code->writePHP(';');
		}
		$code->writePHP($this->getComponentRefCode() . '->prepare();');
	}
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateContents(&$code) {
		$calMonthObjectRefCode = getNewServerId();
		$calDayObjectRefCode = getNewServerId();

		$Table = & new HtmlTableTag();
		
		foreach($this->getAttributesAsArray() as $AttributeName => $AttributeValue) {
		    $Table->setAttribute($AttributeName, $AttributeValue);
		}
		
		$this->addChild($Table);

		if ( !$TitleStyle = & $this->findChildByClass('CalendarTitleStyleTag') ) {
			$TitleStyle = & new CalendarTitleStyleTag();
			$this->addChild($TitleStyle);
		}
		$TitleStyle->calComponent = $this->getComponentRefCode();
		$TitleStyle->calMonthObjectRefCode = $calMonthObjectRefCode;

		if ( !$NextPrevStyle = & $this->findChildByClass('CalendarNextPrevStyleTag') ) {
			$NextPrevStyle = & new CalendarNextPrevStyleTag();
			$this->addChild($NextPrevStyle);
		}
		$NextPrevStyle->calMonthObjectRefCode = $calMonthObjectRefCode;

		if ( !$DayStyle = & $this->findChildByClass('CalendarDayStyleTag') ) {
			$DayStyle = & new CalendarDayStyleTag();
			$this->addChild($DayStyle);
		}
		$DayStyle->calDayObjectRefCode = $calDayObjectRefCode;

		if ( !$SelectedDayStyle = & $this->findChildByClass('CalendarSelectedDayStyleTag') ) {
			$SelectedDayStyle = & new CalendarSelectedDayStyleTag();
			$this->addChild($SelectedDayStyle);
		}
		$SelectedDayStyle->calDayObjectRefCode = $calDayObjectRefCode;

		if ( !$EmptyDayStyle = & $this->findChildByClass('CalendarEmptyDayStyleTag') ) {
			$EmptyDayStyle = & new CalendarEmptyDayStyleTag();
			$this->addChild($EmptyDayStyle);
		}
		$EmptyDayStyle->calDayObjectRefCode = $calDayObjectRefCode;

		$code->writePHP('$'. $calMonthObjectRefCode . '='. 
			$this->getComponentRefCode() . '->getCalendar();');
		$weekCounterRefCode = getNewServerId();

		$Table->preGenerate($code);
		$Table->generateContents($code);

		if ( !$this->getAttribute('titleshow') ) {
			$this->setAttribute('titleshow', 'true');
		}
		if ( $this->getBoolAttribute('titleshow')) {
			$code->writeHTML("<tr>\n<td colspan=\"7\">\n");
			$code->writeHTML("<table width=\"100%\" cellspacing=\"0\" border=\"0\">\n<tr>\n");

			if ( !$this->getAttribute('shownextprev') ) {
				$this->setAttribute('shownextprev', 'true');
			}
			if ( $this->getBoolAttribute('shownextprev')) {
				$NextPrevStyle->preGenerate($code);
				$NextPrevStyle->generateContents($code);
				// This link should be a component
				$code->writeHTML("<a href=\"");
				$code->writePHP('echo ' .
					$this->getComponentRefCode() . '->prevLink();');
				$code->writeHTML('"');
				
				$NextPrevStyle->generateAttributeList($code);
				
				$code->writeHTML('>');
				if ( !$prev = $this->getAttribute('prevtext') ) {
					$prev = '&lt;';
				}
				$code->writeHTML($prev);
				$code->writeHTML('</a>');
				$NextPrevStyle->postGenerate($code);
			}

			$TitleStyle->preGenerate($code);
			$TitleStyle->generateContents($code);
			$TitleStyle->postGenerate($code);

			if ( $this->getBoolAttribute('shownextprev')) {
				$NextPrevStyle->preGenerate($code);
				$NextPrevStyle->generateContents($code);
				// This link should be a component
				$code->writeHTML("<a href=\"");
				$code->writePHP('echo ' .
				    $this->getComponentRefCode() . '->nextLink();');
				$code->writeHTML('"');

				$NextPrevStyle->generateAttributeList($code);

				$code->writeHTML('>');;
				if ( !$next = $this->getAttribute('nexttext') ) {
					$next = '&gt;';
				}
				$code->writeHTML($next);
				$code->writeHTML('</a>');
				$NextPrevStyle->postGenerate($code);
			}

			$code->writeHTML("<tr>\n</table>\n");
			$code->writeHTML("</td>\n</tr>\n");
		}

		if ( !$this->getAttribute('dayheadershow') ) {
			$this->setAttribute('dayheadershow', 'true');
		}
		if ( $this->getBoolAttribute('dayheadershow')) {
			$code->writeHTML('<tr');
			if ( $this->hasAttribute('dayheaderstyle') ) {
				$code->writeHTML(' style="' . $this->getAttribute('dayheaderstyle') . '"');
			}
			$code->writeHTML(">\n");
			// Not finding it - why?
			if ( !$DayHeader = & $this->findChildByClass('CalendarDayHeaderStyleTag') ) {
				$DayHeader = & new CalendarDayHeaderStyleTag();
				$this->addChild($DayHeader);
			}
			$DayHeader->calComponent = $this->getComponentRefCode();

			$DayHeader->preGenerate($code);
			$DayHeader->generateContents($code);
			$DayHeader->postGenerate($code);
			$code->writeHTML("\n</tr>\n");
		}

		$code->writePHP('while($'.$calDayObjectRefCode.'=$'.
			$calMonthObjectRefCode.'->fetch(\'Calendar_Decorator_Uri\')){');
		$code->writePHP('$'.$calDayObjectRefCode.'->setFragments('.
			$this->getComponentRefCode().'->yearUri,'.
			$this->getComponentRefCode().'->monthUri,'.
			$this->getComponentRefCode().'->dayUri);');
		$code->writePHP('if ($'.$calDayObjectRefCode.'->isFirst()) {');
		$code->writeHTML("<tr>\n");
		$code->writePHP('}');

		$code->writePHP('if ($'.$calDayObjectRefCode.'->isEmpty()) {');

		$EmptyDayStyle->preGenerate($code);
		$EmptyDayStyle->generateContents($code);
		$EmptyDayStyle->postGenerate($code);

		$code->writePHP('} else if ($'.$calDayObjectRefCode.'->isSelected()) {');

		$SelectedDayStyle->preGenerate($code);
		$SelectedDayStyle->generateContents($code);
		$SelectedDayStyle->postGenerate($code); 

		$code->writePHP('} else {');

		$DayStyle->preGenerate($code);
		$DayStyle->generateContents($code);
		$DayStyle->postGenerate($code);

		$code->writePHP('}');

		$code->writePHP('if ($'.$calDayObjectRefCode.'->isLast()) {');
		$code->writeHTML("</tr>\n");
		$code->writePHP('}');

		$code->writePHP('}');

		$Table->postGenerate($code);
	}
}
/**
* Handles generation of the calendar title
* @todo EXPERIMENTAL
* @see http://wact.sourceforge.net/index.php/CalendarTitleStyleTag
* @access protected
* @package WACT_TAG
*/
class CalendarTitleStyleTag extends HtmlTableCellTag {
	/**
	* Name of the runtime calendar component reference
	* @var string
	* @access protected
	*/
	var $calComponent;
	/**
	* Used to refer to the runtime instance of PEAR::Calendar used to render
	* the calendar
	* @var string
	* @access protected
	*/
	var $calMonthObjectRefCode;
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateContents(&$code) {
		if ( !$align = $this->getAttribute('align') ) {
			$this->setAttribute('align', 'center');
		}
		parent::generateContents($code);
		if ( !$monthformat = $this->getAttribute('monthformat') ) {
			$monthformat = 'long'; 
		}
		if ( !$yearformat = $this->getAttribute('yearformat') ) {
			$yearformat = 'long'; 
		}
		$code->writePHP('echo('.$this->calComponent.'->monthName(');
		$code->writePHPLiteral($monthformat);
		$code->writePHP(').\' \');');
		if ( !$this->getAttribute('hideyear') ) {
			$code->writePHP('echo('.$this->calComponent.'->yearFormatted(');
			$code->writePHPLiteral($yearformat);
			$code->writePHP('));');
		}
	}
}
/**
* Handles generation of the calendar day headers
* @todo EXPERIMENTAL
* @see http://wact.sourceforge.net/index.php/CalendarDayHeaderStyleTag
* @access protected
* @package WACT_TAG
*/
class CalendarDayHeaderStyleTag extends CoreBlockTag {
	/**
	* Name of the runtime calendar component reference
	* @var string
	* @access protected
	*/
	var $calComponent;
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateContents(&$code) {
		$headers = getNewServerId();
		if ( !$format = $this->getAttribute('format') ) {
			$format = 'long'; 
		}
		$code->writePHP('$'.$headers.'='.$this->calComponent.'->dayHeaders(');
		$code->writePHPLiteral($format);
		$code->writePHP(');');
		for ( $i = 0; $i<7; $i++ ) {
			$TH = & new HtmlTableHeaderTag();
            foreach($this->getAttributesAsArray() as $AttributeName => $AttributeValue) {
                $TH->setAttribute($AttributeName, $AttributeValue);
            }
			$this->addChild($TH);
			$TH->preGenerate($code);
			$TH->generateContents($code);
			$code->writePHP('echo ($'.$headers.'['.$i.']);');
			$TH->postGenerate($code);
		}
	}
}
/**
* Handles generation of the calendar days
* @todo EXPERIMENTAL
* @see http://wact.sourceforge.net/index.php/CalendarDayStyleTag
* @access protected
* @package WACT_TAG
*/
class CalendarDayStyleTag extends HtmlTableCellTag {
	/**
	* Used to refer to the runtime instance of PEAR::Calendar used to render
	* the calendar
	* @var string
	* @access protected
	*/
	var $calDayObjectRefCode;
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateContents(&$code) {
		parent::generateContents($code);
		if ( !$links = $this->getAttribute('links') ) {
			$links = 'show';
		}
		if ( $links == 'show' ) {
			$code->writeHTML('<a href="');
			$code->writePHP('echo ($_SERVER[\'PHP_SELF\'].\'?\'.$'.
			    $this->calDayObjectRefCode.'->this(\'day\'));');
			$code->writeHTML('">');
		}
		$code->writePHP('echo ($'.$this->calDayObjectRefCode.'->thisDay());');
		if ( $links == 'show' ) {
			$code->writeHTML('</a>');
		}
	}
}
/**
* Handles generation of selected calendar days
* @todo EXPERIMENTAL
* @see http://wact.sourceforge.net/index.php/CalendarSelectedDayStyleTag
* @access protected
* @package WACT_TAG
*/
class CalendarSelectedDayStyleTag extends HtmlTableCellTag {
	/**
	* Used to refer to the runtime instance of PEAR::Calendar used to render
	* the calendar
	* @var string
	* @access protected
	*/
	var $calDayObjectRefCode;
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateContents(&$code) {
		parent::generateContents($code);
		if ( !$links = $this->getAttribute('links') ) {
			$links = 'show';
		}
		if ( $links == 'show' ) {
			$code->writeHTML('<a href="');
			$code->writePHP('echo ($_SERVER[\'PHP_SELF\'].\'?\'.$'.
			    $this->calDayObjectRefCode.'->this(\'day\'));');
			$code->writeHTML('">');
		}
		$code->writePHP('echo ($'.$this->calDayObjectRefCode.'->thisDay());');
		if ( $links == 'show' ) {
			$code->writeHTML('</a>');
		}
	}
}
/**
* Handles generation of "empty" calendar days
* @todo EXPERIMENTAL
* @see http://wact.sourceforge.net/index.php/CalendarEmptyDayStyleTag
* @access protected
* @package WACT_TAG
*/
class CalendarEmptyDayStyleTag extends HtmlTableCellTag {
	/**
	* Used to refer to the runtime instance of PEAR::Calendar used to render
	* the calendar
	* @var string
	* @access protected
	*/
	var $calDayObjectRefCode;
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateContents(&$code) {
		parent::generateContents($code);
		if ( $showDays = $this->getAttribute('showEmptyDays') ) {
			$code->writePHP('echo ($'.$this->calDayObjectRefCode.'->thisDay());');
		}
		if ( !$numbers = $this->getAttribute('numbers') ) {
			$numbers = 'hide';
		}
		if ( !$links = $this->getAttribute('links') ) {
			$links = 'hide';
		}
		if ( $links == 'show' ) {
			$code->writeHTML('<a href="');
			$code->writePHP('echo ($_SERVER[\'PHP_SELF\'].\'?\'.$'.$this->calDayObjectRefCode.'->this(\'day\'));');
			$code->writeHTML('">');
		}
		if ( $numbers == 'show' ) {
			$code->writePHP('echo ($'.$this->calDayObjectRefCode.'->thisDay());');
		}
		if ( $links == 'show' ) {
			$code->writeHTML('</a>');
		}
	}
}

/**
* Handles generation of next / prev links on the calendar
* @todo EXPERIMENTAL
* @see http://wact.sourceforge.net/index.php/CalendarNextPrevStyleTag
* @access protected
* @package WACT_TAG
*/
class CalendarNextPrevStyleTag extends HtmlTableCellTag {
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateContents(&$code) {
		if ( !$align = $this->getAttribute('align') ) {
			$this->setAttribute('align', 'center');
		}
		parent::generateContents($code);
	}
}
?>
