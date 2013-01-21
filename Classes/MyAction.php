<?php

class MyAction extends Table {
	
		var $_return = null;

    function __construct() {
		    $debug = $this->get('debug');
		    // Gdy pierwsza klasa w ciagu to skasuj poprzednie
	  }
	  
	  function add_to_debuger() {
		    $debug = $this->get('debug');
		    if (!is_array($debug)) $debug = array();
		
		    $action = array();
		    $action['class'] = get_class($this);
		    $action['time']  = microtime();
		    $action['sqls']  = $this->get_sqls_history_and_clear();
	/*     print_r($action); */
		    array_push($debug, $action);
	  }
       	  
		function clearFilters() {
			//print "CZYSZCZÊ FILTRY PRZESZUKIWANIA<br>\n";
			$this->clear('filters');
		}
	
	
		function clearOrdersExcept($name) {
				//print "CZYSZCZÊ FILTRY PRZESZUKIWANIA<br>\n";
	      $orders = $this->get('orders');
				$this->clear('orders');
	      if (strlen($orders[$name]) > 0) $this->addOrder($name, $orders[$name]);
		}
	
		function clearOrders() {
				//print "CZYSZCZÊ FILTRY PRZESZUKIWANIA<br>\n";
				$this->clear('orders');
		}
	
		function addOrder($name, $filter) {
				$filtry = $this->_getUserVar('orders');
				if (is_array($filtry) == false) $filtry = array ();
		
				// Ustawienie filtru
				$filtry[$name] = $filter;
		
				// Zapisanie filtru
				$this->_setUserVar('orders', $filtry, true);
		}
	
		function getOrders($section = null) {
				$filtry = $this->get('orders');
				if (!is_array($filtry)) $filtry = array ();
				//return $filtry[$section];
				return $filtry;
		}
	
		function addFilter($name, $filtr) {
				$this->_addFilter('global', $name, $filtr);
		}
	
		private function _addFilter($section, $name, $filter) {
				$filtry = $this->_getUserVar('filters');
				if (is_array($filtry) == false)
					$filtry = array ();
		
				// Pobranie filtrow sekcji
				$_filtry = $filtry[$section];
				if (is_array($_filtry) == false)
					$_filtry = array ();
		
				// Ustawienie filtru
				$_filtry[$name] = $filter;
		
				// Zapisanie filtru
				$filtry[$section] = $_filtry;
				$this->_setUserVar('filters', $filtry);
		}
	
		function isFilter($name) {
				$filtry = $this->getFilters();
				return isSet ($filtry[$name]);
		}
	
		function getFilters($section = null) {
				return array_merge($this->_getFilters('global'), $this->_getFilters($section));
		}
	
		private function _getFilters($section) {
			$filtry = $this->get('filters');
			if (!is_array($filtry[$section]))
				$filtry[$section] = array ();
			return $filtry[$section];
		}

		function setReturn($return) {
			$this->_return = $return;
		}
	
		function setSavePoint($return) {
		    // Tworzenie punktu po ktorym bedzie nastepowal powrot
			$this->cache('savepoint', $return);
		}
				
		function cache($label, $variable=null) {
			$this->_setUserVar($label, $variable, true);
			return $variable;
		}
	
		function set($label, $variable=null, $save = false) {
			$this->_setUserVar($label, $variable, $save);
			return $variable;
		}
	
		function clear($label) {
			return $this->_removeUserVar($label);
		}
	
		function findId($name, $table = array ()) {
			$value = null;
	
			// na poczatku sprawdz _GET
			if (isSet ($_GET[$name]))
				$value = $_GET[$name];
	
			$vars = & $GLOBALS["_" . ACTION_METHOD];
			if ($value == null && isSet ($vars[$name]) != null)
				$value = $vars[$name];
	
			// potem poprzednie ustawienie (zapisane w sekcji)
			if ($value == null && $this->get($name) != null)
				$value = $this->get($name);
	
			// Potem wez pierwsze z tablicy
			if ($value == null && is_array($table))
				$value = $table[0][$name];
	
			// Zapisanie w sesji
			$this->set($name, $value);
	
			return $value;
		}
	
		// Pobiera zmienne
		function get($label) {
			return $this->_getUserVar($label);
		}
	
		function getSearch($zmienna = null) {
			if ($zmienna == null)
				return $this->_getUserVar('search');
			else {
				$search = $this->_getUserVar('search');
				if (!isSet($search[$zmienna])) return null;
				else return $search[$zmienna];
			}
		}
	
		// Ustawia skladnik tablizy search
		function setSearch($zmienna, $wartosc) {
				$search = $this->get('search');
				$search[$zmienna] = $wartosc;
				return $this->set('search', $search, true);
		}
	
		// Czy jest ustawiona zmienna
		function is($label) {
			return array_key_exists($label, $GLOBALS['_phiend_actionController']);
		}

		function addData($dane=array(null, null)) {
			list ($ilosc, $wiersze) = $dane;
			$this->_setUserVar('ilosc', $ilosc);
			$this->_setUserVar('dane', $wiersze);
		}
	
		function dbg($name) {
			$dbg = "<pre>" . str_replace("\n", "<br>", print_r($this->get($name), true)) . "</pre>";
			$this->view("dbg_$name", "$name<br>$dbg");
		}
		
	  // XXX POWIAZANIE Z BAZA DANYCH
		function LoadFirstRow($table_name, $where = null, $order = null, $limit = null) {
			$this->setTable($table_name);
			$wynik = parent :: LoadRows($where, $order, $limit);
			if (is_array($wynik[0]))
				return $wynik[0];
			else
				return array ();
		}
	
		function LoadRows($table_name, $where = null, $order = null, $limit = null) {
			$this->setTable($table_name);
			return parent :: LoadRows($where, $order, $limit);
		}
	
		function LoadRow($table_name, $id) {
			$this->setTable($table_name);
			return parent :: LoadRow($id);
		}
	
		function delete($table_name, $where = null) {
			$this->setTable($table_name);
			$this->deleteRows($where);
		}
	  
		function save($table_name, $data) {
			$this->setTable($table_name);
			return $this->saveRow($data);
		}
	
		function start_action() {
  		    // Stworzenie unikatowego wpisu
  		    parent :: query('BEGIN');
  		    $blokada['hash'] = hash('md5', rand());
  		    $this->save('aplikacja_blokady', $blokada);
  		    parent :: query('COMMIT');
  		    return $blokada['hash'];
		}

		function finish_action($hash) {
		    // Sprawdzenie czy taki hash jest
		    $this->query("LOCK TABLES aplikacja_blokady WRITE");
		    $wiersz = $this->LoadRows('aplikacja_blokady', "hash = '$hash'");
/* 		    print_r($wiersz); */
		    $this->delete('aplikacja_blokady', "hash = '$hash'");
		    // Skasowanie starych zapisow - z przed godziny
		    $this->query("UNLOCK TABLES");
		    
/* 		    $this->delete('aplikacja_blokady', "created < TIMESTAMPADD(HOUR, -1, '".$wiersz[0]['created']."')"); */
		    $this->delete('aplikacja_blokady', "created < TIMESTAMPADD(HOUR, -24, NOW())");

		    if (count($wiersz) == 1) return true;
    		else return false;
		}
}
?>
