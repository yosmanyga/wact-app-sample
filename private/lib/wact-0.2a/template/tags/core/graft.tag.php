<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @version $Id: graft.tag.php,v 1.3 2004/11/18 04:22:47 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------

/**
* Register the tag
*/
/*
$taginfo = new TagInfo('core:graft', 'CoreGraftTag');
$taginfo->setCompilerAttributes(array('placeholder'));
TagDictionary::registerTag($taginfo, __FILE__);
*/

/**
* Grafts the tag contents into the component tree as children of the
* indicated <core:placeholder> component.  Unlike the <core:wrap> tag,
* the contents are actually placed at the desired point in the tree and
* hence calls to CheckNestingLevel for these components work.
* @see http://sourceforge.net/mailarchive/forum.php?thread_id=5031269&forum_id=35579
* @see http://wact.sourceforge.net/index.php/CoreGraftTag
* @access protected
* @package WACT_TAG
* @todo EXPERIMENTAL - not yet active tag
*/
class CoreGraftTag extends SilentCompilerDirectiveTag {
    /**
     * Reference to <core:placeholder> component where contents
     * will be grafted
     */
    var $graftpoint = null;

    /**
     * Did we already search for the graft point?
     */
    var $graftsearched = false;

    function _findGraftPoint() {
        if (!$this->graftsearched) {
            $this->graftsearched = true;
            $placeholder = $this->getAttribute('placeholder');
            if (empty($placeholder)) {
            RaiseError('compiler', 'MISSINGREQUIREATTRIBUTE', array(
            'tag' => $this->tag,
            'attribute' => 'placeholder', 
            'file' => $this->SourceFile,
            'line' => $this->StartingLineNo));
            }
            // Locate specified placeholder node
            // TODO: throw error if placeholder not found?
            // NOTE: added 'findRoot' method to compilercomponent but
            //  could probably just do findParentByClass('template');
            $root =& $this->findRoot();    
            $this->graftpoint =& $root->findChild($placeholder);
        }
        return (!empty($this->graftpoint));
    }    

    /**
    * Adds a child component, by reference, to the array of children
    * of the placeholder node (overrides parent class method)
    * @param object instance of a compile time component
    * @return void
    * @access protected
    */
    function addChild(&$child) {
        if ($this->_findGraftPoint()) {
            $this->graftpoint->addChild($child);
        }
    }

}
?>