<?php
/*
require_once LIB_DIR . 'MySmarty.class.php';
require_once LIB_DIR . 'MyAction.class.php';
*/

class Action_Wyswietl extends MyAction {

	var $smarty;

	function init() {
		// Uruchomienie silnika Smarty
		$this->smarty = new MySmarty;
/* 		parent :: generateMenu(); */

		$search = $this->getSearch();

		// ZMIANA ZAKLADKI
		if (isSet ($_GET['typ'])) {
			if (strcmp($_GET['typ'], $search['typ']) != 0) {
				$search['typ'] = $_GET['typ'];
				$search['ord_by'] = "";
				$search['kierunek'] = "";
			}
		}
		$this->_setUserVar('search', $search, true);
	}

	function assign($label, $zmienna) {
		$this->smarty->assign($label, $zmienna);
	}

	function addButton($name, $icon, $access, $label, $action, $preaction=null) {
		$this->smarty->addButton($name, $icon, $access, $label, $action, $preaction);
	}

	function setAction($action) {
		$this->smarty->setAction($action);
	}

	function view($label, $variable = null) {
		if ($variable == null)
			$this->smarty->assign($label, $this->_getUserVar($label));
		else
			$this->smarty->assign($label, $variable);
	}

	function action($name = null) {
		$this->smarty->assign('dane', $this->get('dane'));
		$this->smarty->assign('search', $this->getSearch());
		$this->dbg('search');
		$this->view('menu');
    $this->add_to_debuger();
		if ($name != null)
			return $this->smarty->finish($name . ".tpl");
		return $this->smarty->finish("Pokaz_" . ucfirst($this->getSearch('rodzaj')) . ".tpl");
	}

	function dbg($name) {
		$dbg = "<pre>" . str_replace("\n", "<br>", print_r($this->get($name), true)) . "</pre>";
		$this->view("dbg_$name", "$name<br>$dbg");
	}
}
?>
