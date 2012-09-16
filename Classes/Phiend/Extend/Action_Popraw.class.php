<?php
/* require_once LIB_DIR . 'MyAction.class.php'; */
namespace Phiend\Extends;

class Action_Popraw extends MyAction {

    function init($name = null) {
        if ($name == null) $name = ucfirst($this->getSearch('rodzaj'));

		$id_name = $this->getNameOfPrimaryKey(strtolower($name));
        $id      = parent::findId($id_name);

        // Zaladowanie wiersza z tabeli
        if ($id > 0) parent::set('dane', parent::LoadRow(strtolower($name),  $id));

        // Zapisanie
        parent::set($id_name, $id, true);
        
        // Zwrocenie glownego wiersza
        return parent::get('dane');
    }

    function action($name = null) {
        parent::set('rodzaj_akcji', 'Edycja');
        $this->add_to_debuger();
        if ($name == null) $name = ucfirst($this->getSearch('rodzaj')).ucfirst($this->getSearch('suffix'));
        return $name."_Edytuj";
    }
}
?>

