<?php

// load Smarty library
/* require_once SMARTY_DIR . 'Smarty.class.php'; */
/*
function print_current_date($params, &$smarty)
{
  if(empty($params['format'])) {
    $format = "%b %e, %Y";
  } else {
    $format = $params['format'];
  }
  return strftime($format,time());
}
*/

/*
function smarty_function_custom_class($params, &$smarty)
{
    $class = $params['class'];       
    $smarty->assign($params['var'], new $class());  
}  
*/

function smarty_date_diff($params, &$smarty) {

    $date1  = "now";
    $date2  = "now";
    $format = '%r%a';
    
    $assign = null;
    
    extract($params);
    
    if($assign != null) {
        $smarty->assign($assign, date_diff(date_create($date1), date_create($date2))->format($format));
    } else {
        return date_diff(date_create($date1), date_create($date2))->format($format);
    } 
}

class MySmarty extends Smarty {

    public $buttons         = array();
    public $buttons_bottom  = array();
    public $is_search       = false;
    public $can_be_exported = false;

    function microtime_float($time) {
        list($usec, $sec) = explode(" ", $time);
        return ((float)$usec + (float)$sec);
    }

    function addButton($name, $icon, $access, $label, $action, $preaction=null, $new_window=false) {
        // Wyciagniecie uprawnienia z linku (opzbycie sie parametrow)
        $params = strstr($action, '?');
        $rola   = substr($action, 0, strlen($action) - strlen($params));
        if (strpos($GLOBALS['_phiend_actionController']->getUserVar('roles'), $rola) !== false)
            $this->buttons[$name] = array('name' => $name, 'icon' => $icon, 'access' => $access, 'label' => $label, 'action' => $action, 'preaction' => $preaction, 'new_window' => $new_window);
    }

    function getButtons() {
        return $this->buttons;
    }

    function setExportToExcel($can_be_exported) {
        $this->can_be_exported = $can_be_exported;
    }

/*
    function setSearch($is_search) {
        $this->is_search = $is_search;
    }

*/
    function setAction($name){
        $this->assign('SearchAction', $name);
    }

    function smarty_function_custom_class($params, &$smarty) {
        $class = $params['class'];       
        $smarty->assign($params['var'], new $class());  
    }  

    function __construct()
    {
        require CONFIG_DIR . "config.php";

        parent::__construct();
        
        $this->template_dir  = CODE_DIR . 'templates/smarty/';
        $this->compile_dir   = 'private/var/templates_c/';

/*         $this->addPluginsDir('application/code/templates/smarty/plugins/'); */
        $this->registerPlugin("function", "date_diff", "smarty_date_diff");
        
        $this->config_dir    = CONFIG_DIR;

        $this->config_booleanize = true;
        $this->assign('app_name',       APP_NAME);
        $this->assign('app_version',    APP_VERSION);
        $this->assign('db_version',     $GLOBALS['_phiend_actionController']->getUserVar('db_version'));
        
        $this->assign('authorised_user',   $GLOBALS['_phiend_actionController']->getUserVar('authorised_user'));

        // Data
        list($r,$m,$d) = explode('-', $GLOBALS['_phiend_actionController']->getUserVar('today'));
        $this->assign('today', date("Y-m-d"));
        $this->assign('now', date("Y-m-d H:i:s"));
        $this->assign('lata', $lata);
        $this->assign('miesiace', $miesiace);
        $this->assign('DEBUG', DEBUG);
        $this->assign('ACTION_METHOD',   ACTION_METHOD);
        $this->assign('TINYMCE_VERSION', TINYMCE_VERSION);
        $this->assign('GFX16', 'gfx/16x16/');
        $this->assign('GFX22', 'gfx/22x22/');
        $this->assign('GFX32', 'gfx/32x32/');
        
        $this->assign('menu', $GLOBALS['_phiend_actionController']->getUserVar('menu'));
    }

    function finish($plik) {
        $search = $GLOBALS['_phiend_actionController']->getUserVar('search');

        // Czy mozna exportowac do EXCELa
        $this->assign('can_be_exported', $this->can_be_exported);

        // Dolaczenie przeszukiwania
        $this->assign('is_search', $this->is_search);
        $this->assign('config_file', $GLOBALS['_phiend_actionController']->getUserVar('config_file'));

        // Dolaczenie przyciskow
        if (count($this->buttons) > 0) {
            $this->assign('buttons',      $this->buttons);
        }   
        
        if (count($this->buttons_bottom) > 0) {
            $this->assign('buttons_bottom',      $this->buttons_bottom);
        }   

        // Wyswietlanie debuga
        if (DEBUG == 1) $this->assign('errors', $GLOBALS['_phiend_actionController']->getProperty("Errors"));

				// Wersja aplikacji
        $this->assign('app_version', $GLOBALS['_phiend_actionController']->getUserVar('app_version'));
				
        // Ustawienie czasu i zuzycia pamieci
        $this->assign('time', (float) ($this->microtime_float(microtime()) - (float) $GLOBALS['_SERVER']['REQUEST_TIME']));
        $this->assign('memory', memory_get_peak_usage() / 1000000);
        $this->display($plik);
    }
}

