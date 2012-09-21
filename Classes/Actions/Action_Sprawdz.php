<?php
/* require_once LIB_DIR . 'MyAction.class.php'; */

class Action_Sprawdz extends MyAction {

    var $ok = true;

    function init($name = null) {
        if ($name == null)
            $name = ucfirst($this->getSearch('rodzaj')) . ucfirst($this->getSearch('suffix'));            
        $this->ok = true;
        return $this->set('dane');
    }

    function set($name, $dane = null) {
        if ($dane != null)
            return parent :: set($name, $dane);
        if (is_array($dane))
          if (count($dane) == 0)
            return parent :: set($name, null);
        $vars = & $GLOBALS["_" . ACTION_METHOD];
        return parent :: set($name, $vars[$name]);
    }

    function get($name) {
        $vars = & $GLOBALS["_" . ACTION_METHOD];
        if (isSet($vars[$name])) return $vars[$name];
        else return parent :: get($name);
    }

    function setFalse($name = null, $komunikat = null) {
        $this->ok = false;
        $this->addError($name, $komunikat);
    }

    function getRequest($name = null) {
        if ($name == null)
            $name = 'dane';
        $vars = & $GLOBALS["_" . ACTION_METHOD];
        return $vars[$name];
    }

    function addError($pole, $tekst) {
        $error = parent :: get('error');
        $error[$pole] = $tekst;
        $this->set('error', $error);
    }

    function action($name = null) {
        if ($name == null)
            $name = ucfirst($this->getSearch('rodzaj')) . ucfirst($this->getSearch('suffix'));

        // Ustawienie sciezki powrotu po udanym zapisie
        if ($this->getRequest('return') != "")
            $this->set('return');

        $this->add_to_debuger();
        if ($this->ok) {
            // Wszystko ok - zapisuje
            return $name . "_Zapisz";
        } else {
            // Dodanie informacji z komunikatami o bledach
            return $name . "_Edytuj";
        }
    }

    function check($dane) {
        return true;
    }

    function check_slownik($name, $value) {
        
        $id        = parent :: getOne("SELECT inventory_dictionary_id FROM inventory_dictionary WHERE inventory_dictionary_field_name = '$name'");
        if ($id > 0) {
            $sql_ilosc = parent :: getOne("SELECT COUNT(*) FROM inventory_dictionary_value WHERE inventory_dictionary_id = $id AND inventory_dictionary_value = '$value'");
            if ($sql_ilosc > 0) return true;
        }

        $this->setFalse($name, "Wartość pola nie została wybrana ze słownika!");
        return false;
    }

    function check_id($name, $id) {
        if (!($id > 0)) {
            $this->setFalse($name, "Brak wybranego id!");
            return false;
        }
        return true;
    }

    function check_niepuste($name, $tekst) {
        if (strlen($tekst) == 0) {
            $this->setFalse($name, "Wartość nie może być pusta!");
            return false;
        }
        return true;
    }

    function check_teryt_symbol($name, $tekst) {
        if (strlen($tekst) == 0) {
            $this->setFalse($name, "Nie wybrano miejscowości z bazy TERYT!");
            return false;
        }
        return true;
    }

    function check_date($name, $data) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            $this->setFalse($name, "Niepoprawna data!");
            return false;
        }
        return true;
    }

    function check_integer($name, $liczba) {
        if (!preg_match('/^\d+$/', $liczba)) {
            $this->setFalse($name, "Niepoprawny format liczby!");
            return false;
        }
        return true;
    }

    function check_decimal($name, $kwota, $max=2) {
        if (!preg_match("/^\d+$/", $kwota) && !preg_match('/^\d+,\d{'.$max.'}$/', $kwota)) {
            $this->setFalse($name, "Niepoprawny format kwoty!");
            return false;
        }
        return true;
    }

    function _date($data=null) {
        // "RRRR-MM-DD"
    }

    function _decimal($data=null) {
        // "\d{1,6}.\d{2}"
    }

    function _int($data=null) {
        // "\d{1,}"
    }

    function _string($data=null) {
        // ".{1,}"
    }

}
?>