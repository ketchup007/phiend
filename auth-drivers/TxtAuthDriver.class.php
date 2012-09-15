<?php
/**
 * @package phiend
 * @author Maciej Jarzebski
 * @version $Id: TxtAuthDriver.class.php,v 1.4 2003/09/22 19:11:48 cryonax Exp $
 */

/**
 * A text file auth driver for Phiend.
 *
 * Authenticates users using a plain text file containing user names, passwords and roles.
 * Each line of this file should have the following format:
 * <user name> <password> <first role> <second role> ...
 * The number of roles is not limited, it can be zero.
 *
 * @package phiend
 */
class TxtAuthDriver {

	/**
	 * Constructor.
	 * 
	 * Stores and verifies supplied params.
	 * @param array $params Parameters for the driver:
	 *  - file: name of file to use (must be in {CODE_DIR}/config/), required
	 *  - encryption: 'md5' or 'none', optional (if not given, no encryption will be used)
	 */
	function TxtAuthDriver($params) {
		$this->_useMD5 = false;
		if (isset($params['encryption'])) {
			if (strcmp($params['encryption'], 'md5') == 0) {
				$this->_useMD5 = true;
			} else if (strcmp($params['encryption'], 'none') != 0) {
				trigger_error('Unknown encryption "' . $params['encryption'] . '", cannot init driver', E_USER_NOTICE);
				$this->_initOk = false;
			}
		}
		if (!isset($params['file'])) {
			trigger_error('Parameter "file" not found, cannot init driver', E_USER_NOTICE);
			$this->_initOk = false;
		}
		$this->_filename = CODE_DIR . 'config/' . $params['file'];
		if (!file_exists($this->_filename)) {
			trigger_error('File "' . $this->_filename . '" does not exist, cannot init driver', E_USER_NOTICE);
			$this->_initOk = false;
		}
	}

	/**
	 * Performs authentication.
	 *
	 * Reads the text file, parses it and returns the result of authentication.
	 * @return int One of the _PHIEND_AUTH_something constants defined in AuthManager.class.php
	 * @param string $userName User name to look for, must not contain spaces
	 * @param string $password Password (uncoded) of the user to look for;
	 *   if this parameter is null, search will be based only on user name
	 */
	function getRoles($userName, $password, $params = array()) {
		if (!$this->_initOk) {
			return _PHIEND_AUTH_FAILURE;
		}
		if ($this->_useMD5) {
			$password = md5($password);
		}
		//read file
		$inFile = @fopen($this->_filename, 'r');
		while (!feof($inFile)) {
			$line = fgets($inFile, 1024);
			$keys = preg_split ("/[\s]+/", $line);
			if ( (count($keys) > 1) && (strcmp($keys[0], $userName) == 0) ) {
				if (is_null($password) || (strcmp($keys[1], $password) == 0)) {
					fclose($inFile);
					return implode(' ', array_slice($keys, 2));
				} else {
					fclose($inFile);
					return _PHIEND_AUTH_BAD_PASSWORD;
				}
			}
		}
		fclose($inFile);
		return _PHIEND_AUTH_NO_USER;
	}
	
	/**
	 * Whether this driver successfully initialized.
	 * @var bool
	 * @access private
	 */
	var $_initOk = true;
	
	/**
	 * Whether MD5 is used.
	 * @var bool
	 * @access private
	 */
	var $_useMD5 = false;
	
	/**
	 * Name of file to use.
	 * @var string
	 * @access private
	 */
	var $_filename;
}

?>