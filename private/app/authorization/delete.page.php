<?php
require_once WACT_ROOT . 'controller/controller.inc.php';
require_once WACT_ROOT . 'view/redirect.inc.php';

require_once APP_ROOT . 'authorization/authorization.model.php';

class DeletePageController extends PageController {

	function DeletePageController() {
		parent::PageController();
		
		$this->registerOnLoadListener(new Delegate($this, 'delete'));

		$this->addView('success', new Handle('RedirectView', array('/autorizacion/')));		
	}
	
	function delete(&$source, &$request, &$responseModel){
		$ip = $request->getParameter('ip');
		if (!$ip) {
			$responseModel->set('errorMessage', 'No se encontró el parámetro "ip".');
			return 'failure';
		} else {
			if (AuthorizationModel::deleteAuthorization($ip)) {
				return 'success';
			} else {
				return 'failure';
			}
		}
	}	
}
?>