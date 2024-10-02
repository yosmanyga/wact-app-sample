<?php

TagDictionary::registerTag(new TagInfo('html:link', 'HtmlLinkTag'), __FILE__);

class HtmlLinkTag extends CompilerDirectiveTag {

    var $CheckingCode;

	function preGenerate(&$code) {
		parent::preGenerate($code);
		
		$code->registerInclude(WACT_ROOT . 'util/uri.inc.php');

		$urlvar = $code->getTempVariable();

        if ($this->hasAttribute('param')) {
            $paramvar = $code->getTempVariable();
            $code->writePHP('$' . $paramvar . ' = ' . $this->getDataSourceRefCode() . '->get(\'' . $this->getAttribute('param') . '\');');

            $code->writePHP('$' . $urlvar . ' = setUriParameter($GLOBALS[\'FrontController\']->getRealPath("');
            $code->writePHP($this->getAttribute('url'));
    		$code->writePHP('"), "' . $this->getAttribute('param'));
    		$code->writePHP('", $' . $paramvar . ');');
        } else {
            $code->writePHP('$' . $urlvar . ' = $GLOBALS[\'FrontController\']->getRealPath("');
            $code->writePHP($this->getAttribute('url'));
            $code->writePHP('");');
        }
        
        $code->writeHTML('<a href="');
        $code->writePHP('echo $' . $urlvar . ';');
        $code->writeHTML('">');
	}

	function postGenerate(&$code) {
        $code->writeHTML('</a>');
		parent::postGenerate($code);
	}
		
}
?>