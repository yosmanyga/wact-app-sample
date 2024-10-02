<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_UTIL
* @version $Id: arraydataset.inc.php,v 1.20 2004/06/15 03:03:50 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
/**
* Provides an Iterator over a Recordset of DataSpaces.
*
* The recordset/dataset is made up of rows accessed using the Iterator and
* DataSpace interfaces. It should be exactly analagous to a WACT database
* recordset.
*
* e.g.
* <code>
* $dataset = array (
*     array ('username'=>'jdoe','email'=>'jdoe@hotmail.com'),
*     array ('username'=>'rsmith','email'=>'rsmith@cure.org'),
*     array ('username'=>'nclark','email'=>'nclark@yahoo.com'),
* );
* $ds = new ArrayDataSet($dataset);
* while ($ds->next()) {
*     do_something($ds->get(username));
* }
* </code>
* @see http://wact.sourceforge.net/index.php/ArrayDataSet
* @todo - any need for paging setup?
* @access public
* @package WACT_UTIL
*/
class ArrayDataSet {            /* implements DataSpace, Iterator */
    /**
     * @var array The Recordset (an array of arrays)
     * @access private
     */
    var $dataset;

    /**
     * @var array The current Record (row) from the Recordset
     * @access private
     */
    var $row;

    /**
     * @var array A duplicate of the current row, used with filters
     * @access private
     */
    var $row_copy;

    /**
     * @var object A filter object
     * @access private
     */
    var $filter;

    /**
     * @var boolean Set when a filter is applied to the current row
     * @access private
     */
    var $filtered;

    /**
     * @var boolean Indicates that the cursor is at the first record (row)
     * @access private
     */
    var $first;

    /**
     * @param array An array of arrays representing a Recordset
     */
    function ArrayDataSet($array) {
        $this->importDataSetAsArray($array);
        $this->row = array();
        $this->filtered = FALSE;
        $this->first = TRUE;
    }

    /**
     * Check whether the current row is empty
     */
    function isEmpty() {
        return empty($this->row);
    }

    /**
     * Sets up a Recordset from an array of row arrays
     *
     * Replaces the current Recordset and resets the internal cursor
     * @param array $dataset
     */
    function importDataSetAsArray($dataset) {
        $this->dataset = $dataset;
        $this->reset();
    }

    /**
     * Export the Recordset as an array of row arrays
     */
    function exportDataSetAsArray() {
        return $this->dataset;
    }

    /**
     * Alias for exportDataSetAsArray
     * @deprecated
     */
    function getDataSet() {
        return $this->exportDataSetAsArray();
    }

    //--------------------------------------------------------------------------------
    // DataSource implementation

    /**
     * Return an item from the current row by key/name
     * @param string $name The item key/name
     * @return mixed
     */
    function get($name) {
        if (isset($this->row[$name])) {
            return $this->row[$name];
        }
    }

    /**
     * Set the value of an item in the current row
     * @param string $name The item key/name
     * @param mixed $value The item value
     */
    function set($name, $value) {
        $this->row[$name] = $value;
    }

    function remove($name) {
        unset($this->row[$name]);
    }

    function removeAll() {
        $this->row = array();
    }

    /**
     * Import a dictionary/hash/map that will replace the current row
     * @param array $array 
     */
    function import($array) {
        if (is_array($array)) {
            $this->row =& $array;
        }
    }

    /**
     * Append a dictionary/hash/map of key value pairs to the current row
     *
     * Existing keys are overwritten
     * @param array $array
     */
    function merge($array) {
        $this->row = array_merge($this->row, $array);
    }

    /**
     * Export the current row/dataspace as a dictionary/hash/map
     * @return array
     */
    function &export() {
        return $this->row;
    }

    function isDataSource() {
        return TRUE;
    }

    function hasProperty($name) {
        return isset($this->row[$name]);
    }

    function getPropertyList() {
        return array_keys($this->row);
    }
    
    /**
     * Register a filter object on the current row.
     *
     * Filters can be used to transform rows. The filter object must provide a
     * doFilter(&$array) method that filters values from the current row. The
     * filter is passed a copy of the current row.
     * @param object $filter
     */
    function registerFilter(&$filter) {
        $this->filter =& $filter;
    }

    /**
     * Filter the current row as apt. Works on a copy of the row.
     */
    function prepare() {
        if (isset($this->filter) &&
            method_exists($this->filter, 'doFilter')) {
            if ($this->filtered) {
                $this->row = $this->row_copy;
            } else {
                $this->row_copy = $this->row;
            }
            $this->filter->doFilter($this->row);
            $this->filtered = TRUE;
        }
    }

    //--------------------------------------------------------------------------------
    // Iterator implementation

    /**
     * Reset the internal cursor to the beginning of the Recordset
     *
     * next() must be called after reset() to access a valid row via the
     * DataSpace methods.
     */
    function reset() {
        reset($this->dataset);
        $this->first = TRUE;
    }

    /**
     * Moves the internal cursor to the next row of the Recordset
     *
     * Sets the row accessed by the DataSpace interface to the next row of the
     * Recordset. Returns TRUE if there is another row in the recordset, FALSE
     * otherwise. Calls prepare().
     */
    function next() {
        $this->filtered = FALSE;
        if ($this->first) {
            $dataspace = current($this->dataset);
            $this->first = FALSE;
        } else {
            $dataspace = next($this->dataset);
        }
        /* casts are for clarification only */
        $this->row = (bool) $dataspace ? $dataspace : array();
        if ((bool) $dataspace) {
            $this->prepare();
        }
        return (bool) $dataspace;
    }

}
?>
