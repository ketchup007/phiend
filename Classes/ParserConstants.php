<?php
namespace Phiend;

/**
 * @package phiend
 * @author Maciej 'hawk' Jarzebski
 * @version $Id: ParserConstants.php,v 1.3 2003/10/26 11:30:27 cryonax Exp $
 */

/**#@+
 * Type of tag content.
 */
define('_PHIEND_CONTENT_NONE', 0);
define('_PHIEND_CONTENT_BOOLEAN', 1);
define('_PHIEND_CONTENT_STRING', 2);
define('_PHIEND_CONTENT_INTEGER', 3);
define('_PHIEND_CONTENT_DEFINED', 4);
/**#@-*/

/**#@+
 * A constant for one of the allowed tags.
 */
define('_PHIEND_TAG_NONE', 0);
define('_PHIEND_TAG_PHIEND_CONFIG', 1);

define('_PHIEND_TAG_BASIC_CONFIG', 2);
define('_PHIEND_TAG_USE_CUSTOM_ERROR_CODES', 3);
define('_PHIEND_TAG_USE_REDIRECTS', 4);

define('_PHIEND_TAG_SESSION_CONFIG', 5);
define('_PHIEND_TAG_USE_SESSIONS', 6);
define('_PHIEND_TAG_SESSION_NAME', 7);
define('_PHIEND_TAG_CHECK_IP', 8);

define('_PHIEND_TAG_AUTH_CONFIG', 14);
define('_PHIEND_TAG_USE_AUTH', 15);
define('_PHIEND_TAG_CACHE_USER_ROLES', 16);

define('_PHIEND_TAG_ERROR_CONFIG', 18);
define('_PHIEND_TAG_ERROR_REPORTING', 19);
define('_PHIEND_TAG_LOG_LEVEL', 20);
define('_PHIEND_TAG_ECHO_LEVEL', 21);
define('_PHIEND_TAG_DIE_LEVEL', 22);
define('_PHIEND_TAG_STORE_LEVEL', 23);
define('_PHIEND_TAG_USE_FULL_PATH', 24);

define('_PHIEND_TAG_ACTIONS', 25);
define('_PHIEND_TAG_ACTION', 26);
define('_PHIEND_TAG_NAME', 27);
define('_PHIEND_TAG_INHERIT_FROM', 28);
define('_PHIEND_TAG_ACTION_CONFIG', 29);

define('_PHIEND_TAG_REQUIRED_ROLES', 30);
define('_PHIEND_TAG_FORCE_LOGOUT', 31);
define('_PHIEND_TAG_ACCEPT_PASSWORD', 32);
define('_PHIEND_TAG_ACCEPT_SID', 33);
define('_PHIEND_TAG_USE_HTTPS', 34);
define('_PHIEND_TAG_TYPE', 35);
define('_PHIEND_TAG_FALLBACK_ACTION', 36);

define('_PHIEND_TAG_MATCHES', 37);
define('_PHIEND_TAG_EXACTLY', 38);
define('_PHIEND_TAG_CONTAINS', 39);
define('_PHIEND_TAG_ALWAYS', 40);
define('_PHIEND_TAG_EREG', 41);
define('_PHIEND_TAG_PREG', 42);
define('_PHIEND_TAG_GET_PARAM', 43);

define('_PHIEND_TAG_AUTH_DRIVERS', 45);
define('_PHIEND_TAG_AUTH_DRIVER', 46);
define('_PHIEND_TAG_USER_SUPPLIED', 48);
define('_PHIEND_TAG_PARAM', 49);

define('_PHIEND_TAG_LOG_DRIVERS', 50);
define('_PHIEND_TAG_LOG_DRIVER', 51);
/**#@-*/

/**
 * Names for all allowed tags.
 * @var array
 */
$GLOBALS['_phiend_tagNames'] = array(
	_PHIEND_TAG_PHIEND_CONFIG			=> 'phiend-config',
	_PHIEND_TAG_BASIC_CONFIG			=> 'basic-config',
	_PHIEND_TAG_USE_CUSTOM_ERROR_CODES	=> 'use-custom-error-codes',
	_PHIEND_TAG_USE_REDIRECTS			=> 'use-redirects',
	_PHIEND_TAG_SESSION_CONFIG			=> 'session-config',
	_PHIEND_TAG_USE_SESSIONS			=> 'use-sessions',
	_PHIEND_TAG_SESSION_NAME			=> 'session-name',
	_PHIEND_TAG_CHECK_IP				=> 'check-ip',
	_PHIEND_TAG_AUTH_CONFIG				=> 'auth-config',
	_PHIEND_TAG_USE_AUTH				=> 'use-auth',
	_PHIEND_TAG_CACHE_USER_ROLES		=> 'cache-user-roles',
	_PHIEND_TAG_ERROR_CONFIG			=> 'error-config',
	_PHIEND_TAG_ERROR_REPORTING			=> 'error-reporting',
	_PHIEND_TAG_LOG_LEVEL				=> 'log-level',
	_PHIEND_TAG_ECHO_LEVEL				=> 'echo-level',
	_PHIEND_TAG_DIE_LEVEL				=> 'die-level',
	_PHIEND_TAG_STORE_LEVEL				=> 'store-level',
	_PHIEND_TAG_USE_FULL_PATH			=> 'log-full-path',
	_PHIEND_TAG_ACTIONS					=> 'actions',
	_PHIEND_TAG_ACTION					=> 'action',
	_PHIEND_TAG_NAME					=> 'name',
	_PHIEND_TAG_INHERIT_FROM			=> 'inherit-from',
	_PHIEND_TAG_ACTION_CONFIG			=> 'action-config',
	_PHIEND_TAG_REQUIRED_ROLES			=> 'required-roles',
	_PHIEND_TAG_FORCE_LOGOUT			=> 'force-logout',
	_PHIEND_TAG_ACCEPT_PASSWORD			=> 'accept-password',
	_PHIEND_TAG_ACCEPT_SID				=> 'accept-sid',
	_PHIEND_TAG_USE_HTTPS				=> 'use-https',
	_PHIEND_TAG_TYPE					=> 'type',
	_PHIEND_TAG_FALLBACK_ACTION			=> 'fallback-action',
	_PHIEND_TAG_MATCHES					=> 'matches',
	_PHIEND_TAG_EXACTLY					=> 'exactly',
	_PHIEND_TAG_CONTAINS				=> 'contains',
	_PHIEND_TAG_ALWAYS					=> 'always',
	_PHIEND_TAG_EREG					=> 'ereg',
	_PHIEND_TAG_PREG					=> 'preg',
	_PHIEND_TAG_GET_PARAM				=> 'get-param',
	_PHIEND_TAG_AUTH_DRIVERS			=> 'auth-drivers',
	_PHIEND_TAG_AUTH_DRIVER				=> 'auth-driver',
	_PHIEND_TAG_USER_SUPPLIED			=> 'user-supplied',
	_PHIEND_TAG_PARAM					=> 'param',
	_PHIEND_TAG_LOG_DRIVERS				=> 'log-drivers',
	_PHIEND_TAG_LOG_DRIVER				=> 'log-driver',
);

/**
 * Type of content for all tags
 * @var array
 */
$GLOBALS['_phiend_tagContent'] = array(
	_PHIEND_TAG_PHIEND_CONFIG			=> _PHIEND_CONTENT_NONE,
	_PHIEND_TAG_BASIC_CONFIG			=> _PHIEND_CONTENT_NONE,
	_PHIEND_TAG_USE_CUSTOM_ERROR_CODES	=> _PHIEND_CONTENT_BOOLEAN,
	_PHIEND_TAG_USE_REDIRECTS			=> _PHIEND_CONTENT_BOOLEAN,
	_PHIEND_TAG_SESSION_CONFIG			=> _PHIEND_CONTENT_NONE,
	_PHIEND_TAG_USE_SESSIONS			=> _PHIEND_CONTENT_BOOLEAN,
	_PHIEND_TAG_SESSION_NAME			=> _PHIEND_CONTENT_STRING,
	_PHIEND_TAG_CHECK_IP				=> _PHIEND_CONTENT_BOOLEAN,
	_PHIEND_TAG_AUTH_CONFIG				=> _PHIEND_CONTENT_NONE,
	_PHIEND_TAG_USE_AUTH				=> _PHIEND_CONTENT_BOOLEAN,
	_PHIEND_TAG_CACHE_USER_ROLES		=> _PHIEND_CONTENT_BOOLEAN,
	_PHIEND_TAG_ERROR_CONFIG			=> _PHIEND_CONTENT_NONE,
	_PHIEND_TAG_ERROR_REPORTING			=> _PHIEND_CONTENT_DEFINED,
	_PHIEND_TAG_LOG_LEVEL				=> _PHIEND_CONTENT_DEFINED,
	_PHIEND_TAG_ECHO_LEVEL				=> _PHIEND_CONTENT_DEFINED,
	_PHIEND_TAG_DIE_LEVEL				=> _PHIEND_CONTENT_DEFINED,
	_PHIEND_TAG_STORE_LEVEL				=> _PHIEND_CONTENT_DEFINED,
	_PHIEND_TAG_USE_FULL_PATH			=> _PHIEND_CONTENT_BOOLEAN,
	_PHIEND_TAG_ACTIONS					=> _PHIEND_CONTENT_NONE,
	_PHIEND_TAG_ACTION					=> _PHIEND_CONTENT_NONE,
	_PHIEND_TAG_NAME					=> _PHIEND_CONTENT_STRING,
	_PHIEND_TAG_INHERIT_FROM			=> _PHIEND_CONTENT_STRING,
	_PHIEND_TAG_ACTION_CONFIG			=> _PHIEND_CONTENT_NONE,
	_PHIEND_TAG_REQUIRED_ROLES			=> _PHIEND_CONTENT_STRING,
	_PHIEND_TAG_FORCE_LOGOUT			=> _PHIEND_CONTENT_BOOLEAN,
	_PHIEND_TAG_ACCEPT_PASSWORD			=> _PHIEND_CONTENT_BOOLEAN,
	_PHIEND_TAG_ACCEPT_SID				=> _PHIEND_CONTENT_BOOLEAN,
	_PHIEND_TAG_USE_HTTPS				=> _PHIEND_CONTENT_BOOLEAN,
	_PHIEND_TAG_TYPE					=> _PHIEND_CONTENT_DEFINED,
	_PHIEND_TAG_FALLBACK_ACTION			=> _PHIEND_CONTENT_STRING,
	_PHIEND_TAG_MATCHES					=> _PHIEND_CONTENT_NONE,
	_PHIEND_TAG_EXACTLY					=> _PHIEND_CONTENT_STRING,
	_PHIEND_TAG_CONTAINS				=> _PHIEND_CONTENT_STRING,
	_PHIEND_TAG_ALWAYS					=> _PHIEND_CONTENT_NONE,
	_PHIEND_TAG_EREG					=> _PHIEND_CONTENT_STRING,
	_PHIEND_TAG_PREG					=> _PHIEND_CONTENT_STRING,
	_PHIEND_TAG_GET_PARAM				=> _PHIEND_CONTENT_STRING,
	_PHIEND_TAG_AUTH_DRIVERS			=> _PHIEND_CONTENT_NONE,
	_PHIEND_TAG_AUTH_DRIVER				=> _PHIEND_CONTENT_NONE,
	_PHIEND_TAG_USER_SUPPLIED			=> _PHIEND_CONTENT_BOOLEAN,
	_PHIEND_TAG_PARAM					=> _PHIEND_CONTENT_STRING,
	_PHIEND_TAG_LOG_DRIVERS				=> _PHIEND_CONTENT_NONE,
	_PHIEND_TAG_LOG_DRIVER				=> _PHIEND_CONTENT_NONE,
);

/**
 * Default content for all tags.
 * @var array
 */
$GLOBALS['_phiend_defaultTagContent'] = array(
	_PHIEND_TAG_USE_CUSTOM_ERROR_CODES	=> true,
	_PHIEND_TAG_USE_REDIRECTS			=> false,
	_PHIEND_TAG_USE_SESSIONS			=> false,
	_PHIEND_TAG_SESSION_NAME			=> 'phiend',
	_PHIEND_TAG_CHECK_IP				=> false,
	_PHIEND_TAG_USE_AUTH				=> false,
	_PHIEND_TAG_CACHE_USER_ROLES		=> false,
	_PHIEND_TAG_ERROR_REPORTING			=> 'E_ALL',
	_PHIEND_TAG_LOG_LEVEL				=> 'E_ALL',
	_PHIEND_TAG_ECHO_LEVEL				=> 'E_ALL ^ (E_NOTICE | E_USER_NOTICE)',
	_PHIEND_TAG_DIE_LEVEL				=> 'E_ERROR | E_USER_ERROR | E_COMPILE_ERROR | E_CORE_ERROR | E_PARSE',
	_PHIEND_TAG_STORE_LEVEL				=> 'E_ALL',
	_PHIEND_TAG_USE_FULL_PATH			=> false,
	_PHIEND_TAG_NAME					=> 'DefaultName',
	_PHIEND_TAG_INHERIT_FROM			=> '',
	_PHIEND_TAG_REQUIRED_ROLES			=> '',
	_PHIEND_TAG_FORCE_LOGOUT			=> false,
	_PHIEND_TAG_ACCEPT_PASSWORD			=> false,
	_PHIEND_TAG_ACCEPT_SID				=> false,
	_PHIEND_TAG_USE_HTTPS				=> false,
	_PHIEND_TAG_TYPE					=> '_PHIEND_ACTION_LOGIC',
	_PHIEND_TAG_FALLBACK_ACTION			=> '',
	_PHIEND_TAG_EXACTLY					=> '',
	_PHIEND_TAG_CONTAINS				=> '',
	_PHIEND_TAG_EREG					=> '',
	_PHIEND_TAG_PREG					=> '',
	_PHIEND_TAG_GET_PARAM				=> '',
	_PHIEND_TAG_USER_SUPPLIED			=> false,
	_PHIEND_TAG_PARAM					=> '',
);

/**
 * Allowed sub-tags for all tags.
 * @var array
 */
$GLOBALS['_phiend_allowedSubTags'] = array(
	_PHIEND_TAG_PHIEND_CONFIG			=> array(	_PHIEND_TAG_BASIC_CONFIG,
													_PHIEND_TAG_SESSION_CONFIG,
													_PHIEND_TAG_AUTH_CONFIG,
													_PHIEND_TAG_ERROR_CONFIG,
													_PHIEND_TAG_ACTIONS,
													_PHIEND_TAG_AUTH_DRIVERS,
													_PHIEND_TAG_LOG_DRIVERS,
												),
	_PHIEND_TAG_BASIC_CONFIG			=> array(	_PHIEND_TAG_USE_REDIRECTS,
												),
	_PHIEND_TAG_SESSION_CONFIG			=> array(	_PHIEND_TAG_USE_SESSIONS,
													_PHIEND_TAG_SESSION_NAME,
													_PHIEND_TAG_CHECK_IP,
												),
	_PHIEND_TAG_AUTH_CONFIG				=> array(	_PHIEND_TAG_USE_AUTH,
													_PHIEND_TAG_CACHE_USER_ROLES,
												),
	_PHIEND_TAG_ERROR_CONFIG			=> array(	_PHIEND_TAG_USE_FULL_PATH,
													_PHIEND_TAG_ERROR_REPORTING,
													_PHIEND_TAG_LOG_LEVEL,
													_PHIEND_TAG_ECHO_LEVEL,
													_PHIEND_TAG_DIE_LEVEL,
													_PHIEND_TAG_STORE_LEVEL,
													_PHIEND_TAG_USE_CUSTOM_ERROR_CODES,
												),
	_PHIEND_TAG_USE_CUSTOM_ERROR_CODES	=> array(),
	_PHIEND_TAG_USE_REDIRECTS			=> array(),
	_PHIEND_TAG_USE_SESSIONS			=> array(),
	_PHIEND_TAG_SESSION_NAME			=> array(),
	_PHIEND_TAG_CHECK_IP				=> array(),
	_PHIEND_TAG_USE_AUTH				=> array(),
	_PHIEND_TAG_CACHE_USER_ROLES		=> array(),
	_PHIEND_TAG_ERROR_REPORTING			=> array(),
	_PHIEND_TAG_LOG_LEVEL				=> array(),
	_PHIEND_TAG_ECHO_LEVEL				=> array(),
	_PHIEND_TAG_DIE_LEVEL				=> array(),
	_PHIEND_TAG_STORE_LEVEL				=> array(),
	_PHIEND_TAG_USE_FULL_PATH			=> array(),
	_PHIEND_TAG_ACTIONS					=> array(	_PHIEND_TAG_ACTION,
												),
	_PHIEND_TAG_ACTION					=> array(	_PHIEND_TAG_NAME,
													_PHIEND_TAG_INHERIT_FROM,
													_PHIEND_TAG_PARAM,
													_PHIEND_TAG_ACTION_CONFIG,
													_PHIEND_TAG_MATCHES,
												),
	_PHIEND_TAG_NAME					=> array(),
	_PHIEND_TAG_INHERIT_FROM			=> array(),
	_PHIEND_TAG_ACTION_CONFIG			=> array(	_PHIEND_TAG_REQUIRED_ROLES,
													_PHIEND_TAG_FORCE_LOGOUT,
													_PHIEND_TAG_ACCEPT_PASSWORD,
													_PHIEND_TAG_ACCEPT_SID,
													_PHIEND_TAG_USE_HTTPS,
													_PHIEND_TAG_FALLBACK_ACTION,
													_PHIEND_TAG_TYPE,
												),
	_PHIEND_TAG_REQUIRED_ROLES			=> array(),
	_PHIEND_TAG_FORCE_LOGOUT			=> array(),
	_PHIEND_TAG_ACCEPT_PASSWORD			=> array(),
	_PHIEND_TAG_ACCEPT_SID				=> array(),
	_PHIEND_TAG_USE_HTTPS				=> array(),
	_PHIEND_TAG_TYPE					=> array(),
	_PHIEND_TAG_FALLBACK_ACTION			=> array(),
	_PHIEND_TAG_MATCHES					=> array(	_PHIEND_TAG_EXACTLY,
													_PHIEND_TAG_CONTAINS,
													_PHIEND_TAG_ALWAYS,
													_PHIEND_TAG_EREG,
													_PHIEND_TAG_PREG,
													_PHIEND_TAG_GET_PARAM,
												),
	_PHIEND_TAG_EXACTLY					=> array(),
	_PHIEND_TAG_CONTAINS				=> array(),
	_PHIEND_TAG_ALWAYS					=> array(),
	_PHIEND_TAG_EREG					=> array(),
	_PHIEND_TAG_PREG					=> array(),
	_PHIEND_TAG_GET_PARAM				=> array(),
	_PHIEND_TAG_AUTH_DRIVERS			=> array(	_PHIEND_TAG_AUTH_DRIVER
												),
	_PHIEND_TAG_AUTH_DRIVER				=> array(	_PHIEND_TAG_NAME,
													_PHIEND_TAG_USER_SUPPLIED,
													_PHIEND_TAG_PARAM,
												),
	_PHIEND_TAG_USER_SUPPLIED			=> array(),
	_PHIEND_TAG_PARAM					=> array(),
	_PHIEND_TAG_LOG_DRIVERS				=> array(	_PHIEND_TAG_LOG_DRIVER
												),
	_PHIEND_TAG_LOG_DRIVER				=> array(	_PHIEND_TAG_NAME,
													_PHIEND_TAG_USER_SUPPLIED,
													_PHIEND_TAG_PARAM,
												),
);

/**#@+
 * Template for ConfigParser internal table, filled with default in it's constructor.
 * @var array
 */
$GLOBALS['_phiend_defaultAction'] = array(
	_PHIEND_TAG_NAME => null,
	_PHIEND_TAG_INHERIT_FROM => null,
	_PHIEND_TAG_ACTION_CONFIG => array(
		_PHIEND_TAG_REQUIRED_ROLES => null,
		_PHIEND_TAG_FORCE_LOGOUT => null,
		_PHIEND_TAG_ACCEPT_PASSWORD => null,
		_PHIEND_TAG_ACCEPT_SID => null,
		_PHIEND_TAG_USE_HTTPS => null,
		_PHIEND_TAG_TYPE => null,
		_PHIEND_TAG_FALLBACK_ACTION => null,
	),
	_PHIEND_TAG_MATCHES => array(),
	_PHIEND_TAG_PARAM => array(),
);
$GLOBALS['_phiend_defaultAuthDriver'] = array(
	_PHIEND_TAG_NAME => null,
	_PHIEND_TAG_USER_SUPPLIED => null,
	_PHIEND_TAG_PARAM => array(),
);
$GLOBALS['_phiend_defaultLogDriver'] = array(
	_PHIEND_TAG_NAME => null,
	_PHIEND_TAG_USER_SUPPLIED => null,
	_PHIEND_TAG_PARAM => array(),
);
/**#@-*/

?>