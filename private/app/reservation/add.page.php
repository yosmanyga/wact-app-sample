<?php
require_once WACT_ROOT . 'controller/controller.inc.php';
require_once WACT_ROOT . 'view/redirecturl.inc.php';
require_once WACT_ROOT . 'view/view.inc.php';

require_once APP_ROOT . 'reservation/reservation.model.php';
require_once APP_ROOT . 'srtm.model.php';

class AddPageController extends PageController {

	function AddPageController() {
		parent::PageController();
		
		$this->registerOnActivateListener(new Delegate($this, 'guardDateTimeInitial'));
		$this->registerOnActivateListener(new Delegate($this, 'guardIp'));
		$this->registerOnActivateListener(new Delegate($this, 'guardQuota'));

		$this->registerOnLoadListener(new Delegate($this, 'reserve'));		
		
		$this->addView('failure', new Handle('View', array('/reservation/show.html')));
	}
	
	function guardDateTimeInitial(&$source, &$request, &$responseModel){
		$datetime_initial = $request->getParameter('datetime_initial');
		if (!$datetime_initial) {
			$responseModel->set('errorMessage', 'No se encontró el parámetro "datetime_initial".');
			return 'failure';
		} else {
			$datetime_final = ReserveModel::findIfTimeInitialIsValid($datetime_initial);
			if (!$datetime_final) {
				$responseModel->set('errorMessage', 'El parámetro "datetime_initial" no es válido.');
				return 'failure';
			} else {
				$responseModel->set('datetime_initial', $datetime_initial);
				$responseModel->set('datetime_final', $datetime_final);
			}
			if ($datetime_initial < date('Y-m-d H:i:s', strtotime(SRTMModel::getNow()) + 3600)) {
				$responseModel->set('errorMessage', 'Los turnos se reservan con al menos una hora de anterioridad.');
				return 'failure';
			}
			if (!ReserveModel::findIfTodayIsToReserveThisDatetime($datetime_initial, $responseModel->get('usergroupid'))) {
				$responseModel->set('errorMessage', 'Hoy no se puede reservar ese turno.');
				return 'failure';
			}
		}
	}

	function guardIp(&$source, &$request, &$responseModel){
		if (!$request->hasParameter('ip')) {
			$responseModel->set('errorMessage', 'No se encontró el parámetro "ip".');
			return 'failure';
		} else {
			$computerArray = ReserveModel::getComputer($request->getParameter('ip'));
			if (!computerArray) {
				$responseModel->set('errorMessage', 'La computadora con ip '.$request->getParameter('ip').' no existe en este laboratorio.');
				return 'failure';
			} else {
				if (current($computerArray) <> 1) {
					$responseModel->set('errorMessage', 'La computadora con ip '.$request->getParameter('ip').' no está disponible.');
					return 'failure';
				}
			}
			if (ReserveModel::getOccupationByComputerAndTurn($request->getParameter('ip'), $responseModel->get('datetime_initial'))) {
				$responseModel->set('errorMessage', 'El turno entrado ya está ocupado.');
				return 'failure';
			} else {
				$responseModel->set('ip', $request->getParameter('ip'));						
			}
		}
	}
	
	function guardQuota(&$source, &$request, &$responseModel){
		$turnSize = strtotime($responseModel->get('datetime_final')) - strtotime($responseModel->get('datetime_initial'));
		$responseModel->set('remanent', $responseModel->get('remanent') - $turnSize);
		if ($responseModel->get('remanent') < 0) {
			$responseModel->set('errorMessage', 'Solo puede reservar '.$responseModel->get('quota').' minutos');
			return 'failure';
		}
	}
	
	function reserve(&$source, &$request, &$responseModel){
		$mutationname = $responseModel->get('mutationname');
		if (!is_null($mutationname)) {
			$responseModel->set('username', $mutationname);
		}
		if (ReserveModel::reserveOccupation($responseModel->get('username'),
											$responseModel->get('usergroupid'),
											$responseModel->get('ip'),
											$responseModel->get('datetime_initial'),
											$responseModel->get('datetime_final'))) {
			$responseModel->set('date', ReserveModel::adjustDate($responseModel->get('datetime_initial')));
			$source->addView('success', new Handle ('RedirectURLView', array($responseModel->get('base').'index.php/reservacion/mostrar/?date='.$responseModel->get('date'))));
			return 'success';
		}
	}	
}
?>