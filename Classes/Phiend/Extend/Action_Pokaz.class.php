<?php
/* require_once LIB_DIR . 'MyAction.class.php'; */
namespace Phiend\Extends;

class Action_Pokaz extends MyAction {

	function init($name = null) {
	    parent :: setSavePoint(get_class($this));

		// Pobranie nazwy id glownej tabeli
		if ($name == null) $name = ucfirst($this->getSearch('rodzaj'));

		$id_name = $this->getNameOfPrimaryKey(strtolower($name));
		$id = parent :: findId($id_name);

		// Zaladowanie wiersza z tabeli
		parent :: set('dane', parent :: LoadRow(strtolower($name), $id));
		parent :: set('return', ucfirst($name).'_Pokaz', true);
		
		// Zapisanie
		return parent :: set($id_name, $id, true);
	}

	function multi($column) {
		// Pobranie nazwy id glownej tabeli
		//$id_name = $table_keys[$this->getSearch('rodzaj')]['pk'][0];
		$id = parent :: findId($column);

		// Zaladowanie wiersza z tabeli
		parent :: set($this->getSearch('rodzaj'), parent :: LoadRows($this->getSearch('rodzaj'), "$column = $id"));
		// Zapisanie
		parent :: set($id_name, $id, true);
	}

	function action($name = null) {
		if ($name == null) $name = ucfirst($this->getSearch('rodzaj'));
    $this->add_to_debuger();
		return $name . "_Wyswietl";
	}
}
?>
