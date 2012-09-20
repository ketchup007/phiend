<?php

/* namespace Phiend; */

/**
 * @package phiend
 * @author Maciej Jarzebski
 * @version $Id: Action.class.php,v 1.3 2004/04/16 18:47:56 cryonax Exp $
 */

 /**
 * Base class for all actions.
 *
 * All actions must inherit from this class.
 * Contains methods to access Phiend utilities from within the action.
 * @package phiend
 */
class Action {

	/**
	 * Perform another action outside of action chain.
	 *
	 * The given action is performed as normal, but instead of chaining, its output is returned.
	 * @access private
	 * @return bool Whether the action was successfully executed
	 * @param string $actionName Name of action to execute
	 */
	function _callAction($actionName) {
		if ($GLOBALS['_phiend_actionController']->readConfig($actionName)) {
			return $GLOBALS['_phiend_actionController']->performAction($actionName);
		}
	}

	/**
	 * Get action parameter.
	 *
	 * @return mixed The variable requested, or null if $varName not set
	 * @param string $varName Name of variable to fetch
	 * @access protected
	 * @author Peter Chiocchetti
	 */
	function _getParam($varName) {
		return $GLOBALS['_phiend_actionController']->getParam($varName);
	}
	
	/**
	 * Get application-defined variable.
	 *
	 * @return mixed The variable requested, or null if $varName begins with _phiend_
	 * @param string $varName Name of variable to fetch
	 */
	function _getUserVar($varName) {
		return $GLOBALS['_phiend_actionController']->getUserVar($varName);
	}

	/**
	 * Set application-defined variable.
	 *
	 * @return Whether it was successful
	 * @param string $varName Name of variable to set
	 * @param mixed $varValue Value to set
	 * @param bool $permament Set a session-scope variable, instead of request-scope
	 */
	function _setUserVar($varName, $varValue, $permanent = false) {
		return $GLOBALS['_phiend_actionController']->setUserVar($varName, $varValue, $permanent);
	}

	/**
	 * Remove application-defined variable.
	 *
	 * @return bool Whether the variable was found
	 * @param $varName Name of variable to remove
	 */
	function _removeUserVar($varName) {
		return $GLOBALS['_phiend_actionController']->removeUserVar($varName);
	}
	
	/**
	 * Get phiend property.
	 *
	 * @return mixed Property value, or null if property does not exist
	 * @param string $propName Name of property to fetch
	 */
	function _getProperty($propName) {
		return $GLOBALS['_phiend_actionController']->getProperty($propName);
	}
}

?>