<?php
/**
 * @package WACT_UTIL
 * @version $Id: html_entity_decode.php,v 1.2 2004/11/15 16:21:10 jeffmoore Exp $
 * Provides a PHP implementations of html_entity_decode(),
 * only available since PHP 4.3.x, allowing older versions to use them.
 */
/**
 * @see http://www.php.net/html_entity_decode
 * @see http://wact.sourceforge.net/index.php/PHPCompatibility
 * @todo Implement quote styles (?)
 * @param string to encode HTML entities for
 * @param string (default=NULL) - unused
 * @return string contents of file
 * @access public
 */
function html_entity_decode($str, $style=NULL) {
    static $table;
    if (!isset($table)) {
        $table = array_flip(get_html_translation_table(HTML_ENTITIES));
    }
	return strtr($str, $table);
}
?>