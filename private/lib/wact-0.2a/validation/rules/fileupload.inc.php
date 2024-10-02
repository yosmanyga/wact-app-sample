<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_VALIDATION
* @version $Id: fileupload.inc.php,v 1.3 2004/11/16 01:55:37 jeffmoore Exp $
*/

require_once WACT_ROOT . 'validation/rule.inc.php';

/**
* Replaces RequiredRule for FileUploads.
* @see http://wact.sourceforge.net/index.php/FileUploadRequiredRule
* @access public
* @package WACT_VALIDATION
*/
class FileUploadRequiredRule extends SingleFieldRule {
    /**
    * Constructs RequiredRule
    * @param string fieldname to validate
    * @access public
    */
    function FileUploadRequiredRule($fieldname) {
        parent :: SingleFieldRule($fieldname);
    }
    /**
    * Perform validation
    * @param DataSource - Data to validate
    * @param ErrorList
    * @return boolean
    * @access public
    */
    function validate(&$DataSource, &$ErrorList) {
        $value = $DataSource->get($this->fieldname);

        if (empty($value['name'])) {
            $ErrorList->addError($this->Group, 'MISSING', 
                                 array('Field' => $this->fieldname));
            /* nasty hack - need to set this so SingleFieldRule::validate()
             * doesn't process any more validations on this field */
            $DataSource->set($this->fieldname, '');
            return FALSE;
        }
        return TRUE;
    }
}

/**
* Check that a partial file upload error didn't occur. Will only work with PHP
* >= 4.2.0
* @see http://wact.sourceforge.net/index.php/FileUploadPartialRule
* @access public
* @package WACT_VALIDATION
*/
class FileUploadPartialRule extends SingleFieldRule {
    /**
    * Constructs FileUploadPartialRule
    * @param string fieldname to validate
    * @access public
    */
    function FileUploadPartialRule($fieldname) {
        parent :: SingleFieldRule($fieldname);
    }
    /**
    * Check that an uploaded file was fully uploaded.
	* @param string value to validate
	* @access protected
	* @return void
	*/
    function Check($value) {
        if (version_compare(phpversion(), '4.2.0', '>=')) {
            if ($value['error'] == UPLOAD_ERR_PARTIAL) {
                $this->Error('FILEUPLOAD_PARTIAL');
            }
        }
    }
}

/**
* Check that the size of an uploaded file was not too large.
* @see http://wact.sourceforge.net/index.php/FileUploadMaxSizeRule
* @access public
* @package WACT_VALIDATION
*/
class FileUploadMaxSizeRule extends SingleFieldRule {
    var $maxsize;
    
	/**
	* Constructs a FileUploadMaxSizeRule
	* @param string fieldname to validate
	* @param int max allowable size (in bytes) of the uploaded file
	* @access public
	*/
	function FileUploadMaxSizeRule($fieldname, $maxsize = NULL) {
		parent :: SingleFieldRule($fieldname);
        $this->maxsize = $maxsize;
	}

    /**
    * Check that the uploaded file was smaller than a programmer defined size;
    * then (if PHP >= 4.2.0) the value (if any) set in the form MAX_FILE_SIZE
    * and the php.ini upload_max_filesize setting;
	* @param string value to validate
	* @access protected
	* @return void
	*/
    function Check($value) {
        if (! is_null($this->maxsize) &&
            $value['size'] > (int) $this->maxsize) {
            $this->Error('FILEUPLOAD_MAX_USER_SIZE',
                         array('maxsize' =>
                               $this->_sizeToHuman($this->maxsize)));
            return;
        }

        if (version_compare(phpversion(), '4.2.0', '>=') &&
            ($value['error'] == UPLOAD_ERR_INI_SIZE ||
             $value['error'] == UPLOAD_ERR_FORM_SIZE)) {
            $this->Error('FILEUPLOAD_MAX_SIZE');
        }
    }

    /**
    * Utility function returns readable filesizes for the error message.
    * @param int (optional) filesize
    * @access private
    * @return string human readable filesize
    */
    function _sizeToHuman($filesize = 0) {
        if ($filesize < 1024) {
            $size = $filesize . "B";
        } else if ($filesize >= 1024 &&
            $filesize < 1048576) {
            $size = sprintf("%.2fKB", $filesize / 1024);
        } else {
            $size = sprintf("%.2fMB", $filesize / 1048576);
        }
        return $size;        
    }
}

/**
* Check that an uploaded file has an acceptable mime type
* @see http://wact.sourceforge.net/index.php/FileUploadMimeTypeRule
* @access public
* @package WACT_VALIDATION
*/
class FileUploadMimeTypeRule extends SingleFieldRule {
    var $mimetypes = array();

	/**
	* Constructs a FileUploadMimeTypeRule
	* @param string fieldname to validate
	* @param array of acceptable mimetypes
	* @access public
	*/
	function FileUploadMimeTypeRule($fieldname, $mimetypes = array()) {
		parent :: SingleFieldRule($fieldname);
        if (is_array($mimetypes)) {
            $this->mimetypes = $mimetypes;
        }
	}

	/**
	* Check that the mimetype of the uploaded file appears in the mimetypes
	* array. Some browsers won't provide a mimetype, so we can only check when
	* it is provided.
	* @param string value to validate
	* @access protected
	* @return void
	*/
    function Check($value) {
        if (! empty($value['type']) &&
            ! in_array($value['type'], $this->mimetypes)) {
            $this->Error('FILEUPLOAD_MIMETYPE',
                         array('mimetypes' => implode(', ', $this->mimetypes)));
        }
	}
}
?>