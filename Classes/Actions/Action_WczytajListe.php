<?php
/* require_once LIB_DIR . 'MyAction.class.php'; */
/* namespace Phiend\Actions; */

class Action_WczytajListe extends MyAction {

    private $name = null;

		function init($name=null) {
/* 				print_r($_SESSION['search']); */
		    parent :: setSavePoint(get_class($this));
		    $this->name = $name;

				// Pobranie zmiennych z _POST
				$vars = & $GLOBALS["_" . ACTION_METHOD];
		
        // Czyszczenie cache z poprzednich operacji
				parent :: clear('dane');

				parent :: clear('__kolumny');
				parent :: clear('__akcje');
		
				if (strlen($_GET['rodzaj']) > 0 && $this->name == null) $this->name = $_GET['rodzaj']; 
		
        if ($this->name == null) $this->name = ucfirst(parent :: getSearch('rodzaj')).ucfirst(parent :: getSearch('suffix'));
        
        parent :: clearOrdersExcept($this->name);
				parent :: set('return', ucfirst($this->name) . '_WczytajListe', true);

				// Ustalenie czy zmienila sie zakladka
				$action = explode("_", $GLOBALS['_phiend_actionController']->_actionChain[count($GLOBALS['_phiend_actionController']->_actionChain) - 1]);

/* 				print_r($GLOBALS); */
/*
				print_r(parent :: getSearch('rodzaj'));
				print_r(strtolower($action[0]));
				print_r(strcmp(strtolower($action[0]), parent :: getSearch('rodzaj')));
*/
/* 				print_r($_SESSION['search']); */
				// Sprawdzenie czy bylo to samo
				if (strcmp(strtolower($action[0]), parent :: getSearch('rodzaj')) != 0) {
						parent :: clearFilters();
            parent :: clearOrders();

						parent :: setSearch('rodzaj', strtolower($action[0]));
						parent :: setSearch('ord_by', "");
						parent :: setSearch('kierunek', "");
						parent :: setSearch('typ', "");
						parent :: setSearch('pierwszy', 0);
				}
/* 				print_r($_SESSION['search']); */

				// ZMIANA ZAKLADKI
				if (isSet ($_GET['typ'])) {
						if (strcmp($_GET['typ'], parent :: getSearch('typ')) != 0) {
								parent :: setSearch('typ', $_GET['typ']);
								parent :: setSearch('ord_by',  "");
								parent :: setSearch('kierunek', "");
						}
				}

				if (isSet ($_GET['pierwszy'])) {
					parent :: setSearch('pierwszy', $_GET['pierwszy']);
				}

				// ZMIANA SZUKANIA
				if (isSet ($vars['where'])) {
						parent :: setSearch('where', $vars['where']);
						parent :: setSearch('pierwszy', 0);
				}

				if (isset ($_GET['ord_by'])) {
					parent :: addOrder($this->name, "$_GET[ord_by] $_GET[kierunek]");
				}
/* 				print_r($_SESSION['search']); */

				// USTAWIENIE PARAMETROW POCZATKOWYCH
				if (parent :: getSearch('pierwszy') == null) parent :: setSearch('pierwszy', 0);
/* 				print_r($_SESSION['search']); */
				
				// ZALADOWANIE FILTROW
				$this->setAllAvailableFilters();		
/* 				print_r($_SESSION['search']); */
		}

		function setAllAvailableFilters() {
				$vars = & $GLOBALS["_" . ACTION_METHOD];
				// Sprawdzenie, czy filtr jest ustawiany
/* 				print_r($vars); */
				foreach ($vars as $key=>$var) {
				  if (preg_match("/filtr_/i", $key)) {
/* 				      print("\n<br/>ustawiam filtr: ".$key. "=>$var\n<br/>"); */
    				  parent :: setSearch($key, $var);
				  }
				}
/* 				print_r(parent :: getSearch()); */
		}

		function isFilter($nazwa) {
				$vars = & $GLOBALS["_" . ACTION_METHOD];
/* 				print_r($vars); */
/* 				print_r($GLOBALS); */
				// Sprawdzenie, czy filtr jest ustawiany
/* 				if (isSet($vars[$nazwa]) && strlen($vars[$nazwa]) > 0) parent :: setSearch($nazwa, $vars[$nazwa]); */
        // Zlikwidowalem sprawdzanie ustawienia filtra
        if (isSet($vars[$nazwa])) parent :: setSearch($nazwa, $vars[$nazwa]);
        
				return parent :: getSearch($nazwa);
		}
		
    // Ustawia domyslne wartosci filtra
	  function setFilterDefaults($filter, $default_value) {
        $filters = parent :: getSearch();
/*         print_r($filters); */
		    if (!isSet($filters[$filter]) || strlen($filters[$filter]) == 0) parent :: setSearch($filter, $default_value);
	  }
  
    // Ustawia domyslne wartosci sortowania
	  function setDefaultOrder($tabela, $pole, $asc) {
	      	$filtry = parent :: getOrders();
	      	// Gdy nie ma ustawionego sortowania
	      	if (!isset($filtry[$this->name])) {
	      	    $order = "$tabela.$pole";
	      	    if ($asc) $order .= " ASC";
	      	    else $order .= " DESC";
	      	    parent :: addOrder($this->name, $order);
	      	}
	  }
	  
	  function determineFirst() {
	     // Gdy pelen zakres danych
	     if ($this->determineRange() == null) return null;
	  
	     // Sprawdzenie czy zmienily sie filtry
	     $nowe  = parent :: get('search');
	     $stare = parent :: get('old_search');
	     
	     if (!is_array($nowe))  $nowe = array();
	     if (!is_array($stare)) $stare = array();

	     // ord_by, kierunek, pierwszy, 
	     unset($stare['ord_by'], $stare['kierunek'], $stare['pierwszy']);
	     unset($nowe['ord_by'], $nowe['kierunek'], $nowe['pierwszy']);
	     
       if (count(array_diff($nowe, $stare)) > 0 || count(array_diff($stare, $nowe)) > 0) parent :: setSearch('pierwszy', 0);
       
	     return parent :: getSearch('pierwszy');
	  }

	  function determineRange() {
	  
        // Sprawdzenie , czy w adresie nie ma zmiannej range=ALL
        $range = PL;
        if (isSet($_GET['range'])) $range = $_GET['range'];
        
        if ($range == 'ALL') return null;
        
        if ($range > 1000) $range = 1000;
                
        return $range;
	  }
  
		function action($name=null) {

		    // Zapisanie stanu filtrow
		    parent :: cache('old_search', parent :: getSearch());
/* 		    print_r(parent :: getSearch()); */

		    if ($name != null) $this->name = $name;

				if ($this->name == null) $this->name = ucfirst($this->getSearch('rodzaj')).ucfirst($this->getSearch('suffix'));
		    $this->add_to_debuger();
				return ucfirst($this->name) . "_Szukaj";
		}
}
?>
