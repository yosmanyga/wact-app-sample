<?php
require_once WACT_ROOT . 'db/db.inc.php';

require_once WACT_ROOT . 'util/arraydataset.inc.php';

class AuthorizationModel {

	function getPrivilegesList() {
		return DBC::NewRecordSet('
			SELECT
				computers.ip, computers.hostname, authorizations.username
			FROM computers RIGHT JOIN authorizations ON computers.ip = authorizations.computer__ip
			WHERE computers.status = \'1\'
			ORDER BY computers.sort
		');
	}
	
	function updateAll($authorizations) {
		foreach ($authorizations AS $ip => $username) {
			DBC::execute('
				UPDATE authorizations
				SET username = '.DBC::MakeLiteral($username).' WHERE computer__ip = '.DBC::MakeLiteral($ip)
			);
		}
	}

	function updateOne($authorizations) {
		foreach ($authorizations AS $ip => $username) {
			if ($username <> '') {
				DBC::execute('
					UPDATE authorizations
					SET username = '.DBC::MakeLiteral($username).' WHERE computer__ip = '.DBC::MakeLiteral($ip)
				);
			}
		}
	}
	
	function getFullNamePrivilegeList(){
		$usernamesArray = DBC::getTwoColumnArray('
			SELECT
				computers.ip, authorizations.username
			FROM computers RIGHT JOIN authorizations ON computers.ip = authorizations.computer__ip
			WHERE computers.status = \'1\'
			ORDER BY computers.sort
		');
		$ldap_server = "ldap://172.20.1.14";
		$auth_user = "yosmany@uclv.edu.cu";
		$auth_pass = "acc++";
		$base_dn = "OU=FAC_ING_MECANICA,OU=_Usuarios,DC=uclv,DC=edu,DC=cu";
		if (!($connect=@ldap_connect($ldap_server))) {
			die("Could not connect to ldap server");
		}
		if (!($bind=@ldap_bind($connect, $auth_user, $auth_pass))) {
			die("Unable to bind to server");
		}
		$displaynamesArray = array();
		foreach ($usernamesArray as $ip => $username) {
			if ($username <> '') {
				$i++;
				$filter = "(&(objectClass=user)(objectCategory=person)(cn=".$username."*))";
				if (!($search=@ldap_search($connect, $base_dn, $filter))) {
					die("Unable to search ldap server");
				}
				$info = ldap_get_entries($connect, $search);
				$displaynamesArray[$i]['ip'] = $ip;
				$displaynamesArray[$i]['username'] = $username;
				$displaynamesArray[$i]['displayname'] = $info[0]["displayname"][0];
			}
		}
		return new ArrayDataset($displaynamesArray);
	}
	
	function deleteAuthorization($ip) {
		return DBC::execute("UPDATE authorizations SET username = '' WHERE computer__ip = ".DBC::MakeLiteral($ip));
	}
}
?>