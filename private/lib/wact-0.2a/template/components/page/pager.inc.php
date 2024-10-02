<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_COMPONENT
* @version $Id: pager.inc.php,v 1.1 2004/06/13 21:26:09 harryf Exp $
*/
//--------------------------------------------------------------------------------

//--------------------------------------------------------------------------------
/**
* Represents a page navigator at runtime.  The total number of items in the 
* list to be paged must be known before the navigator can be displayed.
* @see http://wact.sourceforge.net/index.php/PageNavigatorComponent
* @access public
* @package WACT_COMPONENT
*/
class PageNavigatorComponent extends Component {
	/**
	* Used while displaying a page number list to determine when a separator
	* should be shown between two page numbers
	* @var boolean
	* @access protected
	*/
	var $ShowSeparator;

	/**
	* Used while displaying a page number list to the page number being displayed
	* @var integer
	* @access protected
	*/
    var $page;
    
	/**
	* Used while displaying a page number list to determine when an elipses should
	* be displayed
	* @var integer
	* @access protected
	*/
    var $ElipsesCount;

	/**
	* The page number of the last page in the list.
	* @var integer
	* @access protected
	*/
    var $LastPage;

	/**
	* The page number of the current page in the list.
	* @var integer
	* @access protected
	*/
    var $CurrentPage;

	/**
	* The Number of page numbers anchored at each side of the page list.
	* @var integer
	* @access protected
	*/
    var $AnchorSize = 3;
    
	/**
	* Number of pages around the current one to display. (must be odd number)
	* @var integer
	* @access protected
	*/
    var $WindowSize = 3;

	/**
	* Number of items to display on each page of the list.
	* This is set via the items attribute of the page:navigator tag.
	* @var integer
	* @access protected
	*/
    var $Items = 20;

	/**
	* The total number of items in this list.
	* @var integer
	* @access protected
	*/
    var $TotalItems;

	/**
	* The variable used to carry the current page in the URL.
	* @access protected
	*/
    var $pageVariable = 'page';

	/**
	* The Url used to display individual pages of the list.
	* @access protected
	*/
    var $baseUrl;

	/**
	* A paged dataset reference.  Used for determining the total number
	* of items the pager should navagate across.
	* @access protected
	*/
    var $PagedDataSet;
    
	/**
	* Initialize this class
	* @access public
	*/
    function PageNavigatorComponent() {
        $this->baseUrl = $_SERVER['REQUEST_URI'];
        $pos = strpos($this->baseUrl, '?');
        if (is_integer($pos)) {
            $this->baseUrl = substr($this->baseUrl, 0, $pos);
        }
        
        $this->CurrentPage = @$_GET[$this->pageVariable];
        if (empty($this->CurrentPage)) {
            $this->CurrentPage = 1;
        }
    }
    
	/**
	* Set the total number of items in the list.
	* @access protected
	*/
    function setTotalItems($items) {
        $this->TotalItems = $items;
    }
    
	/**
	* Set the database which this pager controls.
	* @param object dataset
	* @access public
	*/
    function setPagedDataSet(&$dataset) {
        $this->PagedDataSet =& $dataset;
    }

	/**
	* Get the item number of the first item in the list.
	* Usually called by the PagedDataSet to determine where to
	* begin query.
	* @return integer
	* @access public
	*/
    function getStartingItem() {
        return $this->Items * ($this->CurrentPage - 1);
    }
    
	/**
	* Get the item number of the first item in the list.
	* Usually called by the PagedDataSet to determine how many
	* items are on a page.
	* @return integer
	* @access public
	*/
    function getItemsPerPage() {
        return $this->Items;
    }

	/**
	* Is the current page being displayed the first page in the page list?
	* @return boolean
	* @access public
	*/
    function IsFirst() {
        return ($this->CurrentPage == 1);
    }
    
	/**
	* Is there a page available to display before the current page being displayed?
	* @return boolean
	* @access public
	*/
    function hasPrev() {
        return ($this->CurrentPage > 1);
    }
    
	/**
	* Is there a page available to display after the current page being displayed?
	* @return boolean
	* @access public
	*/
    function hasNext() {
        return ($this->CurrentPage < $this->LastPage);
    }

	/**
	* Is the current page being displayed the last page in the page list?
	* @return boolean
	* @access public
	*/
    function IsLast() {
        return ($this->CurrentPage == $this->LastPage);
    }

	/**
	* Initialize values used by this component.
	* This is called automatically from the compiled template.
	* @return void
	* @access protected
	*/
    function prepare() {
        if (isset($this->PagedDataSet)) {
            $this->setTotalItems($this->PagedDataSet->getTotalRowCount());
        }

        $this->LastPage = ceil($this->TotalItems / $this->Items);
        if ($this->LastPage < 1) {
            $this->LastPage = 1;
        }
        
        $this->ShowSeparator = FALSE;
        $this->page = 0;
        $this->ElipsesCount = 0;
    }

	/**
	* Advance the page list cursor to the next page.
	* This is called automatically from the compiled template and should
	* not be called directly.
	* @return boolean FALSE if there are no more pages.
	* @access protected
	*/
    function next() {
        $this->page++;
        return ($this->page <= $this->LastPage);  
    }

	/**
	* Get the page number of the page being displayed in the page number list.
	* This is called automatically from the compiled template and should
	* not be called directly.
	* @return integer
	* @access protected
	*/
    function getPageNumber() {
        return $this->page;
    }

    function getCurrentPageNumber() {
        return $this->CurrentPage;
    }
    
    function getLastPageNumber() {
        return $this->LastPage;
    }
  

	/**
	* Is the page number of the page being displayed in the page number list
	* the current page being displayed in the browser?
	* This is called automatically from the compiled template and should
	* not be called directly.
	* @return boolean
	* @access protected
	*/
    function isCurrentPage() {
        return $this->page == $this->CurrentPage;
    }
    
	/**
	* Should the current page in the page number list be displayed?
	* This is called automatically from the compiled template and should
	* not be called directly.
	* @return boolean
	* @access protected
	*/
    function isDisplayPage() {
        $HalfWindowSize = ($this->WindowSize-1) / 2;
        return (
            $this->page <= $this->AnchorSize || 
            $this->page > $this->LastPage - $this->AnchorSize ||
            ($this->page >= $this->CurrentPage - $HalfWindowSize &&
            $this->page <= $this->CurrentPage + $HalfWindowSize) ||
            ($this->page == $this->AnchorSize + 1 && 
            $this->page == $this->CurrentPage - $HalfWindowSize - 1) ||
            ($this->page == $this->LastPage - $this->AnchorSize && 
            $this->page == $this->CurrentPage + $HalfWindowSize + 1)
            );
    }

	/**
	* The URI of the page that is being displayed in the page number list
	* This is called automatically from the compiled template and should
	* not be called directly.
	* @return string
	* @access protected
	*/
    function getCurrentPageUri() {
        return $this->getPageUri($this->page);
    }

	/**
	* Return the URI to a specific page in the list.
	* @return string
	* @access public
	*/
    function getPageUri($page) {

        $params = $_GET;
        if ($page <= 1) {
            unset($params[$this->pageVariable]);
        } else {
            $params[$this->pageVariable] = $page;
        }

        $sep = '';
        $query = '';
        foreach ($params as $key => $value) {
            $query .= $sep . $key . '=' . urlencode($value);
            $sep = '&';
        }
        if (empty($query)) {
            return $this->baseUrl;
        } else {
            return $this->baseUrl . '?' . $query;
        }
        
    }

	/**
	* Return the URI to the first page in the list.
	* @return string
	* @access public
	*/
    function getFirstPageUri() {
        return $this->getPageUri(1);
    }

	/**
	* Return the URI to the previous page in the list.
	* @return string
	* @access public
	*/
    function getPrevPageUri() {
        return $this->getPageUri($this->CurrentPage - 1);
    }
    
	/**
	* Return the URI to the last page in the list.
	* @return string
	* @access public
	*/
    function getLastPageUri() {
        return $this->getPageUri($this->LastPage);
    }
    
	/**
	* Return the URI to the next page in the list.
	* @return string
	* @access public
	*/
    function getNextPageUri() {
        return $this->getPageUri($this->CurrentPage + 1);
    }
    
    
}
?>