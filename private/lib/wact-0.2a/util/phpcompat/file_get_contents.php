<?php
/**
 * @package WACT_UTIL
 * @version $Id: file_get_contents.php,v 1.1 2004/03/04 22:15:56 quipo Exp $
 * Provides a PHP implementations of file_get_contents(),
 * only available since PHP 4.3.x, allowing older versions to use them.
 */
/**
 * @see http://www.php.net/file_get_contents
 * @see http://wact.sourceforge.net/index.php/PHPCompatibility
 * @param string filename
 * @return string contents of file
 * @access public
 */
function file_get_contents($filename) {
	$fd = fopen("$filename", 'rb');
	$content = fread($fd, filesize($filename));
	fclose($fd);
	return $content;
}
?>