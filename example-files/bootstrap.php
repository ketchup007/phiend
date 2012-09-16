<?php

// $Id: bootstrap.php,v 1.3 2003/09/27 17:32:46 cryonax Exp $

define('PHIEND_DIR' , '/path/to/phiend/library/');
define('CODE_DIR', '/path/to/your/php/files/');
define('SESSION_DIR', '/path/to/session/directory/');
define('LOG_DIR', '/path/to/log/directory/');
define('CONFIG_OUTPUT_DIR', '/path/to/config/output/directory/');

// Place your own initialization here
//session_set_cookie_params(0);

require_once PHIEND_DIR . 'ActionController.class.php';

$actionController = & new ActionController();

// Set ActionController variables
//$actionController->debugMode = true;

$actionController->run();

?>