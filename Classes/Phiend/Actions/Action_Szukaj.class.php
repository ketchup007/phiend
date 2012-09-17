<?php
/*
require_once LIB_DIR . 'MySmarty.class.php';
require_once LIB_DIR . 'MyExcel.class.php';
require_once LIB_DIR . 'MyPHPExcel.class.php';
require_once LIB_DIR . 'MyAction.class.php';
*/
namespace Phiend\Extends;

class Action_Szukaj extends MyAction {

    var $smarty;

    function init() {
        // Ustalenie metody prezentacji danych - ustalenie silnika wyswietlania
        $engine = 'Smarty';
        if (isSet($_GET['engine'])) $engine = $_GET['engine'];
        
/*
        print($engine);
        print_r(parent :: get('dane'));
*/
        
        // Uruchomienie silnika Smarty
/*         $this->smarty = new MySmarty; */
/*         $this->smarty = new "My$engine"; */
        
        $reflection_class = new ReflectionClass("My$engine");
        $this->smarty = $reflection_class->newInstanceArgs();
/*         print_r($this->smarty); */
        
        parent :: generateMenu();
    }

    function assign($name, $variable) {
        if (method_exists($this->smarty, 'assign'))
            $this->smarty->assign($name, $variable);
    }
    
    function setAction($action) {
        if (method_exists($this->smarty, 'setAction'))
            $this->smarty->setAction($action);
    }


    function setExportToExcel($can_be_exported=false) {
        if (method_exists($this->smarty, 'setExportToExcel'))
            $this->smarty->setExportToExcel($can_be_exported);
    }

    function setSearch($is_search) {
        if (method_exists($this->smarty, 'setSearch'))
            $this->smarty->setSearch($is_search);
    }

    // Przycisk na pasku przeszukiwania
    function addButton($name, $icon, $access, $label, $action, $preaction=null, $new_window=false) {
        if (method_exists($this->smarty, 'addButton'))
            $this->smarty->addButton($name, $icon, $access, $label, $action, $preaction, $new_window);
    }
    
    // Tabela
    function addColumn($nazwa_kolumny, $kolumna, $szerokosc = null, $sortable=true) {
      // pobierz pola
      $pola = parent :: get('__kolumny');
      if (!is_array($pola)) $pola = array();
      $pole = array();
      $pole['nazwa_kolumny'] = $nazwa_kolumny;
      $pole['kolumna']       = $kolumna;
      $pole['szerokosc']     = $szerokosc;
      $pole['sortable']      = $sortable;
      
      $pola[$kolumna]  = $pole;
      
      return parent :: set('__kolumny', $pola);
    }
    
    // Przyciski przy kazdym wierszu - akcje
    function addDataAction($order, $href, $ikona, $title, $enable=true, $options = null) {
      // options: warunki, 

      // pobierz pola
      $akcje = parent :: get('__akcje');

      // pobranie akcji z puli
      $_akcje = $akcje[$order];
      if (!is_array($_akcje)) $_akcje = array();

      $akcja = array();
      
      $akcja['type']       = 'normal';
      $akcja['href']       = $href;
      $akcja['ikona']      = $ikona;
      $akcja['title']      = $title;
      $akcja['enable']     = $enable;
      $akcja['options']    = $options;

      $after = strstr($href, '?');
      $action = substr ($href, 0, -1 * strlen($after));
      
      if (strpos($GLOBALS['_phiend_actionController']->getUserVar('roles'), $action) !== false)
        array_push($_akcje, $akcja);

      $akcje[$order]  = $_akcje;
      
      return parent :: set('__akcje', $akcje);
    }

    function nwDataAction($order, $href, $ikona, $title, $enable=true, $options = null) {
      // pobierz pola
      $akcje = parent :: get('__akcje');

      // pobranie akcji z puli
      $_akcje = $akcje[$order];
      if (!is_array($_akcje)) $_akcje = array();
      
      $akcja = array();
      
      $akcja['type']       = 'new_window';
      $akcja['href']       = $href;
      $akcja['ikona']      = $ikona;
      $akcja['title']      = $title;
      $akcja['enable']     = $enable;
      $akcja['options']    = $options;
      
      $after = strstr($href, '?');
      $action = substr ($href, 0, -1 * strlen($after));

      if (strpos($GLOBALS['_phiend_actionController']->getUserVar('roles'), $action) !== false)
        array_push($_akcje, $akcja);
      
      $akcje[$order]  = $_akcje;
      
      return parent :: set('__akcje', $akcje);
    }
    
    function delDataAction($order, $href, $ikona, $title, $note, $options = null) {
      // pobierz pola
      $akcje = parent :: get('__akcje');

      // pobranie akcji z puli
      $_akcje = $akcje[$order];
      if (!is_array($_akcje)) $_akcje = array();

      $akcja = array();
      
      $akcja['type']       = 'delete';
      $akcja['href']       = $href;
      $akcja['ikona']      = $ikona;
      $akcja['title']      = $title;
      $akcja['note']       = $note;
      $akcja['options']    = $options;

      $after = strstr($href, '?');
      $action = substr ($href, 0, -1 * strlen($after));
      
      if (strpos($GLOBALS['_phiend_actionController']->getUserVar('roles'), $action) !== false)
        array_push($_akcje, $akcja);
      
      $akcje[$order]  = $_akcje;
      
      return parent :: set('__akcje', $akcje);
    }

    function view($label, $variable = null) {
        if ($variable == null)
            $this->assign($label, $this->_getUserVar($label));
        else
            $this->assign($label, $variable);
    }

    function getSearch($zmienna = null) {
        if ($zmienna == null)
            return $this->_getUserVar('search');
        else {
            $search = $this->_getUserVar('search');
            return $search[$zmienna];
        }
    }

    function action($name=null) {
        if ($name == null) $name = ucfirst($this->getSearch('rodzaj')).ucfirst($this->getSearch('suffix'));

        $search = $this->_getUserVar('search');
        
        if ($search['pierwszy'] == null) $search['pierwszy'] = 0;
        $this->assign('search', $search);

        $this->assign('ACTION_METHOD', ACTION_METHOD);
        $this->assign('ilosc', $this->_getUserVar('ilosc'));
        $this->assign('pl', PL);
                
        // ??
        $this->assign('menu', $this->_getUserVar('menu'));
        
        // tablica zawierajaca kolumny tabeli
        $this->assign('__kolumny', $this->_getUserVar('__kolumny'));
        
        $this->add_to_debuger();
        // Wyswietlenie wynikow
        $kolumny = parent :: get('__kolumny');

        // konwersja danych na nowy format (powiazanie wiersza z akcjami)
        $new_dane = array();
        $dane = parent :: get('dane');
        foreach ($dane as $wiersz) {
            // zmienne uzyte w EVAL: wiersz, pracownik
            $_wiersz['dane'] = $wiersz;
            // Sprawdzenie waznosci akcji dla danego wiersza
            $akcje = $this->_getUserVar('__akcje');
            $_akcje = array();
            if (is_array($akcje)) {
                $keys = array_keys($akcje);
                foreach (array_keys($akcje) as $key) {
                    // pobranie kolejnej akcji
                    $akcja = $akcje[$key];
                    foreach ($akcja as $_akcja) {
                        // to juz sa opcje wybranej akcji (tylko 1 moze przejsc)
                        
                        // Sprawdzenie czy wszystkie wymagane warunki sa spelnione
                        if (strlen($_akcja['options']) > 0) $option = "if (".$_akcja['options'].") \$ok = TRUE; else \$ok = FALSE;";
                        else $option = "";
                        $_ok = eval($option);
                        if (@$ok == TRUE) $_akcje[$key] = $_akcja;
                        if (@$ok || strlen($_akcja['options']) == 0) {$_akcje[$key] = $_akcja; break;}
                      }       
                }
            }
            $_wiersz['akcje'] = $_akcje;
            array_push($new_dane, $_wiersz);
            $this->assign('new_dane', $new_dane);
        }
        if (method_exists($this->smarty, 'finish'))
            $this->smarty->finish("Lista.tpl");
    }
}
?>