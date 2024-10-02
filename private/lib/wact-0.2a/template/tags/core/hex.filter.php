<?php
//--------------------------------------------------------------------------------
// Copyright 2004 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @author  Lorenzo Alberton < l DOT alberton AT tiscalinet DOT it >
* @version $Id: hex.filter.php,v 1.2 2004/11/18 04:22:47 jeffmoore Exp $
*/

FilterDictionary::registerFilter(
    new FilterInfo('hex', 'HexFilter', 0, 0), __FILE__);

class HexFilter extends CompilerFilter {

    /**
    * Return this value as a PHP value
    * @return String
    * @access public
    */
    function getValue() {
        if ($this->isConstant()) {
            return str_replace('&#x;', '', preg_replace("/(.)*/Uimse", "'&#x'.bin2hex('\\1').';'", $this->base->getValue()));
        } else {
            RaiseError('compiler', 'UNRESOLVED_BINDING');
        }
    }

    /**
    * Generate the code to read the data value at run time
    * Must generate only a valid PHP Expression.
    * @param CodeWriter
    * @return void
    * @access protected
    */
    function generateExpression(&$code) {
        $code->writePHP('str_replace(\'&#x;\', \'\', preg_replace("/(.)*/Uimse", "\'&#x\'.bin2hex(\'\\\\1\').\';\'", ');
        $this->base->generateExpression($code);
        $code->writePHP('))');
    }

}

?>