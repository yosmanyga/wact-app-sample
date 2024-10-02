<?php
require_once WACT_ROOT . 'db/db.inc.php';

class SRTMModel {
	function init() {
		session_start();
	}
	function setCurrentUsername($username) {
		SRTMModel::init();
		$_SESSION['username'] = $username;
		return true;
	}
	function getCurrentUsername() {
		SRTMModel::init();
		return $_SESSION['username'];
	}
	
	function setCurrentUserGroupid($usergroupid) {
		SRTMModel::init();
		$_SESSION['usergroupid'] = $usergroupid;
		return true;
	}
	function getCurrentUserGroupid() {
		SRTMModel::init();
		return $_SESSION['usergroupid'];
	}
	
	function setCurrentMutationname($mutationname) {
		SRTMModel::init();
		$_SESSION['mutationname'] = $mutationname;
		return true;
	}
	function getCurrentMutationname() {
		SRTMModel::init();
		return $_SESSION['mutationname'];
	}	
	
	function isMutableGroup($id) {
		if (DBC::getOneValue('SELECT mutable FROM groups WHERE id = '.DBC::MakeLiteral($id)) == 'Y') {
			return true;
		} else {
			return false;
		}
	}

	function setNow() {
		SRTMModel::init();
		return $_SESSION['now'] = date('Y-m-d H:i:s');
		//return $_SESSION['now'] = '2006-01-17 08:30:01';
	}
	function getNow() {
		SRTMModel::init();
		return $_SESSION['now'];
	}		
	function setNowDate() {
		SRTMModel::init();
		return $_SESSION['nowDate'] = date('Y-m-d');
	}
	function getNowDate() {
		SRTMModel::init();
		return $_SESSION['nowDate'];
	}	
	function setNowTime() {
		SRTMModel::init();
		return $_SESSION['nowTime'] = date('H:i:s');
	}
	function getNowTime() {
		SRTMModel::init();
		return $_SESSION['nowTime'];
	}	
	
	
}
?>