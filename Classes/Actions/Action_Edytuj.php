<?php
/*
require_once LIB_DIR . 'MySmarty.class.php';
require_once LIB_DIR . 'MyAction.class.php';
*/

class Action_Edytuj extends MyAction {

	var $smarty;

	function init() {
  		// Uruchomienie silnika Smarty
  		$this->smarty = new MySmarty;
  		$this->view('error');
/*   		parent :: generateMenu(); */
  
      $dane = parent :: get('dane'); 
      
      // stworzenie unikalnej akcji - zabezpieczenie przed powtorzeniami
      $dane['unique_hash']  = parent :: start_action();
  
      $dane = parent :: set('dane', $dane); 
      
      return $dane;
	}

	function view($label, $variable = null) {
		if ($variable == null)
			$this->smarty->assign($label, $this->_getUserVar($label));
		else
			$this->smarty->assign($label, $variable);
	}

	function setAccept($action=null, $label='Zapisz dane') {
		$buttons = $this->get('buttons_bottom');
		if (is_null($action)) {
		  unset($buttons['accept']);
		} else {
  		$buttons['accept'] = array (
  			'name'      => 'zapisz',
  			'label'     => $label,
  			'icon'      => 'ok.png',
  			'accesskey' => 's',
  			'action'    => $action
  		);
    }
		$this->set('buttons_bottom', $buttons);
	}

	function setCancel($action=null, $label='Anuluj', $description=null) {
		$buttons = $this->get('buttons_bottom');
		if (is_null($action)) {
		  unset($buttons['cancel']);
		} else {
  		$buttons['cancel'] = array (
  			'name'   => 'anuluj',
  			'label'  => $label,
  			'icon'   => 'no.png',
  			'description' => $description,
  			'action' => $action
  		);
		}
		$this->set('buttons_bottom', $buttons);
	}

	function setReturn($action) {
/* 		$buttons = $this->get('buttons_bottom'); */
		$buttons = $this->buttons_bottom;
		$buttons['return'] = array (
			'name' => 'return',
			'action' => $action
		);
/* 		$this->set('buttons_bottom', $buttons); */
		$this->buttons_bottom = $buttons;
	}

	function addHelp($pole, $tekst) {
		$help = $this->get('help');
		$help[$pole] = array (
			'sekcja' => $sekcja,
			'tekst' => $tekst
		);
		$this->set('help', $help);
	}

	function action($name = null) {
		include CONFIG_DIR . "config.php";

		$this->smarty->assign('ACTION_METHOD', ACTION_METHOD);
		$this->smarty->assign('tn', $tn);
		$this->view('dane');
		$this->view('buttons');
		$this->view('buttons_bottom');
		$this->view('help');
		$this->view('menu');
		$this->view('rodzaj_akcji');
		
        $this->add_to_debuger();

		// Wyswietlenie wynikow
		if ($name == null)
			$this->smarty->finish("Formatka_" . ucfirst($this->getSearch('rodzaj')) . ucfirst($this->getSearch('suffix')) . ".tpl");
		else
			$this->smarty->finish("Formatka_" . ucfirst($name) . ".tpl");
	}
}
?>

