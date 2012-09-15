<?php

require_once LIB_DIR . 'Table.class.php';
//include ('Archive/Tar.php');        // import class

include( '../private/defined.php');        // Pobranie stalych aplikacji

class Updater extends Table {

  // na poczatku trzeba sprawdzic, czy istnieja katalogi, i uprawnienia
  // Zalozyc i ustawic uprawnienia
  
  // Wkopiowywuje .htaccess z application/.htaccess do wybranego katalogu
  function zablokuj($katalog) {
  }

  // Wylaczenie aplikacji (REWRITE)
  function begin_update() {
    // Zalozenie pliku .service
    $this->create_dirs();
    
    $this->set_perms();
  }
  
  // Wlaczenie aplikacji (REWRITE)
  function end_update() {}
  
  function create_dirs() {
    // Katalogi z definde.php
    // Katalog var
    // 
    // Dokopiowac skrypty
  }
  function set_perms() {}
  
  function update_db() {} // Kazda 
  function revert_db() {} // Cofniecie aktualizacji do backupu ????
  function update_app() {} // Kazda zmiana w aplikacji
  function revert_app() {}
  function update() {}
  function revert() {}
  
  // Sprawdzenie plikow update_2_0_1
  // Paczka moze zawierac pliki do podmiany
  // Plik MD5 z sumami kontrolnymi
  // Update.2.0.1.class.php zawierajacy poprawki na bazie itp
  // Reczna interwencja podnosi zmiane w najwyzszym stopniu (reczna instalacja, itp)
  // Zmiany w bazie danych lub bibliotekach wymuszaja podniesienie wersji srodkowej (gdy zmiana w BD to zrob export danych przed aktualizacja)
  // Zmiany w code podnosza wersje w najnizszym stopniu
  
  function check_updates() {}

  function create_update() {
//    $obj = new Archive_Tar(PRV_EXPORTS . 'dummy.tar'); // name of archive
//    md5_file()

    $files = array();
    // Tworzenie pliku application_$this->app_version.tgz
    $obj = new Archive_Tar(PRV_UPDATES . "application_".$this->app_version.".tar"); // name of archive

    // Zapisanie wszystkich actions
    // Zapisanie wszystkich templates
    // 
    // 
	  if ($obj->create($files)) {
	    echo 'Created successfully!';
		} else {
	    echo 'Error in file creation';
		} 
  }
  
  function install_update() {}
}
?>
