<?php
define('PREFIX' , '../../'); // Ustala sciezke poczatkowa 

include( '../config/defined.php'); // Pobranie stalych aplikacji
//include( '../defined.php');                            // Pobranie konfiguracji

class updateDB extends Script {

    private $backup = null;

	function __construct() {
    	parent::__construct();
	    // Sprawdzenie czy juz jest taka baza
//	    $this->backup = "_".date('l jS \of F Y h:i:s A');
	}

	function __destruct() {
    	parent::__destruct();
	}
	
	function migrate($from, $to) {
      if (parent :: getDBVersion() != $from) throw new Exception("By wykonac ta operacje DB musi byc w wersji $from");

      parent :: setDBVersion($to);
      
      return true;
	}

}

?>
