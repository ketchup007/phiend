<?php
/**
 * @package phiend
 * @author Maciej Jarzebski
 * @version $Id: PearLogDriver.class.php,v 1.2 2003/11/11 09:55:53 cryonax Exp $
 */


/* require_once 'Log.php'; */

/**
 * Log driver which uses PEAR Log.
 */
class PearLogDriver {

	/**
	 * Constructor.
	 * 
	 * Checks if all required parameters are supplied and creates PEAR Log object.
	 * @param array $params Contains parameters for this log driver:
	 *  - type: log type for PEAR Log
	 *  - name: log name for PEAR Log
	 */
	function __construct($params) {
		$this->_params = $params;
		$this->_checkParam('type');
		$this->_checkParam('name');
		
		if ($this->_initOk) {
			$this->_pearLog = Log::factory($params['type'], LOG_DIR . '/' . $params['name'], 'phiend');
		}
	}
	
	/**
	 * Log message.
	 * @param int $code Error code (one of E_something constants)
	 * @param string $messge Error message
	 * @param string $file Filename where error was triggered
	 * @param int $line Line number where error was triggered
	 * @access public
	 */
	function log($code, $message, $file, $line) {
		static $errorMappings = array(
			E_ERROR				=> PEAR_LOG_ERR,
			E_WARNING			=> PEAR_LOG_WARNING,
			E_PARSE				=> PEAR_LOG_ERR,
			E_NOTICE			=> PEAR_LOG_INFO,
			E_CORE_ERROR		=> PEAR_LOG_ERR,
			E_CORE_WARNING		=> PEAR_LOG_WARNING,
			E_COMPILE_ERROR		=> PEAR_LOG_ERR,
			E_COMPILE_WARNING	=> PEAR_LOG_WARNING,
			E_USER_ERROR		=> PEAR_LOG_ERR,
			E_USER_WARNING		=> PEAR_LOG_WARNING,
			E_USER_NOTICE		=> PEAR_LOG_INFO,
			E_DOT_ERROR			=> PEAR_LOG_ERR,
			E_DOT_WARNING		=> PEAR_LOG_WARNING,
			E_DOT_NOTICE		=> PEAR_LOG_INFO,
 		);
		
		if ($this->_initOk) {
			$this->_pearLog->log($file . '/' . $line . ': ' . $message, $errorMappings[$code]);
		}
	}
	
	/**
	 * Check if a required parameter was supplied
	 * @param string $paramName Name of expected parameter
	 * @access private
	 */
	function _checkParam($paramName) {
		if (!isset($this->_params[$paramName])) {
			trigger_error('Parameter "' . $paramName . '" required, cannot init PEAR Log', E_USER_WARNING);
			$this->_initOk = false;
		}
	}
	
	/**
	 * Parameters supplied to this log driver.
	 * @var array
	 * @access private
	 */
	var $_params;
	
	/**
	 * PEAR Log object
	 * @var object
	 * @access private
	 */
	var $_pearLog;
	
	/**
	 * Whether this driver successfully initialized
	 * @var bool
	 * @access private
	 */
	var $_initOk = true;
}

?>