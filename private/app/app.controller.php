<?php
require_once WACT_ROOT . 'controller/pathinfo.inc.php';

require_once APP_ROOT . 'srtm.model.php';

class AppPathInfoDispatchController extends PathInfoDispatchController {

	function AppPathInfoDispatchController() {
		parent::PathInfoDispatchController();

		$this->registerOnActivateListener(new Delegate($this, 'setLanguage'));
		$this->registerOnActivateListener(new Delegate($this, 'setBase'));
		$this->registerOnActivateListener(new Delegate($this, 'setNow'));		
		$this->registerOnActivateListener(new Delegate($this, 'login'));
		
		$this->addChild('reservacion', new Handle(APP_ROOT . 'reservation/reservation.controller.php|ReservationDispatchController'));
		$this->addChild('autorizacion', new Handle(APP_ROOT . 'authorization/authorization.controller.php|AuthorizationDispatchController'));		
		$this->addChild('login', new Handle(APP_ROOT . 'login/login.page.php|LoginFormController'));

		$this->setDefaultChild('reservacion');
	}

    function login(&$source, &$request, &$responseModel) {
		$username = SRTMModel::getCurrentUsername();
		if ($username) {
			$responseModel->set('username', $username);
			
			$usergroupid = SRTMModel::getCurrentUserGroupid();
			$responseModel->set('usergroupid', $usergroupid);
			
			if (SRTMModel::isMutableGroup($usergroupid)) {
				$mutationname = SRTMModel::getCurrentMutationname();
				$source->addChild('alias', new Handle(APP_ROOT . 'mutate/mutate.page.php|MutateFormController'));
				if ($mutationname) {
					$responseModel->set('mutationname', $mutationname);
				} else {
					$_SERVER['PATH_INFO'] = '/alias/';
				}
			}
		} else {
			if (!in_array($_SERVER['PATH_INFO'], array('', '/reservacion/', '/reservacion/mostrar/', '/autorizacion/', '/autorizacion/delete/'))) {
				$_SERVER['PATH_INFO'] = '/login/';
			}
		}
    }	
	
	function setLanguage(){
		// Date in SPANISH
		setlocale(LC_ALL, "es_ES");
	}

	function setBase(&$source, &$request, &$responseModel){
		// Base
		$responseModel->set('base', 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']).'/');
	}
	
	function setNow(&$source, &$request, &$responseModel){
		// Date in SPANISH
		SRTMModel::setNow();
		$responseModel->set('now', SRTMModel::setNow());
		$GLOBALS['responseModel'] =& $responseModel;
	}
}
?>