<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_CONTROLLERS
* @version $Id: redirecturl.inc.php,v 1.1 2004/11/07 23:12:32 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------

/**
* Basic redirection view
* @abstract
* @package WACT_VIEW
*/
class RedirectUrlView {

    var $url;
    
    /**
    * @param string name of the Url to redirect to
    */
    function RedirectUrlView($url = NULL) {
        $this->url = $url;
    }

    /**
    * Output the View
    * @return void
    * @access public
    */
    function display(&$controller, &$request, &$responseModel) {
        header('Location: ' . $this->url);
        ?>
        <html>
        <head>
        <meta http-equiv="refresh" content="url=<?php $this->url ?>; 0">
        </head>
        </html>
        <?php
    }

}

?>
