<?php
require_once WACT_ROOT . 'db/db.inc.php';

require_once APP_ROOT . 'srtm.model.php';

class ReserveModel {

	function _getFirstDatetimeInitial($usergroupid){
		return DBC::getOneValue('
			SELECT
				CONCAT(DATE_FORMAT(DATE_ADD(date_tomake_reserve, INTERVAL days_ahead DAY) , \'%Y-%m-%d\'),\' \', time_initial_reservable)
			FROM
				horaries
			WHERE
				group__id = '.DBC::MakeLiteral($usergroupid).'
			ORDER BY
				date_tomake_reserve, days_ahead, time_initial_reservable
			LIMIT 0, 1
		');
		/*
		$initial = DBC::getTwoColumnArray('
			SELECT
				dayofweek_tomake_reserve + days_ahead, time_initial_reservable
			FROM
				horaries
			WHERE
				group__id = '.DBC::MakeLiteral($usergroupid).'
			ORDER BY
				dayofweek_tomake_reserve, days_ahead, time_initial_reservable
			LIMIT 0, 1
		');
		$timestamp = strtotime(date('Y-m-d'));
		$day_of_week = date ("w", $timestamp);
		$timestamp = $timestamp - 86400 * ($day_of_week - key($initial));
		$date = strftime ('%Y-%m-%d', $timestamp);
		$datetime_initial = $date.' '.current($initial);
		return $datetime_initial;
		*/
	}

	function _getLastDatetimeFinal($usergroupid){
		return DBC::getOneValue('
			SELECT
				CONCAT(DATE_FORMAT(DATE_ADD(date_tomake_reserve, INTERVAL days_ahead DAY) , \'%Y-%m-%d\'),\' \', time_final_reservable)
			FROM
				horaries
			WHERE
				group__id = '.DBC::MakeLiteral($usergroupid).'
			ORDER BY
				date_tomake_reserve DESC, days_ahead DESC, time_final_reservable DESC
			LIMIT 0, 1
		');
		/*
		$final = DBC::getTwoColumnArray('
			SELECT
				dayofweek_tomake_reserve + days_ahead, time_final_reservable
			FROM
				horaries
			WHERE
				group__id = '.DBC::MakeLiteral($usergroupid).'
			ORDER BY
				dayofweek_tomake_reserve DESC, days_ahead DESC, time_final_reservable DESC
			LIMIT 0, 1				
		');
		$timestamp = strtotime(date('Y-m-d'));
		$day_of_week = date ("w", $timestamp);
		$timestamp = $timestamp - 86400 * ($day_of_week - key($final));
		$date = strftime ('%Y-%m-%d', $timestamp);
		return $date.' '.current($final);
		*/
	}
	
	function getTotalOccupationSeconds($username, $usergroupid){
		return DBC::getOneValue('
			SELECT
				SUM(UNIX_TIMESTAMP(datetime_final) - UNIX_TIMESTAMP(datetime_initial))
			FROM
				reservations
			WHERE
				displayname = '.DBC::MakeLiteral($username).' AND
				datetime_initial >= '.DBC::MakeLiteral(ReserveModel::_getFirstDatetimeInitial($usergroupid)).' AND
				datetime_final <= '.DBC::MakeLiteral(ReserveModel::_getLastDatetimeFinal($usergroupid))
		);
	}
	
	function getTotalOccupationSeconds_ifMutable($usergroupid){
		return DBC::getOneValue('
			SELECT
				SUM(UNIX_TIMESTAMP(datetime_final) - UNIX_TIMESTAMP(datetime_initial))
			FROM
				reservations, displaynames
			WHERE
				displaynames.displayname = reservations.displayname
			AND displaynames.group__id = '.DBC::MakeLiteral($usergroupid).'
			AND datetime_initial >= '.DBC::MakeLiteral(ReserveModel::_getFirstDatetimeInitial($usergroupid)).'
			AND	datetime_final <= '.DBC::MakeLiteral(ReserveModel::_getLastDatetimeFinal($usergroupid))
		);
	}
	
	function getComputer($ip){
		return DBC::getTwoColumnArray('
			SELECT
				ip, status
			FROM
				computers
			WHERE
				ip = '.DBC::MakeLiteral($ip)
		);
	}

	function getFirstGroupByDN($dn) {
		return DBC::getOneValue('
			SELECT
				group__id
			FROM
				dn_filters
			WHERE
				RIGHT ('.DBC::MakeLiteral($dn).', CHAR_LENGTH(dn)) = dn
			ORDER BY 
				sort
		');
		/* Another solution
		return DBC::getOneValue('
			SELECT
				dn
			FROM
				dn_filters
			WHERE
				INSTR('.DBC::MakeLiteral($dn).', dn)
			ORDER BY 
				sort
		');
		*/
	}
	
	//////////////////////////////////////////////////////////////////////
	
	// Get the date in the format '26-12-2005' and reverse to '2005-12-26'
	/*
	function reverseDate($date) {
		return implode('-', array_reverse(explode('-', $date)));
	}
	*/
	// Get total reservations seconds by username

	function getQuota($groupid){
		return DBC::getOneValue('
			SELECT
				quota
			FROM
				groups
			WHERE
				id = '.DBC::MakeLiteral($groupid)
		);
	}
	/*
	function getMonday($date){
		$timestamp = strtotime($date);
		$day_of_week = date ("w", $timestamp);
		if ($day_of_week == '0') $day_of_week = 7;
		$timestampSum = 86400 * ($day_of_week - 1);
		$timestamp = $timestamp - $timestampSum;
		return date ("Y-m-d", $timestamp);
	}
	*/

	function getOccupationListByUsername($username, $usergroupid) {
		return DBC::NewRecordSet('
			SELECT
				id,
				computer__ip,
				computers.hostname AS computerHostname,
				DATE_FORMAT(datetime_initial, \'%d-%m-%Y\') AS date,
				DATE_FORMAT(datetime_initial, \'%I:%i %p\') AS datetime_initial,
				DATE_FORMAT(datetime_final, \'%I:%i %p\') AS datetime_final,
				DATE_FORMAT(datetime_initial, \'%Y-%m-%d %H:%i:%s\') AS datetime,
				
				DATE_FORMAT(datetime_initial, \'%Y-%m-%d\') AS date_real,				
				DATE_FORMAT(datetime_initial, \'%H:%i:%s\') AS time_real
			FROM reservations, computers
			WHERE
				displayname = '.DBC::MakeLiteral($username).' AND
				datetime_initial >= '.DBC::MakeLiteral(ReserveModel::_getFirstDatetimeInitial($usergroupid)).' AND
				datetime_final <= '.DBC::MakeLiteral(ReserveModel::_getLastDatetimeFinal($usergroupid)).' AND
				computer__ip = ip
			ORDER BY datetime, computerHostname
		');
	}
	
	function &getComputersList() {
		return DBC::NewRecordSet('SELECT * FROM computers WHERE status = 1 ORDER BY sort');
    }

	function &getTimeline($date) {
		return DBC::NewRecordSet('
			SELECT
				DATE_ADD('.DBC::MakeLiteral($date).', INTERVAL isnextday DAY) AS date,
				time_initial,
				DATE_FORMAT(DATE_ADD(CONCAT('.DBC::MakeLiteral($date).', \' \', time_initial), INTERVAL seconds_ahead SECOND), \'%H:%i:%s\') AS time_final,
				visible,
				reservable
			FROM timelines
			ORDER BY isnextday, time_initial
		');
    }
	
	function findIfTodayIsToReserveThisDatetime($datetime, $usergroupid) {
		return DBC::FindRecord('
			SELECT
				id
			FROM
				horaries
			WHERE
				group__id = '.DBC::MakeLiteral($usergroupid).' AND
				date_tomake_reserve = DATE_FORMAT(\''.SRTMModel::getNow().'\', \'%Y-%m-%d\') AND
				time_initial_tomake_reserve <= TIME_FORMAT(\''.SRTMModel::getNow().'\', \'%H:%i:%s\') AND
				time_final_tomake_reserve >= TIME_FORMAT(\''.SRTMModel::getNow().'\', \'%H:%i:%s\') AND
				DATE_FORMAT(DATE_ADD(\''.SRTMModel::getNow().'\', INTERVAL days_ahead DAY), \'%Y-%m-%d\') = DATE_FORMAT('.DBC::MakeLiteral($datetime).', \'%Y-%m-%d\') AND
				time_initial_reservable <= TIME_FORMAT('.DBC::MakeLiteral($datetime).', \'%H:%i:%s\') AND
				time_final_reservable > TIME_FORMAT('.DBC::MakeLiteral($datetime).', \'%H:%i:%s\')
		');
	}

	function getOccupationByComputerAndTurn($ip, $datetime_initial) {
		return DBC::FindRecord('
			SELECT *
			FROM reservations
			WHERE
				computer__ip = '.DBC::MakeLiteral($ip).' AND
				datetime_initial = '.DBC::MakeLiteral($datetime_initial)
		);
	}

	function findIfTimeInitialIsValid($datetime_initial) {
		return DBC::getOneValue('
			SELECT 
				DATE_ADD('.DBC::MakeLiteral($datetime_initial).', INTERVAL seconds_ahead SECOND) AS datetime_final
			FROM
				timelines
			WHERE
				DATE_FORMAT('.DBC::MakeLiteral($datetime_initial).', \'%H:%i:%s\') = time_initial AND
				reservable = \'Y\'
		');
	}

	function reserveOccupation($username, $usergroupid, $ip, $datetime_initial, $datetime_final) {
		$query = 'INSERT INTO reservations (displayname, group__id, computer__ip, datetime_initial, datetime_final) 
			VALUES ('.DBC::MakeLiteral($username).', '.DBC::MakeLiteral($usergroupid).', '.DBC::MakeLiteral($ip).', '.DBC::MakeLiteral($datetime_initial).', '.DBC::MakeLiteral($datetime_final).')';
		return DBC::execute($query);
	}

	function adjustDate($datetime) {
		return DBC::getOneValue('
			SELECT
				DATE_FORMAT(DATE_ADD('.DBC::MakeLiteral($datetime).', INTERVAL 0-isnextday DAY), \'%Y-%m-%d\') AS date
			FROM timelines
			WHERE
				time_initial = DATE_FORMAT('.DBC::MakeLiteral($datetime).', \'%H:%i:%s\')'
		);
    }

	function &getDateReservablesByToday($usergroupid) {
		return DBC::NewRecordSet('
			SELECT
				DATE_FORMAT(DATE_ADD(\''.SRTMModel::getNow().'\', INTERVAL days_ahead DAY), \'%Y-%c-%e\') as date
			FROM horaries
			RIGHT JOIN timelines
			ON time_initial_reservable = time_initial
			WHERE
				group__id = '.DBC::MakeLiteral($usergroupid).' AND
				date_tomake_reserve = DATE_FORMAT(\''.SRTMModel::getNow().'\', \'%Y-%m-%d\') AND
				time_initial_tomake_reserve <= TIME_FORMAT(\''.SRTMModel::getNow().'\', \'%H:%i:%s\') AND
				time_final_tomake_reserve >= TIME_FORMAT(\''.SRTMModel::getNow().'\', \'%H:%i:%s\') AND
				isnextday = 0
		');
	}

	function getDisplaynameList_byGroupId($id) {
		return DBC::GetTwoColumnArray('
			SELECT displayname, displayname FROM displaynames WHERE group__id = '.DBC::MakeLiteral($id)
		);
	}




	/*
	function getReservableDays() {
		return DBC::getTwoColumnArray('SELECT id, days_ahead FROM horaries ORDER BY days_ahead');
	}

	function &getReservableDaysList($dayofweek, $actualTime) {
		return DBC::NewRecordSet('
			SELECT
				dayofweek_reservable AS dayofweek
			FROM
				horaries
			WHERE
				dayofweek_tomake_reserve = '.DBC::MakeLiteral($dayofweek).'
				time_initial <= '.DBC::MakeLiteral($actualTime).'
				time_final > '.DBC::MakeLiteral($actualTime).'
		');
	}
	*/
	
	function deleteOccupation($id) {
		return DBC::execute('DELETE FROM reservations WHERE id = '.DBC::MakeLiteral($id));
	}
}
?>