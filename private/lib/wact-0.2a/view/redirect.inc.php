<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_CONTROLLERS
* @version $Id: redirect.inc.php,v 1.1 2004/11/07 23:12:32 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------

/**
* Redirect to a specific controller
* @abstract
* @package WACT_VIEW
*/
class RedirectView {

    var $path;
    
    /**
    * @param string name of the controller to redirect to
    */
    function RedirectView($path = NULL) {
        $this->path = $path;
    }

    /**
    * Output the View
    * @return void
    * @access public
    */
    function display(&$controller, &$request, &$responseModel) {
        $url = $controller->getRealPath($this->path);
        header('Location: ' . $url);
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
