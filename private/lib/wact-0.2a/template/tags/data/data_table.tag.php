<?php
//--------------------------------------------------------------------------------
// Copyright 2004 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_TAG
* @author	Jason E. Sweat <jsweat_php AT yahoo DOT com>
* @version $Id: data_table.tag.php,v 1.53 2004/11/18 04:22:48 jeffmoore Exp $
*/
//--------------------------------------------------------------------------------
if (!function_exists('clone_obj')) {
	require WACT_ROOT . 'util/phpcompat/clone.php';
}

/**
* Register tags
*/
TagDictionary::registerTag(new TagInfo('data:TABLE', 'DataTableTag'), __FILE__);
TagDictionary::registerTag(new TagInfo('data:GROUP', 'DataGroupTag'), __FILE__);
TagDictionary::registerTag(new TagInfo('data:COLUMN', 'DataColumnTag'), __FILE__);
TagDictionary::registerTag(new TagInfo('data:CELL', 'DataCellTag'), __FILE__);
TagDictionary::registerTag(new TagInfo('data:HEADER', 'DataHeaderTag'), __FILE__);
TagDictionary::registerTag(new TagInfo('data:FOOTER', 'DataFooterTag'), __FILE__);
TagDictionary::registerTag(new TagInfo('data:DEFAULT', 'DataDefaultTag'), __FILE__);

/**
* The compile time component to output a result set as an HTML table
* @author	Jason E. Sweat <jsweat_php AT yahoo DOT com>
* @see http://wact.sourceforge.net/index.php/ResultsetTableTag
* @access protected
* @package WACT_TAG
*/
class DataTableTag extends ServerDataComponentTag {
	/**
	* File to include at runtime
	* @var string path to runtime component relative to WACT_ROOT
	* @access private
	*/
	var $runtimeIncludeFile = 'template/components/data/data_table.inc.php';
	/**
	* Name of runtime component class
	* @var string
	* @access private
	*/
	var $runtimeComponentName = 'DataTableComponent';
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function preGenerate(&$code) {
		parent::preGenerate($code);
		//$code->writePHP($this->getComponentRefCode() . '->prepare();');

		$att_list = '';
		foreach ($this->getTableAttributeMap() as $key => $value) {
			if ($att = $this->getAttribute($key)) {
				$att_list .= ' '.$value.'="'.$att.'"';
			}
		}
		//row attributes
		$row_att_list = '';
		foreach ($this->getRowAttributeMap() as $key => $value) {
			if ($row_att = $this->getAttribute($key)) {
				$row_att_list .= ' '.$value.'="'.$row_att.'"';
			}
		}
		if (trim($row_att_list)) {
			$code->writePHP($this->getComponentRefCode().'->setRowAttrib(');
			$code->writePHPLiteral($row_att_list);
			$code->writePHP(');');
		}
		
		//start output
		$code->writeHTML("<table $att_list >\n");
		$code->writePHP('if ('.$this->getDataSourceRefCode().'->next()) {'); 
			// write column setup
			$columns = $this->findChildrenByClass('DataColumnTag');
			foreach ($columns as $colkey => $colval) {
				$columns[$colkey]->preGenerate($code);
				// cell attributes
				$col_att_list = '';
				foreach ($this->getCellAttributeMap() as $key => $value) {
					if ($col_att = $columns[$colkey]->getAttribute($key)) {
						$col_att_list .= ' '.$value.'="'.$col_att.'"';
					}
				}
				if (trim($col_att_list)) {
					$code->writePHP($columns[$colkey]->getComponentRefCode().'->setAttrib(');
					$code->writePHPLiteral($col_att_list);
					$code->writePHP(');');
				}			
			}
			$key = $code->getTempVarRef();
			$val = $code->getTempVarRef();
			// supress automatic column generation if autogen=FALSE
			if ($this->getBoolAttribute('autogen', TRUE)) {
				$code->writePHP('foreach ('.$this->getDataSourceRefCode().'->export() as '.$key.' => '.$val.') {');
					$code->writePHP($this->getComponentRefCode().'->addColumn("'.$key.'");');
				$code->writePHP('}');
			}
			// group setup
			$groups = $this->findChildrenByClass('DataGroupTag');
			foreach ($groups as $grpkey => $obj) {
				$groups[$grpkey]->preGenerate($code);
			}
			//table wide header
			if ($header =& $this->findImmediateChildByClass('DataHeaderTag')) {
				$code->writeHTML('<tr><th colspan="');
				$code->writePHP('echo '.$this->getComponentRefCode().'->colCount(),"\">";');
				$header->generateNow($code);
				$code->writeHTML('</th></tr>');
			}
			//column/group headers
			$code->writePHP($this->getComponentRefCode().'->genHeaders('.$this->getDataSourceRefCode().', $root);');
		$code->writePHP('}do{');
	}
	
	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function generateContents(&$code) {
		//uncommenting this would cause comments inside the table to be output
		//as raw text in the HTML
		//parent::generateContents($code);
		
		//create cell content
		$code->writePHP($this->getComponentRefCode().'->renderRow($root);');
	}

	/**
	* @param CodeWriter
	* @return void
	* @access protected
	*/
	function postGenerate(&$code) {
		$code->writePHP('}while ('.$this->getDataSourceRefCode().'->next());'); 
		//column footers
		$code->writePHP($this->getComponentRefCode().'->genColFooters('.$this->getDataSourceRefCode().', $root);');
		$emptyChild =& $this->findChildByClass('DataDefaultTag');
		if ($emptyChild) {
			$code->writePHP('if (!'.$this->getComponentRefCode().'->RowCount) { ');
			$code->writeHTML('<tr><td>');
			$emptyChild->generateNow($code);
			$code->writeHTML('</td></tr>');
			$code->writePHP('}');
		}
		//table wide footer
		if ($footer =& $this->findImmediateChildByClass('DataFooterTag')) {
			$cell_type = ($footer->hasAttribute('heading')) ? 'th' : 'td';
			$code->writeHTML('<tr><'.$cell_type.' colspan="');
			$code->writePHP('echo '.$this->getComponentRefCode().'->colCount(),"\">";');
			$footer->generateNow($code);
			$code->writeHTML('</'.$cell_type.'></tr>');
		}
		
		$code->writeHTML("</table>\n");
		parent::postGenerate($code);
	}

	/**
	* return map of table attributes
	* @return array
	* @access private
	* @see http://www.w3.org/TR/html4/struct/tables.html#h-11.2.1
	*/
	function getTableAttributeMap() {
		//table attributes w3c 
		return array(
			 'id'			=> 'id'
			,'tableclass' 	=> 'class'
			,'border'		=> 'border'
			,'width'		=> 'width'
			,'summary'		=> 'summary'
			,'frame'		=> 'frame'
			,'rules'		=> 'rules'
			,'cellspacing'	=> 'cellspacing'
			,'cellpadding'	=> 'cellpadding'
			,'lang'			=> 'lang'
			,'dir'			=> 'dir'
			,'title'		=> 'title'
			,'style'		=> 'style'
			,'onclick'		=> 'onclick'
			,'ondblclick'	=> 'ondblclick'
			,'onmousedown'	=> 'onmousedown'
			,'onmouseup'	=> 'onmouseup'
			,'onmouseover'	=> 'onmouseover'
			,'onmousemove'	=> 'onmousemove'
			,'onmouseout'	=> 'onmouseout'
			,'onkeypress'	=> 'onkeypress'
			,'onkeydown'	=> 'onkeydown'
			,'onkeyup'		=> 'onkeyup'
			,'bgcolor'		=> 'bgcolor'
			);
	}
	/**
	* return map of row attributes
	* @return array
	* @access private
	* @see http://www.w3.org/TR/html4/struct/tables.html#h-11.2.5
	*/
	function getRowAttributeMap() {
		//tr attributes per w3c 
		return array(
 			//class and id skipped intentionally
			 'rowstyle'			=> 'style'
			,'rowtitle'			=> 'title'
			,'rowlang'			=> 'lang'
			,'rowdir' 			=> 'dir'
			,'rowonclick'    	=> 'onclick'    
			,'rowondblclick' 	=> 'ondblclick' 
			,'rowonmousedown'	=> 'onmousedown'
			,'rowonmouseup'  	=> 'onmouseup'  
			,'rowonmouseover'	=> 'onmouseover'
			,'rowonmousemove'	=> 'onmousemove'
			,'rowonmouseout' 	=> 'onmouseout' 
			,'rowonkeypress' 	=> 'onkeypress' 
			,'rowonkeydown'  	=> 'onkeydown'  
			,'rowonkeyup'    	=> 'onkeyup' 
			,'align'     		=> 'align'  
			,'char'      		=> 'char'   
			,'charoff'   		=> 'charoff'
			,'valign'			=> 'valign'
			);
	}
	/**
	* return map of cell attributes
	* @return array
	* @access private
	* @see http://www.w3.org/TR/html4/struct/tables.html#h-11.2.6
	*/
	function getCellAttributeMap() {
		// td/th attributes per w3c 
		return array(
			 //class, id, rowspan and colspan skipped intentionally
			 'style'			=> 'style'
			,'title'			=> 'title'
			,'lang'				=> 'lang'
			,'dir' 				=> 'dir'
			,'onclick'    		=> 'onclick'    
			,'ondblclick' 		=> 'ondblclick' 
			,'onmousedown'		=> 'onmousedown'
			,'onmouseup'  		=> 'onmouseup'  
			,'onmouseover'		=> 'onmouseover'
			,'onmousemove'		=> 'onmousemove'
			,'onmouseout' 		=> 'onmouseout' 
			,'onkeypress' 		=> 'onkeypress' 
			,'onkeydown'  		=> 'onkeydown'  
			,'onkeyup'    		=> 'onkeyup' 
			,'align'     		=> 'align'  
			,'char'      		=> 'char'   
			,'charoff'   		=> 'charoff'
			,'valign'			=> 'valign'
			,'abbr'				=> 'abbr'		   
			,'axis'				=> 'axis'		   
			,'headers'			=> 'headers'	
			,'scope'			=> 'scope'	  
			);
	}
}


/**
* The compile time component representing a group of columns
* @author Jason E. Sweat
* @see http://wact.sourceforge.net/index.php/ResultsetTableTag
* @access protected
* @package WACT_TAG
*/
class DataGroupTag extends ServerComponentTag {
	/**
	* File to include at runtime
	* @var string path to runtime component relative to WACT_ROOT
	* @access private
	*/
	var $runtimeIncludeFile = 'template/components/data/data_table.inc.php';
	/**
	* Name of runtime component class
	* @var string
	* @access private
	*/
	var $runtimeComponentName = 'DataGroupComponent';
	/**
	* @return void
	* @access protected
	*/
	function CheckNestingLevel() {
		if ( !is_a($this->parent, 'DataTableTag')
			) {
            RaiseError('compiler', 'MISSINGENCLOSURE', array(
                'tag' => $this->tag,
                'EnclosingTag' => 'data:TABLE',
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
		//code to register columns here
		$columns = $this->findChildrenByClass('DataColumnTag');
		foreach ($columns as $key => $obj) {
			$code->writePHP($columns[$key]->getComponentRefCode().'->registerGroup('.$this->getComponentRefCode().');');
		}
		if (count($columns)) {
		$DataTable =& $this->findParentByClass('DataTableTag');
			if ($DataTable) {
				$code->writePHP($DataTable->getComponentRefCode().'->registerGroup('.$this->getComponentRefCode().');');
				$funct = $code->getTempVarRef();
				if ($header =& $this->findChildByClass('DataHeaderTag')) {
					$header_code = clone_obj($code);
					$header_code->code = ''; 
					$header->generateNow($header_code);
					$header_code->writePHP('echo "";'); //make sure you end in php mode or the create_function will fail
					$code->writePHP($funct.' = create_function(\'&'.$DataTable->getDataSourceRefCode().', &$root\', ');
					$code->writePHPLiteral($header_code->code);
					foreach($header_code->includeList as $file) { $code->registerInclude($file); }
					unset($header_code);
					$code->writePHP(');');
					$code->writePHP($this->getComponentRefCode().'->setHeaderFunct('.$funct.');');
				}
			}
		}
	}
}

/**
* The compile time component representing common attributes for a column of table cells
* @author Jason E. Sweat
* @see http://wact.sourceforge.net/index.php/ResultsetTableTag
* @access protected
* @package WACT_TAG
*/
class DataColumnTag extends ServerComponentTag {
	/**
	* File to include at runtime
	* @var string path to runtime component relative to WACT_ROOT
	* @access private
	*/
	var $runtimeIncludeFile = 'template/components/data/data_table.inc.php';
	/**
	* Name of runtime component class
	* @var string
	* @access private
	*/
	var $runtimeComponentName = 'DataColumnComponent';
	/**
	* @return void
	* @access protected
	*/
	function CheckNestingLevel() {
		if ( !(is_a($this->parent, 'DataTableTag')
			|| is_a($this->parent, 'DataGroupTag'))) {
            RaiseError('compiler', 'MISSINGENCLOSURE', array(
                'tag' => $this->tag,
                'EnclosingTag' => 'data:TABLE',
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
		if ( !$this->getAttribute('name')) {
            RaiseError('compiler', 'MISSINGREQUIREATTRIBUTE', array(
                'tag' => $this->tag,
                'attribute' => 'name',
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
		} else {

			parent::preGenerate($code);
			if ($this->hasAttribute('hide')) {
				$code->writePHP($this->getComponentRefCode().'->hide();');
			}
			if ($this->hasAttribute('heading')) {
				$code->writePHP($this->getComponentRefCode().'->outputTh();');
			}
			if ($this->hasAttribute('label')) {
				$code->writePHP($this->getComponentRefCode().'->setLabel(');
				$code->writePHPLiteral($this->getAttribute('label'));
				$code->writePHP(');');
			}
			$DataTable =& $this->findParentByClass('DataTableTag');
			if ($DataTable) {
				$code->writePHP($DataTable->getComponentRefCode().'->registerColumn(');
				$code->writePHPLiteral($this->getAttribute('name'));
				$code->writePHP(','.$this->getComponentRefCode().');');
				$funct = $code->getTempVarRef();
				if ($cell =& $this->findChildByClass('DataCellTag')) {
					$cell_code = clone_obj($code);
					$cell_code->code = ''; 
					$cell->generateNow($cell_code);
					$cell_code->writePHP('echo "";'); //make sure you end in php mode or the create_function will fail
					$code->writePHP($funct.' = create_function(\''.$DataTable->getDataSourceRefCode().', $root\', ');
					$code->writePHPLiteral($cell_code->code);
					// this is a no no, accessing a private var, but we need any new includes :(
					foreach($cell_code->includeList as $file) { $code->registerInclude($file); }
					unset($cell_code);
					$code->writePHP(');');
					$code->writePHP($this->getComponentRefCode().'->setRenderFunct('.$funct.');');
				}
				if ($header =& $this->findChildByClass('DataHeaderTag')) {
					$head_att_list = '';
					foreach ($DataTable->getCellAttributeMap() as $key => $value) {
						if ($head_att = $header->getAttribute($key)) {
							$head_att_list .= ' '.$value.'="'.$head_att.'"';
						}
					}
					if (trim($head_att_list)) {
						$code->writePHP($this->getComponentRefCode().'->setHeaderAttrib(');
						$code->writePHPLiteral($head_att_list);
						$code->writePHP(');');
					}
					$header_code = clone_obj($code);
					$header_code->code = ''; 
					$header->generateNow($header_code);
					$header_code->writePHP('echo "";'); //make sure you end in php mode or the create_function will fail
					$code->writePHP($funct.' = create_function(\''.$DataTable->getDataSourceRefCode().', $root\', ');
					$code->writePHPLiteral($header_code->code);
					foreach($header_code->includeList as $file) { $code->registerInclude($file); }
					unset($header_code);
					$code->writePHP(');');
					$code->writePHP($this->getComponentRefCode().'->setHeaderFunct('.$funct.');');
				}
				$footers = $this->findChildrenByClass('DataFooterTag');
				foreach ($footers as $key => $obj) {
					$cell_type = ($footers[$key]->hasAttribute('heading')) ? 'th' : 'td';
					$wrap_start = $wrap_end = '';
					if ($footers[$key]->hasAttribute('bold')
								||$footers[$key]->hasAttribute('b')) {
						$wrap_start = '<b>';
						$wrap_end = '</b>';
					}
					$footer_code = clone_obj($code); 
					$footer_code->code = '';
					$footer_code->writePHP(' echo "<'.$cell_type.'$attribs>'.$wrap_start.'";');
					$footers[$key]->generateNow($footer_code);
					$footer_code->writePHP(' echo "'.$wrap_end.'</'.$cell_type.'>";');
					$code->writePHP($funct.' = create_function(\''.$DataTable->getDataSourceRefCode().', $root, $attribs\', ');
					$code->writePHPLiteral($footer_code->code);
					foreach($footer_code->includeList as $file) { $code->registerInclude($file); }
					unset($footer_code);
					$code->writePHP(');');
					$code->writePHP($this->getComponentRefCode().'->addFooterFunct('.$funct.');');
				}
			}
		}
	}
}

/**
* The compile time component to output when the DataSet is empty
* @author	Jason E. Sweat < jsweat_php AT yahoo DOT com >
* @see http://wact.sourceforge.net/index.php/ResultsetTableTag
* @access protected
* @package WACT_TAG
*/
class DataDefaultTag extends SilentCompilerDirectiveTag {
	/**
	* @return void
	* @access protected
	*/
	function CheckNestingLevel() {
		if ( !is_a($this->parent, 'DataTableTag') ) {
            RaiseError('compiler', 'MISSINGENCLOSURE', array(
                'tag' => $this->tag,
                'EnclosingTag' => 'data:TABLE',
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
		}
	}
}

/**
* The compile time component for a cell template
* @author Jason E. Sweat
* @see http://wact.sourceforge.net/index.php/ResultsetTableTag
* @access protected
* @package WACT_TAG
*/
class DataCellTag extends SilentCompilerDirectiveTag {
	/**
	* @return void
	* @access protected
	*/
	function CheckNestingLevel() {
		if ( !is_a($this->parent, 'DataColumnTag') ) {
            RaiseError('compiler', 'MISSINGENCLOSURE', array(
                'tag' => $this->tag,
                'EnclosingTag' => 'data:TABLE',
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
	}	
}

/**
* The compile time component to output a heading for a column, group or table
* @author Jason E. Sweat
* @see http://wact.sourceforge.net/index.php/ResultsetTableTag
* @access protected
* @package WACT_TAG
*/
class DataHeaderTag extends SilentCompilerDirectiveTag {
	/**
	* @return void
	* @access protected
	*/
	function CheckNestingLevel() {
		if ( !(is_a($this->parent, 'DataTableTag') 
			|| is_a($this->parent, 'DataGroupTag')
			|| is_a($this->parent, 'DataColumnTag')
			)) {
            RaiseError('compiler', 'MISSINGENCLOSURE', array(
                'tag' => $this->tag,
                'EnclosingTag' => 'data:TABLE',
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
		}
	}
}

/**
* The compile time component to output a footer for a column or table
* @author Jason E. Sweat
* @see http://wact.sourceforge.net/index.php/ResultsetTableTag
* @access protected
* @package WACT_TAG
*/
class DataFooterTag extends SilentCompilerDirectiveTag {
	/**
	* @return void
	* @access protected
	*/
	function CheckNestingLevel() {
		if ( !(is_a($this->parent, 'DataTableTag') 
			|| is_a($this->parent, 'DataColumnTag')
			)) {
            RaiseError('compiler', 'MISSINGENCLOSURE', array(
                'tag' => $this->tag,
                'EnclosingTag' => 'data:TABLE',
                'file' => $this->SourceFile,
                'line' => $this->StartingLineNo));
		}
	}
}


?>
