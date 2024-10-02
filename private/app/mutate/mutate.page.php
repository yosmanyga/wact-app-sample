<?php
require_once WACT_ROOT . 'controller/form.inc.php';
require_once WACT_ROOT . 'view/formview.inc.php';

require_once APP_ROOT . 'srtm.model.php';
require_once APP_ROOT . 'reservation/reservation.model.php';

class MutateFormView extends FormView {

	function MutateFormView($TemplateFile) {
		parent::FormView($TemplateFile);
	}
	
	function prepare(&$controller, &$request, &$responseModel) {
		$this->Template->registerDataSource($responseModel);
		
		$defaultDisplayname[$responseModel->get('username')] = $responseModel->get('username');
		$mutationnamesArray = ReserveModel::getDisplaynameList_byGroupId($responseModel->get('usergroupid'));
		$mutationnamesArray = array_merge($defaultDisplayname, $mutationnamesArray);

		$mutationnameTag =& $this->Form->getChild('mutationname');
        $mutationnameTag->setChoices($mutationnamesArray);
		$mutationnameTag->setSelection($responseModel->get('username'));
	}
}

class MutateFormController extends FormController {

	function MutateFormController() {
		parent::FormController();

		$this->addChild('submit', new ButtonController(new Delegate($this, 'mutate')));
        $this->setDefaultChild('submit');

        $this->addRule(new Handle('RequiredRule', array('mutationname')));

        $this->addView('success', new Handle('RedirectView', array('/reservacion')));
		$this->addView('failure', new Handle('MutateFormView', array('/mutate/mutate.html')));
        $this->setDefaultView(new Handle('MutateFormView', array('/mutate/mutate.html')));
	}

    function mutate(&$source, &$request, &$responseModel){
        if ($responseModel->isValid()) {
			SRTMModel::setCurrentMutationname($responseModel->get('mutationname'));
			return 'success';
		}
    }
}
?>