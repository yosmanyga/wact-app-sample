<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_CONTROLLERS
* @version $Id: requestfilter.inc.php,v 1.1 2004/11/07 23:12:30 jeffmoore Exp $
*/
// EXPERIMENTAL

class RequestFilter {
    var $base;

    function RequestFilter(&$base) {
        $this->base =& $base;
    }

    function hasParameters() {
        return $this->base->hasParameters();
    }

    function hasParameter($name) {
        return $this->base->hasParameter($name);
    }

    function getParameter($name) {
        return $this->base->getParameter($name);
    }

    function hasPostProperty($name) {
        return $this->base->hasPostProperty($name);
    }

    function getPostProperty($name) {
        return $this->base->getPostProperty($name);
    }

    function exportPostProperties() {
        return $this->base->exportPostProperties();
    }

    function getMethod() {
        return $this->base->getMethod();
    }

    function getPathInfo() {
        return $this->base->getPathInfo();
    }
}

?>