<?php
namespace Phiend;

/**
 * @package phiend
 * @author Maciej Jarzebski
 * @version $Id: ActionController.class.php,v 1.15 2004/08/28 13:49:23 cryonax Exp $
 */

/**#@+
 * One of the available types of actions.
 */
define('_PHIEND_ACTION_LOGIC', 1);
define('_PHIEND_ACTION_VIEW', 2);
/**#@-*/

/**#@+
 * Custom error code.
 */
define('E_DOT_ERROR', 32768);
define('E_DOT_WARNING', 16384);
define('E_DOT_NOTICE', 8192);
/**#@-*/

/**
 * Global reference to main ActionController object.
 *
 * Necessary so that other objects may easily call its methods and access its public variables.
 * @var object
 */
$_phiend_actionController = null;

/**
 * Phiend error handler.
 *
 * Installed by phiend in place of the standard PHP error handler at the beginning of ActionController constructor.
 * @param integer $code PHP error code (one of E_something constants defined by PHP)
 * @param string $message Message explaining the error
 * @param string $file Name of file where error was triggered (full path to file)
 * @param integer $line Number of line where error was triggered
 */
function _phiend_errorHandler($code, $message, $file, $line) {
	global $_phiend_actionController;
//	print($message);
	if (($code && error_reporting()) == 0) {
		return;
	}
	
	if ($_phiend_actionController->useFullPath == false) {
		$file = basename($file);
	}
	if (($_phiend_actionController->useCustomErrorCodes == true) && (ord($message) == 46)) {
		$message = substr($message, 1);
		$code = ($code == E_USER_ERROR) ? E_DOT_ERROR : ($code == E_USER_WARNING) ? E_DOT_WARNING: E_DOT_NOTICE;
	}
	if ( ($_phiend_actionController->logLevel & $code) != 0) {
		foreach ($_phiend_actionController->logDrivers as $logDriver) {
			$logDriver->log($code, $message, $file, $line);
		}
	}
	if ( ( ($_phiend_actionController->echoLevel & $code) != 0) || ($_phiend_actionController->debugMode == true) ) {
		echo '<br /><b>Error</b>: ' . $message . ' in <b>' . $file . '</b> on line <b>' . $line . "</b><br />\n";
	}
	if (($_phiend_actionController->dieLevel & $code) != 0) {
		while (ob_get_level() > 0) {
			ob_end_flush();
		}
		die();
	}
	if (($_phiend_actionController->storeLevel & $code) != 0) {
		$_phiend_actionController->errors[] = array(
			'file' => $file,
			'line' => $line,
			'code' => $code,
			'message' => $message
		);
	}
}

/**
 * The core class of phiend.
 *
 * Created at the beginning of every request. Sets up everything:
 * - installs custom error handler
 * - checks if config file has been modified, if yes creates ConfigParser to parse it
 * - initializes logs
 * - includes generated action matches switch
 * - finds a matching action
 * - performs all actions in chain
 * This is the core of the Controller part of MVC.
 * @package phiend
 */
class ActionController {

	/**
	 * Constructor.
	 *
	 * Initializes basic things. Does not do any real work.
	 */
	function ActionController() {
		
		$GLOBALS['_phiend_actionController'] = & $this;
		
		//PHP does not allow initializers in class variable definitions, so...
		$this->echoLevel = E_ALL ^ (E_NOTICE | E_USER_NOTICE);
		$this->dieLevel = E_ERROR | E_USER_ERROR | E_COMPILE_ERROR | E_CORE_ERROR | E_PARSE;
		$this->storeLevel = E_ALL;
		
		error_reporting(E_ALL);
		set_error_handler('_phiend_errorHandler');
	}
	
	/**
	* Serve request.
	* 
	* Does all the job.
	* @access public
	*/
	function run() {

		if ($this->debugMode) {
			trigger_error('----- Phiend starting (debug mode) -----', E_USER_NOTICE);
		} else {
			trigger_error('----- Phiend starting -----', E_USER_NOTICE);
		}
		//check read/write permissions		
		if (!is_readable(CODE_DIR . 'config/phiend-config.xml')) {
			trigger_error('Cannot read phiend-config.xml', E_USER_ERROR);
		}
		if (!is_writable(LOG_DIR) || !is_writable(CONFIG_OUTPUT_DIR)) {
			trigger_error('Cannot write to output dirs', E_USER_ERROR);
		}
		
		//init config
		if ( ($this->debugMode == true) || !file_exists(CONFIG_OUTPUT_DIR . 'phiend-config.php') ||
			( ($this->noParse == false) && (filemtime(CONFIG_OUTPUT_DIR . 'phiend-config.php') < filemtime(CODE_DIR . 'config/phiend-config.xml') ) )
		) {
			//create actions config
			include_once PHIEND_DIR . 'ConfigParser.class.php';
			$configParser = new ConfigParser();
			$configParser->parse();
			trigger_error('Configuration file parsed', E_USER_NOTICE);
		}

		include_once CONFIG_OUTPUT_DIR . 'phiend-config.php';
		//include_once CONFIG_OUTPUT_DIR . 'log-drivers.php';
		
		//$actionName holds name of action
		while (strcmp($actionName, '') != 0) {
			
			if (!$this->readConfig($actionName)) {
				exit();
			}
			
			//perform action
			$performResult = $this->performAction($actionName);
			if ($performResult == false) {
				if (strcmp($this->actionConfig['fallback-action'], '') == 0) {
					trigger_error('Action ' . $actionName . ' failed and no fallback given', E_USER_ERROR);
				} else {
					trigger_error('Action ' . $actionName . ' failed, falling back to ' . $this->actionConfig['fallback-action'], E_USER_NOTICE);
					$actionName = $this->actionConfig['fallback-action'];
					continue;
				}
			}
			if (is_string($performResult) && ($this->actionConfig['type'] == _PHIEND_ACTION_LOGIC)) {
				$actionName = $performResult;
			}
			if ($this->actionConfig['type'] == _PHIEND_ACTION_VIEW) {
				return;
			}
		}
		trigger_error('Action chain ended prematurely', E_USER_ERROR);
	}
	
//--- subroutines -------------------------------------------------------------

	/*
	 * Read config file for action, given its name.
	 *
	 * Must be called before performing an action.
	 * @return bool Whether the config could be loaded
	 * @param string $actionName Name of action to load config for
	 * @access public
	 */
	function readConfig($actionName) {
		if (!is_readable(CONFIG_OUTPUT_DIR . $actionName . '.config.php')) {
			trigger_error('Cannot read config for action ' . $actionName, E_USER_ERROR);
			return false;
		}
			
		//include generated file
		include CONFIG_OUTPUT_DIR . $actionName . '.config.php';
		return true;
	}
	
	/**
	 * Executes any type of action, given its name.
	 *
	 * If the action is logic, it is performed. If it is a view, it is displayed.
	 * Performs authentication if needed. Creates action class.
	 * @return bool Whether the action was executed successfully
	 * @param string $actionName Name of action to execute
	 * @access public
	 */
	function performAction($actionName) {
		
		trigger_error('Performing ' . $actionName, E_USER_NOTICE);
		$this->_actionChain[] = $actionName;
		
		if (!is_readable(CODE_DIR . 'actions/' . $actionName . '.class.php')) {
			trigger_error('Cannot read class for action ' . $actionName, E_USER_WARNING);
			return false;
		}
		
		if ($this->_authConfig['use-auth'] == true) {
			include_once PHIEND_DIR . 'AuthManager.class.php';
			//perform authentication
			if (!isset($this->_auth)) {
				$this->_auth = new AuthManager($this->_sessionConfig, $this->_authConfig);
			}
			$authResult = $this->_auth->performAuth($this->actionConfig);
			$this->_authResults[] = $authResult;
			if ($authResult != _PHIEND_AUTH_OK) {
				trigger_error('Auth failed for action ' . $actionName . ', code ' . $authResult, E_USER_NOTICE);
				return false;
			}
		}
		
		//create action object
		include_once CODE_DIR . 'actions/' . $actionName . '.class.php';
		$action = new $actionName;
		
		if ($this->actionConfig['type'] == _PHIEND_ACTION_LOGIC) {
			//logic action
			return $action->perform();
		} else {
			//view action
			$action->display();
			return true;
		}	
	}

	/**
	 * Retrieve an action parameter.
	 *
	 * @return mixed The variable requested, or null if $varName not set
	 * @param string $varName Name of variable to fetch
	 * @access public
	 * @author Peter Chiocchetti
	 */
	function getParam($varName) {
		 return isset($this->params[$varName]) ? $this->params[$varName] : null;
	}
		
	/**
	 * Retrieve a variable set by application.
	 *
	 * The variable can be either request-scope or session-scope.
	 * There is no way to choose scope.
	 * There is also no way to list all currently set variables.
	 * User variables cannot begin with _phiend_.
	 * @return mixed The variable requested, or null if $varName begins with _phiend_
	 * @param string $varName Name of variable to fetch
	 * @access public
	 */
	function getUserVar($varName) {
	if (strncmp($varName, '_phiend_', 8) == 0) {
			return null;
		}
		if (($this->_sessionConfig['use-sessions'] == true) && isset($_SESSION[$varName])) {
			return $_SESSION[$varName];
		} else if (isset($this->_userVars[$varName])) {
			return $this->_userVars[$varName];
		} else {
			return null;
		}
	}
	
	/**
	 * Set the value of an application-defined variable.
	 *
	 * If a variable with given name already exists, it will be replaced, regardless of scope.
	 * Variables cannot start with _phiend_. Also, session-scope variables cannot be set if session support is off.
	 * @return bool Whether it was successful
	 * @param string $varName Name of variable to set
	 * @param mixed $varValue Value to set
	 * @param bool $permament Set a session-scope variable, instead of request-scope
	 * @access public
	 */
	function setUserVar($varName, $varValue, $permanent = false) {
	if (strncmp($varName, '_phiend_', 8) == 0) {
			return false;
		}
		$this->removeUserVar($varName);
		if ($permanent == true) {
			if ($this->_sessionConfig['use-sessions'] == true) {
				$_SESSION[$varName] = $varValue;
			} else {
				return false;
			}
		} else {
			$this->_userVars[$varName] = $varValue;
		}
		return true;
	}
	
	/**
	 * Remove an application-set variable.
	 *
	 * The variable will be removed regardless of scope.
	 * @return bool Whether the variable was found
	 * @param $varName Name of variable to remove
	 * @access public
	 */
	function removeUserVar($varName) {
	if (strncmp($varName, '_phiend_', 8) == 0) {
			return false;
		}
		if (isset($_SESSION[$varName])) {
			unset($_SESSION[$varName]);
			return true;
		} elseif (isset($this->_userVars[$varName])) {
			unset($this->_userVars[$varName]);
			return true;
		}
		return false;
	}
	
	/**
	 * Retrieve a property set by phiend.
	 *
	 * Properties are read-only, there is no setter method.
	 * Only a limited number of properties is available,
	 * there is no way to enumerate them or to get all.
	 *
	 * @return mixed Property value, or null if property does not exist
	 * @param string $propName Name of property to fetch
	 * @access public
	 */
	function getProperty($propName) {
		switch (strtolower($propName)) {
			case "username":
				return ($this->_auth) ? $this->_auth->userName : null;
			case 'userroles':
				return ($this->_auth) ? $this->_auth->userRoles : null;
			case 'requiredroles':
				return ($this->_auth) ? $this->_auth->_actionConfig['required-roles'] : null;
			case 'errors':
				return $this->errors;
			case 'actionchain':
				return $this->_actionChain;
			case 'authresults':
				return $this->_authResults;
		}
		return null;
	}
	
//--- variables ---------------------------------------------------------------

	/**
	* Contains all error messges stored by custom error handler.
	* @var array
	* @access public
	*/
	var $errors = array();
	
	/**
	* Contains all log drivers as specified in config file.
	* @var array
	* @access public
	*/
	var $logDrivers = array();

	 /**#@+
	  * One of the variables governing error output.
	  * @var int
	  * @access public
	  */
	var $logLevel = 0;
	var $echoLevel;
	var $dieLevel;
	var $storeLevel;
	/**#@-*/

	/**
	* Whether Phiend is running in debug mode.
	* @var bool
	* @access public
	*/
	var $debugMode = false;
	
	/**
	* If true, Phiend will not parse config file, regardless of mtime.
	* @var bool
	* @access public
	*/
	var $noParse = false;

	/**
	 * Whether logged and echoed error messages should contain full path to file where they were triggered.
	 * @var bool
	 * @access public
	 */
	var $useFullPath = false;

	/**
	 * Session configuration, as generated basing on config file.
	 * @var array
	 * @access private
	 */
	var $_sessionConfig;

	/**
	 * User auth configuration, as generated basing on config file.
	 * @var array
	 * @access private
	 */
	var $_authConfig;

	/**
	 * AuthManager object, created only if auth is needed.
	 * @var object
	 * @access private
	 */
	var $_auth;

	/**
	 * Whether to use custom error codes hack.
	 * @var bool
	 * @access public
	 */
	var $useCustomErrorCodes = false;

	/**
	 * Request-scope variables set by application.
	 * @var array
	 * @access private
	 */
	var $_userVars = array();

	/**
	 * Names of all actions executed during this request, in an array.
	 * @var array
	 * @access private
	 */
	var $_actionChain = array();

	/**
	 * Result of authentication (one of constants _PHIEND_AUTH_something) for all actions for this request, or empty array if auth not used.
	 * @var array
	 * @access private
	 */
	var $_authResults = array();

	/**
	 * Current action parameters.
	 * @var array
	 * @access private
	 */
	
	var $params = array();

	/**
	 * Current action config.
	 * @var array
	 * @access private
	 */
	var $actionConfig = array();
}

?>
