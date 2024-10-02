<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------

function ApplyDefault($value, $default) {
    if (empty($value) && $value !== "0" && $value !== 0) {
        return $default;
    } else {
        return $value;
    }
}
?>