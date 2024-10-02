<?php
/**
 * @package WACT_UTIL
 * @version $Id: scandir.php,v 1.2 2004/05/31 16:57:42 quipo Exp $
 * Provides a PHP implementations of scandir(),
 * only available since PHP 5.0.x, allowing older versions to use them.
 */
/**
 * @see http://www.php.net/scandir
 * @see http://wact.sourceforge.net/index.php/PHPCompatibility
 * @param string directory to scan
 * @param int sorting order (0=asc, 1=desc)
 * @return float float variable
 * @access public
 */
function scandir($directory, $sorting_order=0) {
	$dh  = opendir($directory);
    if ($dh === false) {
        return false;
    }
    $files = array();
    while (false !== ($filename = readdir($dh))) {
        $files[] = $filename;
    }
    closedir($dh);
    if ($sorting_order) {
        rsort($files);
    } else {
        sort($files);
    }
    return $files;
}
?>