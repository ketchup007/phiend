<?php
require_once LIB_DIR . 'Table.class.php';

class Validator extends Table {
    var $database = array(
                'varchar'     => array('reg' => '.+', 'kom' => 'Pole nie jest poprawne!'),
                'smallint'    => array('reg' => '\d{2}-\d{3}', 'kom' => 'Pole nie jest poprawne!'),
                'decimal'     => array('reg' => '\d{2}-\d{3}', 'kom' => 'Pole nie jest poprawne!'),
                'date'        => array('reg' => '\d', 'kom' => 'Pole nie jest poprawne!'),
                'int'         => array('reg' => '', 'kom' => 'Pole nie jest poprawne!'),
		);

    var $strings = array(
                'ip'          => '(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)'
                );

    function Validator ($table_name='odbiorcy') {
        $this->table_name = $table_name;
    }
    
    function perform() {
        return "";
    }

    function check($dane) {
      #if ($dane['id_odbiorcy'] != 0) return true;
      #else return false;
      return true;
    }
    

    function komunikaty($dane) {
        return "Pole nie jest poprawne!.";
    }

    function is_valid($name, $value) {
        return false;
    }

    function _date($data) {
        // "RRRR-MM-DD"
    }
	     
    function _decimal($data) {
        // "\d{1,6}.\d{2}"
		          }
			  
    function _int($data) {
        // "\d{1,}"
    }
	       
    function _string($data) {
        // ".{1,}"
    }
				    
}
?>
