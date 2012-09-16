<?php
/**
 * @package phiend
 * @author Maciej Jarzebski
 * @version $Id: PearDbAuthDriver.class.php,v 1.1 2003/11/11 12:44:23 cryonax Exp $
 */

namespace Phiend\auth-drivers;

require_once 'DB.php';
 
/**
 * A database auth driver for Phiend, based on PEAR DB.
 *
 * @package phiend
 */
class PearDbAuthDriver {

	/**
	 * Constructor.
	 * 
	 * Stores and verifies supplied params.
	 * @param array $params Parameters for the driver:
	 *  - dsn: DSN to use (see PEAR DB manual for details), required
	 *  - table: database table to use, required
	 *  - userCol: column containing user names, default is "username"
	 *  - passwdCol: column containing passwords (encoded with MD5), default is "passwd"
	 *  - rolesCol: column containing user roles, default is "roles"
	 */
	function __construct($params) {

		$this->_readParam($params, 'dsn', '_dsn');
		$this->_readParam($params, 'table', '_table');
		$this->_readParam($params, 'userCol', '_userCol', false);
		$this->_readParam($params, 'passwdCol', '_passwdCol', false);
		$this->_readParam($params, 'rolesCol', '_rolesCol', false);

		if ($this->_initOk) {
			$this->_conn = DB::connect($this->_dsn);
			if (DB::isError($this->_conn)) {
				$this->_initOk = false;
			}
		}
	}

	/**
	 * Performs authentication.
	 *
	 * Retrieves user data from database.
	 * @return int One of the _PHIEND_AUTH_something constants defined in AuthManager.class.php
	 * @param string $userName User name to look for, must not contain spaces
	 * @param string $password Password (not encoded) of the user to look for;
	 *   if this parameter is null, search will be based only on user name
	 */
	function getRoles($userName, $password) {
	
		if (!$this->_initOk) {
			return _PHIEND_AUTH_FAILURE;
		}
		
		$query = 'SELECT ' . $this->_passwdCol . ', ' . $this->_rolesCol . ' FROM ' . $this->_table .
			' WHERE ' . $this->_userCol . '="' . $userName . '" AND aktualny = 1';
		
		$row = $this->_conn->getRow($query, null, DB_FETCHMODE_ORDERED);
		if (DB::isError($row)) {
			return _PHIEND_AUTH_FAILURE;
		}
		if (!isset($row[0])) {
			return _PHIEND_AUTH_NO_USER;
		}
		
		if (($password == null) || (strcmp($row[0], md5($password)) == 0)) {
			return $row[1];
		}
		return _PHIEND_AUTH_BAD_PASSWORD;
	}
	
	function _readParam($params, $paramName, $varName, $required = true) {
		if (isset($params[$paramName])) {
			$this->$varName = $params[$paramName];
		} else if ($required) {
			trigger_error('Parameter "' . $paramName . '" not found, cannot init driver', E_USER_NOTICE);
			$this->_initOk = false;
		}
	}

	var $_initOk = true;
	var $_dsn;
	var $_conn;
	var $_table;
	var $_userCol = 'username';
	var $_passwdCol = 'passwd';
	var $_rolesCol = 'roles';
}

?>
