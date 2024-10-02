<?php
//--------------------------------------------------------------------------------
// Copyright 2004 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_COMPONENT
* @author   Jason E. Sweat <jsweat_php AT yahoo DOT com>
* @version $Id: data_table.inc.php,v 1.20 2004/11/12 21:25:07 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
require_once WACT_ROOT . 'template/components/list/list.inc.php';

/**
* run-time component for the Data:Table tag
* @package WACT_COMPONENT
* @author   Jason E. Sweat <jsweat_php AT yahoo DOT com>
*/
class DataTableComponent extends ListComponent {

    /**
    * Prepares the list for iteration, creating an EmptyDataSet if no
    * data set has been registered then calling the dataset reset
    * method.
    * @see EmptyDataSet
    * @return void
    * @access protected
    */
    function prepare() {
        parent::prepare();
        foreach ($this->columns as $col => $obj) {
            $this->columns[$col]->clearFooters();
        }   
    }
     
    /**
    * store the id of the tag and name of a column, transformed to be identifier safe
    * @access private
    */
    function genSafeColId($column) {
        $this->id = 'dt'.md5($this->getServerId().$column).'_';
    }
    /**
    * @var integer  count of rows processed
    */
    var $RowCount=0;
    /**
     * @var array   registry for column componenets as hash map of
     *              'column name' => &column component
     * @access private
     */
    var $columns = array();
    /**
     * @var array   registry for group componenets
     * @access private
     */
    var $groups = array();
    /**
     * @var row class filter
     * @access private
     */
    var $rowCssClassFilter;
    /**
     * @var string row attributes
     */
    var $rowAttribs = '';
    /**
     * set the row attributes for this table
     * @param string the row attributes
     * @return void
     * @access public
     */
    function setRowAttrib($attribs) { 
        if (trim($attribs)) {
            $this->rowAttribs = ' '.trim($attribs); 
        }
    }
    /**
     * register a column component with the table 
     * @param   object	the row class filter
     * @return  void
     * @access  public
     */
    function registerRowCssClassFilter(&$filter) {
        if (is_object($filter)
            && method_exists($filter, 'doFilter')) {
            $this->rowCssClassFilter =& $filter;
        }
    }
    /**
     * renders a table row
     * @param DataSource the template data space
     * @return void
     * @access protected
     */
    function renderRow(&$DataSource) {
        if ($this->colCount()) {
            $this->openTableRow($DataSource);
            foreach ($this->columns as $key => $val) {
                $this->columns[$key]->render($this->_datasource, $DataSource, $this->get($key));
            }
            echo "</tr>\n";
        }
    }
    /**
     * opens a table row
     * @param DataSource the template data source
     * @return void
     * @access public
     */
    function openTableRow(&$DataSource) {
        $this->RowCount++;
        $filter =& $this->rowCssClassFilter;
        if (is_object($filter)
            && $class = $filter->doFilter($DataSource, $this->_datasource, $this->RowCount)
            ) {
            echo '<tr class="'.$class.'"'.$this->rowAttribs.'>';
        } else {
            echo '<tr'.$this->rowAttribs.'>';
        }
    }
    /**
     * register a column component with the table 
     * @param   string              column name identifier
     * @param   DataColumnComponent
     * @return  void
     * @access  protected
     */
    function registerColumn($column, &$columnComponent) {
        $this->columns[$column] =& $columnComponent;
    }
    /**
     * register a group component with the table 
     * @param   DataGroupComponent
     * @return  void
     * @access  protected
     */
    function registerGroup(&$groupComponent) {
        $this->groups[] =& $groupComponent;
    }
    /**
     * will register a column component with the table if none exists
     * @param   string              column name identifier
     * @param   DataColumnComponent
     * @return  void
     * @access  protected
     */
    function addColumn($column) {
        if (!array_key_exists($column, $this->columns)) {
            $this->columns[$column] =& new DataColumnComponent;
        }
    }
    /**
     * return the list of keys for generation of columns
     * @return  array
     * @access  protected
     */
    function getColumnKeys() {
        return array_keys($this->columns);
    }
    /**
     * generate table headings
     * @return void
     * @access protected
     */
    function genHeaders(&$TabDataSource, &$TplDataSource) {
        if (count($this->groups)) {
            $grp_head = false;
            foreach($this->groups as $key => $obj) {
                $grp_head = ($grp_head || ($this->groups[$key]->hasHeader()));
            }
            if ($grp_head) {
                echo '<tr>';
                foreach ($this->columns as $key => $obj) {
                    $this->columns[$key]->header($TabDataSource, $TplDataSource, $key, true, true);
                }
                echo "</tr>\n<tr>";
                foreach ($this->columns as $key => $obj) {
                    $this->columns[$key]->header($TabDataSource, $TplDataSource, $key, true, false);
                }
                echo "</tr>\n";
                return;
            }
        } else {
            echo '<tr>';
        }
        foreach ($this->columns as $key => $obj) {
            $this->columns[$key]->header($TabDataSource, $TplDataSource, $key);
        }
        echo '</tr>';
    }
    /**
     * generate table footers
     * @return void
     * @access protected
     */
    function genColFooters(&$TabDataSource, &$TplDataSource) {
        if ($this->RowCount > 0) {
            $depth = 0;
            foreach ($this->columns as $key => $obj) {
                $depth = max($depth, $this->columns[$key]->getFooterCount());
            }
            if ($depth) {
                for ($i=0; $i<$depth; $i++) {
                    echo '<tr>';
                    foreach ($this->columns as $key => $obj) {
                        $this->columns[$key]->renderFooter($i, $TabDataSource, $TplDataSource, $key);
                    }
                    echo "</tr>\n";
                }
            }
        }
    }
    /**
     * return the nubmer of visible columns
     * @return integer
     * @access public
     */
    function colCount() {
        $ret = 0;
        foreach ($this->columns as $key => $obj) {
            if ($this->columns[$key]->isVisible()) $ret++;
        }
        return $ret;
    }
    /**
     * return a column object by name
     * @param string the column id
     * @return DataColumnComponent
     * @access public
     */
    function &getColumnByName($column) {
        if (array_key_exists($column, $this->columns)) {
            return $this->columns[$column];
        }
    }
     
}

/**
* run-time component for the Data:Column tag
* @package WACT_COMPONENT
* @author   Jason E. Sweat <jsweat_php AT yahoo DOT com>
*/
class DataColumnComponent extends Component {
    /**
     * @var boolean controls visibility
     * @access private
     */
    var $visible = true;
    /**
     * @var prefered output as table heading
     * @access private
     */
    var $heading = false;
    /**
     * @var text for column heading
     * @access private
     */
    var $label = false;
    /**
     * @var string name of the custom render function
     * @access private
     */
    var $renderFunct = false;
    /**
     * @var string name of the custom header function
     * @access private
     */
    var $headerFunct = false;
    /**
     * @var array list of the custom footer functions
     * @access private
     */
    var $footerFuncts = array();
    /**
     * @var DataGroupComponent this columns data:group component, if defined
     * @access private
     */
    var $group = false;
    /**
     * @var row class filter
     * @access private
     */
    var $colCssClassFilter;
    /**
     * @var string  column cell attributes
     * @access private
     */
    var $attribs = '';
    /**
     * @var string header cell attributes
     * @access private
     */
    var $headAttribs = '';
    /**
     * set header attributes
     * @param   string
     * @access  public
     */
    function setHeaderAttrib($attribs) {
        if (trim($attribs)) {
            $this->headAttribs = ' '.trim($attribs); 
        }
    }
    /**
     * set column cell attributes
     * @param   string  the cell attributes
     * @return  void
     * @access  public
     */
    function setAttrib($attribs) { 
        if (trim($attribs)) {
            $this->attribs = ' '.trim($attribs); 
        }
    }
    /**
     * register a column component with the table 
     * @param   string              column name identifier
     * @param   DataColumnComponent
     * @return  void
     * @access  protected
     */
    function registerCssClassFilter(&$filter) {
        if (is_object($filter)
            && method_exists($filter, 'doFilter')) {
            $this->colCssClassFilter =& $filter;
        }
    }
    /**
     * register this columns group object
     * callback to the object to increment column count if this column is visible
     * @param DataColumnComponent
     * @return void
     * @access protected
     */
    function registerGroup(&$group) { 
        $this->group =& $group; 
        if ($this->visible) { $group->incColCount(); }
    }
    /**
     * enable visibility for this column
     * @return void
     * @access public
     */
    function show() { $this->visible = true; }
    /**
     * disable visibility for this column
     * @return void
     * @access public
     */
    function hide() { $this->visible = false; }
    /**
     * is visibility for enabled this column
     * @return void
     * @access public
     */
    function isVisible() { return $this->visible; }
    /**
     * output as a th cell
     * @return void
     * @access public
     */
    function outputTh() { $this->heading = true; }
    /**
     * output as a td cell
     * @return void
     * @access public
     */
    function outputTd() { $this->heading = false; }
    /**
     * set column heading
     * @todo Is this used?
     * @param   string  heading
     * @return void
     * @access public
     */
    function setLabel($label) { $this->label = $label; }
    /**
     * set render function for this column
     * @return void
     * @access public
     */
    function setRenderFunct($funct) { $this->renderFunct = $funct; }
    /**
     * add a footer render function for this column
     * @return void
     * @access public
     */
    function addFooterFunct($funct) { $this->footerFuncts[] = $funct; }
    /**
     * remove all footer render functions for this column
     * @return void
     * @access public
     */
    function clearFooters() { $this->footerFuncts = array(); }
    /**
     * add a footer render function for this column
     * @return void
     * @access public
     */
    function getFooterCount() { return count($this->footerFuncts); }
    /**
     * render a footer cell
     * @param integer count the number of the footer to render
     * @param DataSource the table data space
     * @param DataSource the template data space
     * @return void
     * @access protected
     */
    function renderFooter($count, &$TabDataSource, &$TplDataSource) {
        if ($this->visible) {
            if (array_key_exists($count, $this->footerFuncts)) {
                $funct = $this->footerFuncts[$count];
                $funct($TabDataSource, $TplDataSource, $this->attribs);
            } else {
                echo '<td>&nbsp;</td>';
            }
        }
    }

    /**
     * opens a table row
     * @param DataSource the template data space
     * @return void
     * @access public
     */
    function openTableRow(&$DataSource) {
        $filter =& $this->rowCssClassFilter;
        if (is_object($filter)
            && $class = $filter->doFilter($DataSource, $this->_datasource, $this->RowCount)
            ) {
            echo '<tr class="'.$class.'">';
        } else {
            echo '<tr>';
        }
    }


    /**
     * render the cell
     * @param DataSource the template data space
     * @param string    the value
     * @return void
     * @access protected
     */
    function render(&$TabDataSource, &$TplDataSource, $value) {
        if ($this->visible) {
            $cell_type = ($this->heading) ? 'th' : 'td';
            $filter =& $this->colCssClassFilter;
            $class_out = '';
            if (is_object($filter)
            	&& is_object($table =& $this->findParentByClass('DataTableComponent'))
                && $class = $filter->doFilter($TplDataSource, $TabDataSource, $table->RowCount)
                ) {
                $class_out = ' class="'.$class.'"';
            }
            echo "<$cell_type$class_out".$this->attribs.">";
            if ($funct = $this->renderFunct) {
                 $funct($TabDataSource, $TplDataSource);
            } else {
                echo $value;
            }
            echo "</$cell_type>";
        }
    }
    /**
     * set custom header function for this column
     * @return void
     * @access public
     */
    function setHeaderFunct($funct) { $this->headerFunct = $funct; }
    /**
     * render the header for this column
     * @param DataSource the table data space
     * @param DataSource the template data space
     * @param string    the column id
     * @param boolean   does this table require group headings
     * @return void
     * @access protected
     */
    function header(&$TabDataSource, &$TplDataSource, $key, $grpHead=false, $firstPass=true) {
        if ($this->visible) {
            if ($grpHead) {
                if ($firstPass) {
                    if (is_object($this->group) 
                        && $this->group->hasHeader()) {
                            $this->group->renderHeader($TabDataSource, $TplDataSource);
                            return;
                    } elseif (is_object($this->group) 
                        && !$this->group->hasHeader()) {
                            echo '<th rowspan="2"'.$this->headAttribs.'>';
                    } else {
                        echo '<th rowspan="2"'.$this->headAttribs.'>';
                    }
                } else { //not firstPass
                    if (is_object($this->group) 
                    && $this->group->hasHeader()) {
                        echo '<th>';
                    } else  {
                        return;
                    }
                }
            } else {
                echo '<th'.$this->headAttribs.'>';
            }
            if ($funct = $this->headerFunct) {
                 $funct($TabDataSource, $TplDataSource);
            } elseif ($this->label) {
                echo $this->label;
            } else {
                echo $key;
            }
            echo '</th>';
        }
    }

}

/**
* run-time component for the Data:Group tag
* @package WACT_COMPONENT
* @author   Jason E. Sweat <jsweat_php AT yahoo DOT com>
*/
class DataGroupComponent extends Component {
    /**#@+
     * @access private
     */
    var $output = false;
    var $columnCount = 0;
    var $headerFunct = false;
    /**#@-*/    
    /**#@+
     * @access public
     */
    function incColCount() { $this->columnCount++; }
    function getColCount() { return $this->columnCount; }
    function setHeaderFunct($funct) { $this->headerFunct = $funct; }
    function hasHeader() { 
        return ($this->columnCount && $this->headerFunct) 
                ? true : false; 
    }
    /**#@-*/    
    /**
     * render the header for this column group
     * @param DataSource the table data space
     * @param DataSource the template data space
     * @return void
     * @access protected
     */
    function renderHeader(&$TabDataSource, &$TplDataSource) {
        if (!$this->output && $this->columnCount && $this->headerFunct) {
            $this->output = true;
            echo '<th colspan="'.$this->columnCount.'">';
            $funct = $this->headerFunct;
            $funct($TabDataSource, $TplDataSource);
            echo '</th>';
        }
    }
}

#?>