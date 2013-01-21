<?php

class Script extends Table {
    private $bar;
    private $starttime;

    function __construct() {
        $mtime = microtime(); 
        $mtime = explode(" ",$mtime); 
        $mtime = $mtime[1] + $mtime[0]; 
        $this->starttime = $mtime; 
        
        $inipath = php_ini_loaded_file();
        
        echo 'Current PHP version: ' . phpversion() . " [" . $_ENV["_"] . "] \n";    
        
        
        if ($inipath) {
            echo 'Loaded php.ini: ' . $inipath . "\n";
        } else {
            echo 'A php.ini file is not loaded\n';
        }    
        
        
        // Dodanie sciezki PEAR
/*         set_include_path(PEAR_DIR . PATH_SEPARATOR . get_include_path()); */
/*         set_include_path(get_include_path() . PATH_SEPARATOR . PEAR_DIR); */
        echo 'PATH: '. get_include_path() . "\n";

    }
    
    function __destruct() {
        $mtime = microtime(); 
        $mtime = explode(" ",$mtime); 
        $mtime = $mtime[1] + $mtime[0]; 
        $endtime = $mtime; 
        $totaltime = ($endtime - $this->starttime);
        
        print("\n Skrypt potrzebowal   " . memory_get_peak_usage() / 1000000 . " MB RAM");
        print "\n Skrypt wykonywal sie " .$totaltime. " s.\n";
    }
    
    function progressBar($ilosc, $zegary = true) {
        $this->bar = new Console_ProgressBar('%bar%', '=', ' ', 76, 3);
        if ($zegary) $this->bar->reset('[%bar%] %percent% [%elapsed%/%estimate%]', '=>', '-', 76, $ilosc);
        else $this->bar->reset('[%bar%] %percent%', '=>', '-', 76, $ilosc);
//        $this->bar->reset('[%bar%] %percent% Elapsed Time: %elapsed% Pozostalo %estimate%', '=>', '-', 76, $ilosc);
//        $this->bar->reset('- %fraction% [%bar%] %percent% Elapsed Time: %elapsed%', '=>', '-', 76, $ilosc);
    }
    
    function updateBar($i) {
        $this->bar->update($i);
    }

    function getApplicationParameter($parametr) {
        return parent :: getOne("SELECT wartosc FROM aplikacja_parametry WHERE klucz = '$parametr'");
    }
    
    function setApplicationParameter($parametr, $wartosc) {
        parent :: query("DELETE FROM aplikacja_parametry WHERE klucz = '$parametr'");
        parent :: query("INSERT INTO aplikacja_parametry (klucz, wartosc) VALUES ('$parametr', '$wartosc')");
    }
    
    function getDBVersion() {
        return parent :: getOne("SELECT wartosc FROM aplikacja_parametry WHERE klucz = 'db_version'");
    }

    function setDBVersion($version) {
        parent :: query("DELETE FROM aplikacja_parametry WHERE klucz = 'db_version'");
        parent :: query("INSERT INTO aplikacja_parametry (klucz, wartosc) VALUES ('db_version', '$version')");
    }
}
?>
