<?php
/* require_once LIB_DIR . 'MyAction.class.php'; */

class Action_Zapisz extends MyAction {

    var $dane_new = null;
    var $dane_old = array();
    var $name     = null;

    function init($name = null, $save=true) {
        if ($name == null)
            $name = ucfirst($this->getSearch('rodzaj')) . ucfirst($this->getSearch('suffix'));
        
        $id_name = $this->getNameOfPrimaryKey(strtolower($name));

        $id = parent :: findId($id_name);

        $this->name = $name;
        $this->dane_new = parent :: get('dane');
        if (class_exists($this->name)) {
            // NOWE PODEJSCIE
            if (strlen($id) > 0) $this->dane_old = new $name($id);

            // Rozpoczecie transakcji
            $this->begin();
    
            // Zapisanie podstawowego rekordu 
            if ($save) {
              $instance = new $name();
              return $instance->setAllData(parent :: get('dane'))->persist();
            }
            else return null;
        } else {
            // STARE PODEJSCIE - BEZ MODELI
            if (strlen($id) > 0) $this->dane_old = parent :: LoadRow(strtolower($name), $id);
    
            // Rozpoczecie transakcji
            $this->begin();
    
            // Zapisanie podstawowego rekordu 
            if ($save)
              return $this->save(strtolower($name), parent :: get('dane'));
            else return array();
        }
        
    }

/*
    function save($table_name, $data) {
        return $this->saveRow($table_name, $data);
    }
*/

/*
    function zapiszHistorie($id, $pola, $naglowek_n = "Utworzono", $naglowek_u = "Zmiana danych") {
        // Sprawdzenie, czy mamy doczynienia z nowym wpisem, czy nie
        $opis     = "";
        $naglowek = "";
        $autor    = parent :: get('login');

        if (count($this->dane_old) > 0) {
            $naglowek = $naglowek_u;
            foreach($pola as $pole) {
                if ($this->dane_new[$pole] != $this->dane_old[$pole]) $opis .= "Pole <b>$pole</b> zmieniono z: <b>".$this->dane_old[$pole]."</b> na: <b>".$this->dane_new[$pole]."</b><br/>";
            }
        } else {
            $naglowek = $naglowek_n;
            $opis = "Dopisanie";
        }

        parent :: query("CALL dopisz_historie($id, '$naglowek', '$opis', '$autor')");
    }
*/

/*
    function delete($table_name, $where = null) {
        $this->deleteRows($table_name, $where);
    }
*/

    function action($name = null) {
        if ($name == null)
            $name = ucfirst($this->getSearch('rodzaj')) . ucfirst($this->getSearch('suffix'));

        // Zakonczenie transakcji
        $ok = $this->finish_action($this->dane_new['unique_hash']);
        parent :: end($ok);
        
        $this->add_to_debuger();
        
        if (parent :: get('savepoint') != "") {
            return parent :: get('savepoint');
        } else
            return $name . "_WczytajListe";
    }
}
?>