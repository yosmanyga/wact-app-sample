<?php
require_once WACT_ROOT . 'controller/form.inc.php';
require_once WACT_ROOT . 'view/formview.inc.php';

require_once APP_ROOT . 'authorization/authorization.model.php';

class PrivilegeFormView extends FormView {

	function PrivilegeFormView($TemplateFile) {
		parent::FormView($TemplateFile);
	}

	function prepare(&$controller, &$request, &$responseModel) {
		$this->Template->registerDataSource($responseModel);
		
		//Populating username's authorizations list
        $privilegesList =& AuthorizationModel::getPrivilegesList();
		$this->Template->setChildDataSource('privilegesList', $privilegesList);

		//Populating full name's authorizations list
        $privilegesList =& AuthorizationModel::getFullNamePrivilegeList();
		$this->Template->setChildDataSource('fullNamePrivilegesList', $privilegesList);
	}
}

class PrivilegePageController extends PageController {

    function PrivilegePageController() {
        parent::PageController();

        $Form = new FormController();
        $Form->addChild('update', new ButtonController(new Delegate($this, 'update')));
        $Form->setDefaultChild('update');

        $this->addChild('privilegeForm', $Form);
        $this->setDefaultChild('privilegeForm');

		$this->setDefaultView(new PrivilegeFormView('/authorization/privilege.html'));		
    }
	
	function update(&$source, &$request, &$responseModel) {
		if ($_POST['update'] && $responseModel->isValid()) {
			AuthorizationModel::updateAll($_POST['username']);
		} else {
			AuthorizationModel::updateOne($_POST['username']);
		}
	}
}
?>