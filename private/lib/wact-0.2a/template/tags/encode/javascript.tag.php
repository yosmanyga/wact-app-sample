<?php
//--------------------------------------------------------------------------------
// Copyright 2004 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
 * @package WACT_TAG
 * @version $Id: javascript.tag.php,v 1.5 2004/11/18 04:22:48 jeffmoore Exp $
 */
//--------------------------------------------------------------------------------
/**
 * Register the tag
 */
TagDictionary::registerTag(new TagInfo('encode:javascript', 'EncodeJavascriptTag'), __FILE__);

/**
 * JavaScript-encodes the contents
 * @see http://wact.sourceforge.net/index.php/EncodeJavascriptTag
 * @access protected
 * @package WACT_TAG
 */
class EncodeJavascriptTag extends CompilerDirectiveTag
{
    /**
     * @return void
     * @access protected
     */
    function CheckNestingLevel() {
        if ($this->findParentByClass('EncodeJavascriptTag')) {
            RaiseError('compiler', 'BADSELFNESTING', array(
                'tag'  => $this->tag,
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
        }
    }

    /**
     * @param CodeWriter
     * @return void
     * @access protected
     */
    function preGenerate(&$code) {
		parent::preGenerate($code);
        $code->writePHP('ob_start();');
    }

    /**
     * @param CodeWriter
     * @return void
     * @access protected
     */
    function generateContents(&$code) {
        parent::generateContents($code);
    }

    /**
     * @param CodeWriter
     * @return void
     * @access protected
     */
    function postGenerate(&$code) {
        $contents = $code->getTempVariable();
        $hexencode = $code->getTempVariable();
        $arr = $code->getTempVariable();

        $code->writePHP('if (!function_exists("str_split")) {');
        $code->writePHP('require_once WACT_ROOT . "/util/phpcompat/str_split.php"; }');

        $code->writePHP('function '.$hexencode.'($char) {');
        $code->writePHP('  return \'%\' . bin2hex($char);');
        $code->writePHP('}');
        /*
        $code->writePHP('function '.$hexencode.'($string) {');
        $code->writePHP('  $encoded = \'\';');
        $code->writePHP('  for ($x=0, $size=strlen($string); $x < $size; $x++) {');
        $code->writePHP('    $encoded .= \'%\' . bin2hex($string[$x]);');
        $code->writePHP('  }');
        $code->writePHP('  return $encoded;');
        $code->writePHP('}');
        */

        $code->writePHP('$' . $contents . ' = ob_get_contents();');
        $code->writePHP('ob_end_clean();');

        //$code->writePHP('$' . $contents . ' = '.$hexencode.'($'. $contents.');');
        $code->writePHP('$' . $arr . ' = str_split($'. $contents.');');
        $code->writePHP('if (is_array($' . $arr .')) {');
        $code->writePHP('  $'. $contents . ' = implode("", array_map("'.$hexencode.'", $'.$arr.'));');
        $code->writePHP('}');

        $code->writeHTML('<script type="text/javascript" language="javascript">document.write(unescape(\'');
        $code->writePHP('echo $' . $contents.';');
        $code->writeHTML('\'))</script>');
		parent::postGenerate($code);
    }
}
?>
