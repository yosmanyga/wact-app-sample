<?php
require_once WACT_ROOT . 'controller/pathinfo.inc.php';

require_once APP_ROOT . 'reservation/reservation.model.php';
require_once APP_ROOT . 'srtm.model.php';

class ReservationDispatchController extends PathInfoDispatchController {

	function ReservationDispatchController() {
		parent::PathInfoDispatchController();

		$this->registerOnActivateListener(new Delegate($this, 'loadDate'));
		$this->registerOnActivateListener(new Delegate($this, 'loadRemanent'));		
		
		$this->addChild('mostrar',	new Handle(APP_ROOT . 'reservation/show.page.php|ShowPageController'));
		$this->addChild('insertar',	new Handle(APP_ROOT . 'reservation/add.page.php|AddPageController'));
		$this->addChild('borrar',	new Handle(APP_ROOT . 'reservation/delete.page.php|DeletePageController'));
		$this->setDefaultChild('mostrar');
	}
	
    function loadDate(&$source, &$request, &$responseModel) {
		if ($request->hasParameter('date')) {
			$date = $request->getParameter('date');
        } else {
			$date = strftime("%Y-%m-%d",strtotime(SRTMModel::getNow()));
		}
		$responseModel->set('date', $date);
	}

	function loadRemanent(&$source, &$request, &$responseModel) {
		$occupationTotalSeconds = 0;
		if ($responseModel->hasProperty('username')) {
			$occupationTotalSeconds = ReserveModel::getTotalOccupationSeconds($responseModel->get('username'), $responseModel->get('usergroupid'));
			if ($responseModel->hasProperty('mutationname')) {
				$occupationTotalSeconds += ReserveModel::getTotalOccupationSeconds_ifMutable($responseModel->get('usergroupid'));
			}
			
			$quota = ReserveModel::getQuota($responseModel->get('usergroupid'));
			$responseModel->set('quota', $quota/60);
			$responseModel->set('remanent', $quota - $occupationTotalSeconds);
		}
	}
}
?>