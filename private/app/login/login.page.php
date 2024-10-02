
<?php
require_once WACT_ROOT . 'controller/form.inc.php';
require_once WACT_ROOT . 'view/formview.inc.php';

require_once APP_ROOT . 'srtm.model.php';
require_once APP_ROOT . 'reservation/reservation.model.php';

class LoginFormView extends FormView {

	function LoginFormView($TemplateFile) {
		parent::FormView($TemplateFile);
	}
	
	function prepare(&$controller, &$request, &$responseModel) {
		$this->Template->registerDataSource($responseModel);
	}
}

class LoginFormController extends FormController {

	function LoginFormController() {
		parent::FormController();

		$this->addChild('submit', new ButtonController(new Delegate($this, 'login')));
        $this->setDefaultChild('submit');

        $this->addRule(new Handle('RequiredRule', array('username')));
        $this->addRule(new Handle('RequiredRule', array('password')));

        $this->addView('success', new Handle('RedirectView', array('/reservacion')));
		$this->addView('failure', new Handle('LoginFormView', array('/login/login.html')));
        $this->setDefaultView(new Handle('LoginFormView', array('/login/login.html')));
	}

    function login(&$source, &$request, &$responseModel){
        if ($responseModel->isValid()) {
			if (!($connect=@ldap_connect('ldap://10.12.1.4'))) {
				$responseModel->set('loginErrorMessage', 'Error al conectar con el LDAP.');
				die('failure');
			}
			if (!($bind=@ldap_bind($connect, $responseModel->get('username').'@uclv.edu.cu', $responseModel->get('password')))) {
				$responseModel->set('loginErrorMessage', 'Usuario o password incorrecto.');
				return 'failure';
			}
			$base_dn = 'OU=_Usuarios,DC=uclv,DC=edu,DC=cu';
			//$base_dn = "OU=FAC_ING_MECANICA,OU=_Usuarios,DC=uclv,DC=edu,DC=cu";
			//$base_dn = "OU=Mecanica,OU=CRD,OU=Pregrado,OU=Estudiantes,OU=FAC_ING_MECANICA,OU=_Usuarios,DC=uclv,DC=edu,DC=cu";
			//$filter = '(&(objectClass=user)(objectCategory=person)(cn='.$responseModel->get('username').'*))';
			$filter = '(&(samaccountname='.$responseModel->get('username').'))';
			if (!($search=@ldap_search($connect, $base_dn, $filter))) {
				$responseModel->set('loginErrorMessage', 'Error al filtrar en el LDAP.');
				return 'failure';
			}
			$info = ldap_get_entries($connect, $search);
			/*
			if ($_SERVER[REMOTE_ADDR] == '10.12.12.9') {
				print_r($info);die;
			}
			*/
			if (!$info["0"]) {
				$responseModel->set('loginErrorMessage', 'El usuario no es estudiante de mecánica.');
				return 'failure';
			}
			SRTMModel::setCurrentUsername($responseModel->get('username'));
			SRTMModel::setCurrentUserGroupid(ReserveModel::getFirstGroupByDN($info["0"]['distinguishedname'][0]));
			/*
			if ($_SERVER[REMOTE_ADDR] == '10.12.12.9') {
				$pieces = array_reverse(explode(",", $info["0"]['distinguishedname'][0]));
				foreach ($pieces as $piece) {
					$dn = explode ('=', $piece);
					$dns[] = $dn[1];
				}
			}
			*/
			//SRTMModel::setCurrentUserGroupid('1');
			return 'success';
		}
    }
}
?>