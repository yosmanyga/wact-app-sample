<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_UTIL
* @version $Id: mutabledataset.inc.php,v 1.6 2004/03/04 00:07:40 jon-bangoid Exp $
*/
//--------------------------------------------------------------------------------
/**
* Include ArrayDataSet
*/
require_once WACT_ROOT . 'util/arraydataset.inc.php';
/**
* Extends the ArrayDataSet with methods to allow manipulation of the dataset, such
* adding / removing entire rows as opposed to manipulation of the contents of
* single rows, as provided by the ArrayDataSet.
* @see http://wact.sourceforge.net/index.php/MutableDataSet
* @access public
* @package WACT_UTIL
*/
class MutableDataSet extends ArrayDataSet {
    /**
    * Constructs MutableDataSet
    * @param array (an array of arrays)
    * @access public
    */
    function MutableDataSet($dataset = NULL) {
        parent::ArrayDataSet($dataset);
    }

    /**
    * Adds a row to the beginning of the datase resetting the dataset
    * cursor
    * @param array
    * @return int new number of rows in the dataset
    * @access public
    */
    function unshiftRow($row) {
        return array_unshift($this->dataset,$row);
    }
    /**
    * Removes a row from the beginning of the dataset, returning it to the
    * the caller, resetting the dataset cursor
    * @return array the deleted row
    * @access public
    */
    function shiftRow() {
        return array_shift($this->dataset);
    }
    /**
    * Adds a row to the end of the dataset, resetting the dataset cursor
    * @param array
    * @return int new number of rows in the dataset
    * @access public
    */
    function pushRow($row) {
        return array_push($this->dataset,$row);
    }
    /**
    * Removes a row from the end of the dataset, returning it to the
    * the caller then resetting the dataset cursor
    * @return array the deleted row
    * @access public
    */
    function popRow() {
        return array_pop($this->dataset);
    }
    /**
    * Inserts a row directly after the current dataset index and moves
    * the cursor to that location. Be warned - it's not going to be fast
    * with a large dataset
    * @param array
    * @return int new number of rows in the dataset
    * @access public
    */
    function insertRow($row) {
        $newkey = key($this->dataset)+1;
        $after = array_splice($this->dataset,$newkey);
        array_push($this->dataset,$row);
        $this->dataset = array_merge($this->dataset,$after);
        $this->seekRow($newkey);
        return count ( $this->dataset );
    }
    /**
    * Deletes the row at the current dataset index, moving the cursor
    * back to the previous row index. Be warned - it's not going to be fast
    * with a large dataset.
    * @param array
    * @return array the deleted row
    * @access public
    */
    function deleteRow() {
        ($key = key($this->dataset) ) > 0 ? $lastkey = ($key - 1) : $lastkey = 0;
        if ( !$key ) $key = 0;
        $deleted = $this->dataset[$key];
        unset($this->dataset[$key]);
        $this->dataset = array_values($this->dataset);
        $this->seekRow($lastkey);
        return $deleted;
    }
    /**
    * Moves the internal (i.e. PHP) cursor for the dataset array
    * to the given index. If out of bounds (too large / too small)
    * moves the pointer to the end / beginning of the dataset.
    * Not exceptionally fast to be warned.
    * @param int index of row in dataset to move pointer to
    * @return array the row at the provided index
    * @access public
    */
    function seekRow($index) {
        $last = (count($this->dataset)-1);
        if ( $index > 0 && $index < $last ) {
            $this->first = FALSE;
            if ( $index < ($last/2) ) {
                for ($i = 0; $i <= $index; $i++ ) {
                    if ( $i != 0 ) {
                        next($this->dataset);
                    } else {
                        reset($this->dataset);
                    }
                }
            } else {
                for ($i = $last; $i >= $index; $i-- ) {
                    if ( $i != $last ) {
                        prev($this->dataset);
                    } else {
                        end ($this->dataset);
                    }
                }
            }
            $this->row = current($this->dataset);
        } else if ( $index >= $last ) {
            $this->first = FALSE;
            $this->row = end($this->dataset);
        } else {
            $this->reset();
            $this->row = reset($this->dataset);
        }
        return $this->row;
    }
}
?>