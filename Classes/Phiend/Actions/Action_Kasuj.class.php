<?php
/* require_once LIB_DIR . 'MyAction.class.php'; */
namespace Phiend\Extends;

class Action_Kasuj extends MyAction {
		var $id_name = "";
		var $id = 0;

		function init($name = null) {
				if ($name == null)
						$name = ucfirst($this->getSearch('rodzaj'));
		
				$this->id_name = parent :: getNameOfPrimaryKey(strtolower($name));
				$this->id      = parent :: findId($this->id_name);

				// Zaladowanie wiersza z tabeli
				parent :: set('dane', parent :: LoadRow(strtolower($name), $this->id));
				
				// Zapisanie
				parent :: set($this->id_name, $this->id, true);
		
				parent :: begin();
		
				// Zwrocenie glownego wiersza
				return parent :: get('dane');
		}

		function canDelete() {
				//if ($_GET['is_js_confirmed'] == 1) return true;
				//else return false;
				return true;
		}

		function delete($table, $where = null) {
				if ($this->canDelete())
						parent :: delete($table, $where);
		}

		function action($name = null) {
				if ($name == null)
						$name = ucfirst($this->getSearch('rodzaj')) . ucfirst($this->getSearch('suffix'));
		
				// Skasowanie glownego wiersza
				$this->delete(strtolower($name), $this->id_name . " = " . $this->id);
		
				parent :: end();
		
		        $this->add_to_debuger();
				if ($this->_return != null)
						return $this->_return;
				return $name . "_WczytajListe";
		}
}
?>