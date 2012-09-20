<?php

namespace Phiend\Extends;

class Action_Dopisz extends MyAction {

    function init($name = null) {
        if ($name == null) $name = ucfirst($this->getSearch('rodzaj'));
        
        // Czyszczenie danych z formatki glownej
        parent :: set('dane', null);

        // stworzenie unikalnej akcji - zabezpieczenie przed powtorzeniami
        parent :: start_action();

    }
    
    function action($name = null) {
        parent::set('rodzaj_akcji', 'Dopisywanie');
        if ($name == null) $name = ucfirst($this->getSearch('rodzaj')).ucfirst($this->getSearch('suffix'));
        $this->add_to_debuger();
        return $name."_Edytuj";
    }
}
?>

