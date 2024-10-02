<?php
require_once WACT_ROOT . 'controller/controller.inc.php';
require_once WACT_ROOT . 'view/redirect.inc.php';
require_once WACT_ROOT . 'view/formview.inc.php';

require_once APP_ROOT . 'reservation/reservation.model.php';

class DeletePageController extends PageController {

	function DeletePageController() {
		parent::PageController();
		
		$this->registerOnLoadListener(new Delegate($this, 'delete'));

		$this->addView('failure', new Handle('View', array('/reservation/show.html')));		
	}
	
	function delete(&$source, &$request, &$responseModel){
		$id = $request->getParameter('id');
		if (!$id) {
			$responseModel->set('errorMessage', 'No se encontró el parámetro "id".');
			return 'failure';
		} else {
			if (ReserveModel::deleteOccupation($id)) {
				$source->addView('success', new Handle ('RedirectView', array('/reservacion/mostrar/?date='.$request->getParameter('date'))));		
				return 'success';
			} else {
				$responseModel->set('errorMessage', 'No se pudo borrar el turno con id '.$id);
				return 'failure';
			}
		}
	}	
}
?>