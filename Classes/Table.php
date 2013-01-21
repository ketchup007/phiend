<?php

class Table extends Action {

    private $table_name;

  // Przechowuja informacje o tabelach systemu i o ich powiazaniach
  // !!!UWAGA!!! - wszyskie primary keys musza sie zaczynac id_ i musza byc unikatowe
    private $tables_info;
    private $primary_keys = array();
    private $references   = array();
    private $searchable   = array();
    
    private static $db;
    private $error;
    private $error_message;

    function __construct($table_name = "") {
        $this->table_name = $table_name;
        $this->db = null;
    }

    function get_tables_info() {
        return $this->tables_info;
    }

    function get_primary_keys() {
        return $this->primary_keys;
    }

    function get_references() {
        return $this->references;
    }

    function get_searchable() {
        return $this->searchable;
    }

    function okreslTyp($wyraz) {
        $typy = array('date'    => "\d{4}-\d{2}-\d{2}", 
                      'decimal' => "\d{1,6}.\d{2}", 
                      'integer' => '\d+', 
                      'text'  => ".+");
    
        $_typy = array ();
        foreach($typy as $klucz=>$wartosc) {
            if (preg_match('/^' . $wartosc . '$/', $wyraz)) array_push($_typy, $klucz);
        }

        return $_typy;
    }

    // Metoda zwraca wszystkie kolumny tabeli (w odpowiedniej kolejnosci)
    private function getAllFields() {
        $fields = array ();
        $db = $this->connection();
        if ($this->error == true) return array ();
        
        foreach ($this->tables_info[$this->table_name] as $field)
            array_push($fields, $field['name']);

        return $fields;
    }

    function findValuesOfType($where = array (), $function) {
        $values = array ();
        foreach ($where as $value)
            if ($this->valid($value, $function))
                array_push($values, $value);
        return $values;
    }

    /*
      GENEROWANIE SELECTOW
      1. Do przeszukiwania
        - potrzebne sa nazwy tabel
        - potrzebne sa wyrazy do ustawienia waruknow
        - potrzebna jest podana kolejnosc
        - potrzebne sa ilosci
      2. Do ladowania wierszy
        - potrzebne sa nazwy tabel
        - potrzebne sa warunki na poszczegolne kolumny w odpowiednich tabelach
        - potrzebna jest podana kolejnosc
        - potrzebne sa ilosci
    */
    public function generateSelect($tables) {
        $select = "";

        $pierwsze = true;
        foreach ($tables as $table_name) {
            // pobranie wszystkich pol z tabel
            $this->setTable($table_name);
            $_pola = $this->getAllFields();
//          print_r($_pola);
            
            // zlikwidowanie kluczy obcych
            foreach ($_pola as $pole) {
          // Pole nie moze sie powtarzac, wiec wywalenie dowiazanych
                if (!in_array($pole, $this->references[$table_name]))
                    if ($pierwsze) {
                        $select .= "SELECT " . $table_name . "." . $pole;
                        $pierwsze = false;
                    } else
                        $select .= ", " . $table_name . "." . $pole;
            }
        }

        $pierwsze = true;
        foreach ($tables as $table_name) {
            if ($pierwsze) {
                $select .= " FROM " . $table_name;
                $pierwsze = false;
            } else
                $select .= ", " . $table_name;
        }
        return $select;
    }

    /*
      Generuje wiezy pomiedzy tabelami dla warunku WHERE
    */

    private function generateConstraints($tables) {
        $con_sql = "";

        // Zaladowanie informacji o tabelach
        foreach ($tables as $table_name) {
            $this->setTable($table_name);
        }
        
        // jezeli wiecej niz jedna tabela to zacznij generowac where (polaczenia tabel)
        $pierwsze = true;
        foreach ($tables as $table_name_fk) {
            foreach ($tables as $table_name_pk) {
              // Dla wszystkich innych tabel
                if ($table_name_pk != $table_name_fk) {
                  // gdy klucz glowny jednej tabeli znajduje sie w uzyciu innej
                    if (in_array($this->primary_keys[$table_name_pk], $this->references[$table_name_fk])) {
                        if ($pierwsze) {
                            $con_sql .= "($table_name_pk.".$this->primary_keys[$table_name_pk]." = $table_name_fk.".$this->primary_keys[$table_name_pk].")";
                            $pierwsze = false;
                        } else
                            $con_sql .= " AND ($table_name_pk.".$this->primary_keys[$table_name_pk]." = $table_name_fk.".$this->primary_keys[$table_name_pk].")";
                    }
                }
            }
        }
        return $con_sql;
    }

    private function generateSearch($tables, $where = "", $where_2 = array()) {
        $pierwsze = true;
        
        // Wyszukanie wiezow
        $where_sql = $this->generateConstraints($tables);
        if (strlen($where_sql) > 0) {
            $pierwsze = false;
            $where_sql = "WHERE $where_sql";
        }

        // Pobranie informacji o polach w tabelach uczestniczacych w przeszukiwaniu
        $tables_info = array ();
        foreach ($tables as $table_name) {
            $this->setTable($table_name);
        }

        // dla kazdego wyrazu z where wygenerwanie OR i ustawienie AND
        $a_where = explode(" ", trim($where));

        // Dla kazdego wyrazu nie pustego
        foreach ($a_where as $wyraz) {
            if (strlen($wyraz) != 0) {
                $_pierwsze = true;
                $_where = "";
                // Ustalenie czym jest dany wyraz
                $typy = $this->okreslTyp($wyraz);

                // Dla kazdej tabeli uczestniczacej w zapytaniu
                foreach ($tables as $table_name) {
                    // Pobranie informacji o tabeli i informacji o palach ktore biara udzial w przeszukiwaniu
//                print_r($this->searchable[$table_name]);
                    foreach ($typy as $typ) {
                    // pobranie pol danego rodzaju, ktore biara udzial w przeszukiwaniu

                        foreach ($this->tables_info[$table_name] as $pole) {
// FIXME                        
//print $pole['name'];

                            // sprawdzenie czy jest w polach do przeszukiwania
                           if ((@in_array($pole['name'], array_values($this->searchable[$table_name]))) || !in_array($table_name, array_keys($this->searchable))) {
//                           print_r($pole);
//print_r(array_values($this->searchable[$table_name]));
//                                print $pole['name']."\n";
//                            if (in_array($pole['name'], array_flip($this->searchable[$table_name]))) {

//                                print $pole['name'];
                                $warunek = "";

                                if (in_array($pole['mdb2type'], $typy) && $pole['length'] >= strlen($wyraz)) {
                                    
                                    switch ($typ) {
                                        case "text" :
                                            $warunek = "$table_name.$pole[name] LIKE '%$wyraz%'";
                                            break;
                                        case "integer" :
                                            $warunek = "$table_name.$pole[name] = $wyraz";
                                            break;
                                        case "decimal" :
                                            $warunek = "$table_name.$pole[name] = $wyraz";
                                            break;
                                        case "date" :
                                            $warunek = "$table_name.$pole[name] = '$wyraz'";
                                            break;
                                    }
                                }
                            
                            
                            if (strlen($warunek) > 0) {
                                if ($_pierwsze) {
                                    $_where .= "($warunek)";
                                    $_pierwsze = false;
                                } else
                                    $_where .= " OR ($warunek)";
                            }
                            }
                        }
                    }
                }
                if ($pierwsze) {
                    $where_sql .= "WHERE ($_where)";
                    $pierwsze = false;
                } else
                    $where_sql .= " AND ($_where)";
            }
        }

        foreach ($where_2 as $where)
            if ($pierwsze) {
                $where_sql .= "WHERE ($where)";
                $pierwsze = false;
            } else
                $where_sql .= " AND ($where)";

        return $where_sql;
    }

    function generateOrderBy($order) {
        $sql = "";
        $pierwszy = true;
        foreach ($order as $_order) {
            if (strlen(trim($_order)) > 0) // Pomijam puste wiersze
                if ($pierwszy) {
                    $sql .= "ORDER BY $_order";
                    $pierwszy = false;
                } else
                    $sql .= ", $_order";
        }
        return $sql;
    }

    // Metoda zwraca polaczenie z baza danych
    private function connection() {

        // SPRAWDZENIE CZY JUZ JEST JAKIES POLACZENIE W SESJI
/*
        if (is_a($_SESSION['established_connection'], "MDB2_Driver_mysql")) {
            $this->db = $_SESSION['established_connection'];
            print_r($this->db);
        }
*/

        // Gdy brak polaczenia, polacz sie
        if (!is_a($this->db, "MDB2_Driver_mysqli")) {
        
            $options = array (
                'debug' => 1,
                'use_transactions' => 1
/*                 'persistent' => 1, */
/*                 'portability' => MDB2_PORTABILITY_ALL */
            );

/*             $db = & MDB2 :: factory(DSN, $options); */
            $db = & MDB2 :: singleton(DSN, $options);
            if (PEAR :: isError($db)) {
                $this->_error($db);
                $this->komunikat("Connection", "Polaczenie z baza danych. [FAILED]");
                return null;
            }
            
            $this->db = $db;
/*             print_r($db); */
            // Gdy połączenie, zapisz w sesji
/*             $_SESSION['established_connection'] = $db; */


//          $this->table_fields = $this->getAllFields();
            if (FORCE_LATIN2 == 1) $res = & $db->query('SET NAMES latin2');
            if (FORCE_UTF8 == 1)   $res = & $db->query('SET NAMES UTF8');
            $this->db->query("SET autocommit = 0");

            $this->komunikat("Connection", "Polaczenie z baza danych. [OK]");
        }
/* print_r($this->db->connection); */

        return $this->db;
    }

    private function disconnect() {
        $db = $this->db;

        if ($db != null) {
            $db->disconnect();
            $this->komunikat("Disconnect", "Rozlaczenie z baza danych.");
        }
        $this->db = null;
    }
    
    private function _error($db_er) {
        $this->error = true;
        $komunikat = "<font class='error'>" . $db_er->getMessage() . "</font></br>" . $db_er->getCode() . "<br/><font class='info'>" . $db_er->getUserInfo() . "</font>";
        $this->komunikat("Error", $komunikat);
        $this->error_message = $komunikat;
        #$this->db = null;
    }

    private function komunikat($function, $komunikat) {
/*         backtrace(); */
/*       $conn_id = $this->db->connection; */
      $conn_id = null;
      $transakcja = '';
      if ($this->db->inTransaction()) $transakcja = 'T';

      if(php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
        print(date('r')." $function: ".strip_tags($komunikat)."\n");
      } else {
        trigger_error(date('r')." <b>$function($conn_id$transakcja)</b>: <i>$komunikat</i>", E_USER_NOTICE);
      }
    }

    private function log($ident, $linia) {
        $db = $this->Connection();
        $this->komunikat("Log", $linia);
/*         $conf['db'] = $db; */
//        $logger = & Log :: singleton('sql', 'logi', $ident, $conf);
//        $logger->log($linia);
    }
    
  // !! METODY PUBLICZNE !!
    public function get_sqls_history_and_clear() {
        $db = $this->connection();
        $history = $db->getDebugOutput();
        $db->debug_output = null;
        $_history = explode("query(3): ", $history);
        $_history = array_shift($_history);
        return $history;
    }

    // Czysci cache i ustawia nowa tabele
    public function getNameOfPrimaryKey($table_name) {
            $this->setTable($table_name);
        return $this->primary_keys[$table_name];
    }
    
    public function setTable($table_name) {
        $db = $this->connection();
        $this->table_name = $table_name;

        if (!isSet($this->tables_info[$table_name])) {
            // Ustawienie
            $db->loadModule("Reverse");
            // Informacje o polach
            $this->tables_info[$table_name] = $db->tableInfo($this->table_name);
            
            // Ustawienie primary_key
            // Ustawienie referencji (wszystkie pozostale pola zaczynajace sie od id_)
            $this->references[$table_name] = array();
            foreach ($this->tables_info[$table_name] as $field) {
              if (!(strpos($field['flags'], 'primary_key') === false)) {
//              $this->primary_keys[$field['name']] =  $table_name;
                $this->primary_keys[$table_name]    =  $field['name'];
              } else {
                // Sprawdzenie czy juz jest to klucz innej tabeli
                $reference = false;
                foreach ($this->primary_keys as $key) {
                  if ($key == $field['name']) $reference = true;
                }
                // Jest to klucz - dodaj do referencji
                if ($reference)
                  array_push($this->references[$table_name], $field['name']);
              }
            }
            
            // Sprawdzenie czy pozostale tabele nie odnosza sie do tej tabeli i wstawienie do references
            foreach (array_keys($this->tables_info) as $_table_name) {
              if ($table_name != $_table_name) {
                // Sprawdzenie czy klucz glowny aktualnej tabeli jest w polach i dodanie do referencji tych tabel
                foreach($this->tables_info[$_table_name] as $pole) {
                  if ($pole['name'] == $this->primary_keys[$table_name]) array_push($this->references[$_table_name], $pole['name']);
                }
              }
            }
            
            // FIXME zaladowanie list pol do przeszukiwania (jezeli nie ma listy to szukaj po wszystkich)
            $dir = "application/code/config/tables/search";
            $handle = opendir($dir);
            $pola = @file($dir."/".$table_name);
/*             var_dump($pola); */
            if ($pola != FALSE) {
              $this->searchable[$table_name] = array();
              foreach ($pola as $pole) array_push($this->searchable[$table_name], rtrim($pole, "\n"));
              $this->komunikat("setTable", "$table_name - załadowano plik z listą pól");
            } else
              $this->komunikat("setTable", "$table_name - brak pliku z listą pól do szukania");
        }
    }

    // Rozpoczyna transakcje
    public function begin() {
        $db = $this->Connection();

//        $db->autoCommit(false);
        if ($db->supports('transactions')) {
          $db->beginTransaction();
/*           $db->query("START TRANSACTION"); */
/*           $this->query("START TRANSACTION"); */
          $this->komunikat("begin", "Rozpoczynam transakcję");
        } else {
          $this->komunikat("begin", "DB nie wspiera transakcji");
        }
/*         if ($db->inTransaction()) $this->komunikat("begin", "jestem w transakcji"); */
        
        $this->error = false;
        $this->error_message = "";
    }

    public function executeMultiple($sql, $data) {
        $db = $this->Connection();
        $this->komunikat("ExecuteMultiple", $sql);
        $sth = $db->prepare($sql);
        $res = $db->executeMultiple($sth, $data);
        if (PEAR :: isError($res)) {
            $this->_error($res);
            return array ();
        }
        return $res;
    }

    public function getOne($sql) {
        $db = $this->Connection();
        $this->komunikat("GetOne", $sql);
        $res = $db->queryOne($sql);
        //print "$sql</br>";
        if (PEAR :: isError($res)) {
            $this->_error($res);
            return array ();
        }
        return $res;
    }

    public function query_insert($sql) {
        $db = $this->Connection();
        $this->komunikat("Query_Insert", $sql);
        $rows = & $db->query($sql);
        if (PEAR :: isError($rows)) {
            $this->_error($rows);
            return array ();
        }
        return $rows;
    }

    public function query($sql) {
        $db = $this->Connection();
        $this->komunikat("Query", $sql);
        $db->setFetchMode(MDB2_FETCHMODE_ASSOC);
        $rows = & $db->queryAll($sql);
        if (PEAR :: isError($rows)) {
            $this->_error($rows);
            return array ();
        }
        return $rows;
  }
  
    function findRowsWithRef($tables, $where = "", $where2 = array (), $order = array (), $pierwszy = null, $ilosc = null) {

        $select_sql = $this->generateSelect($tables);
        $where_sql  = $this->generateSearch($tables, $where, $where2);
        $order_sql  = $this->generateOrderBy($order);

        $db = $this->connection();
        if (PEAR :: isError($db)) {
            $this->_error($db);
            return array ();
        }
        $sql       = "$select_sql $where_sql $order_sql";

        $sql_count = "SELECT COUNT(*) " . substr($select_sql, strpos($select_sql, "FROM")) . " $where_sql";

        // Wyliczenie ilosci wierszy (bez limitow) spelniajacych zapytanie
//        $db->query("set profiling=1");
        $_ilosc = & $db->queryOne($sql_count);
        if (PEAR :: isError($_ilosc)) {
            $this->_error($_ilosc);
            return array ();
        }
//        $a = $db->query("show profiles for query 1");
//        print_r($a);
//        print_r($a->fetchAll());
//        $db->query("set profiling=0");
        
//      print "$ilosc $pierwszy $sql</br>/n";

        if ($pierwszy != null || $ilosc != null) {
            $db->loadModule('Extended');
                $res = & $db->limitQuery($sql, null, $ilosc, $pierwszy);
        } else $res = & $db->query($sql);

        if (PEAR :: isError($res)) {
            $this->_error($res);
            return array ();
        }

        // Pobranie wybranych wierszy
        $rows = array ();
        $db->setFetchMode(MDB2_FETCHMODE_ASSOC);

        $rows = & $res->fetchAll();

        $this->komunikat("findRowsWithRef", $db->last_query);

        return array (
            $_ilosc,
            $rows
        );
    }

    public function findRows($where = "") {
        $db = $this->connection();
        if (PEAR :: isError($db)) {
            $this->_error($db);
            return array ();
        }
        //if ($db == null) {return array();}

        $sql = "SELECT * FROM " . $this->table_name;
        $a_where = explode(" ", $where);
        $is_where = false;

        // !! UWAGA NA TO !!
        // Sa pola i wyniki tekstowe
        if (count($this->findValuesOfType($a_where, "_string")) > 0 and count($this->findTextFields()) > 0) {
            foreach ($this->findTextFields() as $field)
                foreach ($this->findValuesOfType($a_where, "_string") as $value)
                    if (!$is_where) {
                        $sql .= " WHERE ($field LIKE '%$value%')";
                        $is_where = true;
                    } else
                        $sql .= " OR ($field LIKE '%$value%')";
        }

        // FIXME dodac obsluge dla pozostalych pol
        $this->komunikat("FindRows", $sql);

        $res = & $db->limitQuery($sql, null, 0, 50);
        if (PEAR :: isError($res)) {
            $this->_error($res);
            return array ();
        }
        // ERROR
        $rows = array ();
        $db->setFetchMode(MDB2_FETCHMODE_ASSOC);
        while ($row = & $res->fetchRow()) {
            array_push($rows, $row);
        }

        return $rows;
    }
  
    public function loadRowsWithRef($tables, $where = array(), $order=array(), $pierwszy = 0, $ilosc = 50) {

        $select_sql = $this->generateSelect($tables);
        $where_sql  = $this->generateSearch($tables, null, $where);
        $order_sql  = $this->generateOrderBy($order);
        
        $sql = "$select_sql $where_sql $order_sql";

        $db = $this->connection();
        $db->loadModule('Extended');
                
        $res = & $db->limitQuery($sql, null, $ilosc, $pierwszy);
        if (PEAR :: isError($rows)) {
            $this->_error($rows);
            return array ();
        }

        $rows = array ();
        $db->setFetchMode(MDB2_FETCHMODE_ASSOC);
        
//        print_r($res);

        $rows = & $res->fetchAll();

        $this->komunikat("loadRowsWithRef", $db->last_query);

        // Zapytanie bazy danych
        return $rows;
    }

    // Metoda zwraca okreslony wiersz (tylko 1 lub zaden)
    public function loadRows($where = "", $order = "", $limit = "") {
        $db = $this->connection();

        if (strlen($where) > 0)
            $where = " WHERE " . $where;
        if (strlen($order) > 0)
            $order = " ORDER BY " . $order;
        if (strlen($limit) > 0)
            $limit = " LIMIT " . $limit;
        $sql = "SELECT * FROM " . $this->table_name . $where . $order . $limit;
        
        $this->komunikat("LoadRows", $sql);

        $db->setFetchMode(MDB2_FETCHMODE_ASSOC);
        $rows = & $db->queryAll($sql);
        if (PEAR :: isError($rows)) {
        print_r($rows);
            $this->_error($rows);
            return array ();
        }
        
//        $db->free();

        return $rows;
    }

    // Metoda zwraca okreslony wiersz (tylko 1 lub zaden) (tylko z pojedynczym primary key)
    public function loadRow($table_id = 0) {
        $db = $this->connection();

        $this->komunikat("LoadRow", "Zaladowanie wiersza z tabeli $this->table_name o id = $table_id");
        $sth = $db->prepare('select * from ' . $this->table_name . ' where ' . $this->primary_keys[$this->table_name] . ' = ?');
        if (PEAR :: isError($sth)) {
            $this->_error($sth);
            return array ();
        }

        // Zaladowanie wiersza
        $res = & $sth->execute($table_id);
        if (PEAR :: isError($res)) {
            $this->_error($res);
            return array ();
        }

        $db->setFetchMode(MDB2_FETCHMODE_ASSOC);
        $row = $res->fetchRow();
        return $row;
    }

    public function saveRows($wiersze = array ()) {
        foreach ($wiersze as $wiersz)
            $this->saveRow($wiersz);
    }

    // Metoda zapisuje okreslony wiersz w bazie danych (insert lub update)
    public function saveRow($dane = array ()) {
        // Ustanowienie polaczenia, jezeli jeszcze nie ma
        $db = $this->connection();      
        $db->loadModule('Extended');
        $db->setOption('seqcol_name', 'id'); // Kompatybilnosc z DB
        
        // Stworzenia wiersza danych (odpowiednio poukladany)
        $wiersz = array ();
        foreach ($this->getAllFields() as $field)
            if (array_key_exists($field, $dane))
                //if (strlen($dane[$field]) > 0)
                    $wiersz[$field] = $dane[$field];

        if ($wiersz[$this->primary_keys[$this->table_name]] > 0) {
          // UPDATE
                $typ = MDB2_AUTOQUERY_UPDATE; // Czy nowy wpis - nie :)
                $where = $this->primary_keys[$this->table_name] . "=" . $wiersz[$this->primary_keys[$this->table_name]];
        } else {
            // INSERT
                // Pobranie wolnego identyfikatora
                $typ = MDB2_AUTOQUERY_INSERT; // Jest to nowy wpis
                $wiersz[$this->primary_keys[$this->table_name]] = $db->nextId($this->table_name);
        }
        
        // Przygotowanie zapytania i wykonanie
        $sth = $db->autoExecute($this->table_name, $wiersz, $typ, $where);
        if (PEAR :: isError($sth)) {
            $this->_error($sth);
            return 0;
        }

        $this->komunikat("SaveRow", $db->last_query);

        return $wiersz[$this->primary_keys[$this->table_name]];

    }
    
    // Metoda kasuje wiele wierszy
    public function deleteRows($where) {
        $db = $this->connection();
        $sql = "DELETE FROM " . $this->table_name . " WHERE " . $where;
        $this->komunikat("DeleteRows", $sql);
        $res = & $db->query($sql);
        if (PEAR :: isError($res)) {
            $this->_error($res);
            return array ();
        }
    }

    // Metoda kasuje tylko jeden wybrany wiersz
    public function deleteRow($row_id) {
        // Ustanowienie polaczenia, jezeli jeszcze nie ma
        $db = $this->connection();
        if (strlen($pk) > 0) {
            $sql = "DELETE FROM " . $this->table_name . " WHERE " . $this->primary_keys[$this->table_name] . "=" . $row_id;
            $this->komunikat("DeleteRow", $sql);
            $res = & $db->query($sql);
            if (PEAR :: isError($res)) {
                $this->_error($res);
                return array ();
            }
        }
    }

    // Konczy transakcje
    public function end($ok = true) {

/*         $db = $this->db; */
        $db = $this->Connection();
        
        if ($db->inTransaction()) {
            if ($this->error == true or $ok == false) {
                $test = $db->rollback();
/*                 print_r($db); */
/*                    $this->query("ROLLBACK"); */
/*                 print_r($test); */
                $this->komunikat("end", "Kończę transakcję [ROLLBACK]");
                return false;
            } else {
                $db->commit();
                $this->komunikat("end", "Kończę transakcję [COMMIT]");
                return true;
            }
        }

        $this->komunikat("end", "Brak transakcji !!!");
        
        $this->disconnect();
        
        return true;
      }
    
}
?>
