<?php
require_once WACT_ROOT . 'controller/pathinfo.inc.php';

class AuthorizationDispatchController extends PathInfoDispatchController {

	function AuthorizationDispatchController() {
		parent::PathInfoDispatchController();
		
		//$this->registerOnActivateListener(new Delegate($this, 'login'));
		
		$this->addChild('privilege',	new Handle(APP_ROOT . 'authorization/privilege.page.php|PrivilegePageController'));
		$this->addChild('delete',	new Handle(APP_ROOT . 'authorization/delete.page.php|DeletePageController'));
		//$this->addChild('login',		new Handle(APP_ROOT . 'login.page.php|LoginPageController'));
		$this->setDefaultChild('privilege');
	}
	/*
	function login(&$source, &$request, &$responseModel) {
		$username = SRTMModel::getCurrentUsername();
		if ($username) {
			$responseModel->set('username', $username);
		} else {
			$_SERVER['PATH_INFO'] = '/login/';
		}
    }
	*/
}
?>