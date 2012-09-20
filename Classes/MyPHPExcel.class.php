<?php

/* require_once PHPEXCEL_DIR . 'PHPExcel.php'; */

class MyPHPExcel extends PHPExcel {

    function __construct()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(3600);
        require CONFIG_DIR . "config.php";

        parent::__construct();
        $this->getActiveSheet()->setTitle("Eksport danych z systemu CSOZ");
        
        $this->setActiveSheetIndex(0);

    }

    function finish($plik) {
        $objWriter = PHPExcel_IOFactory::createWriter($this, "Excel5");

        // KOLUMNY
        $kolumny = $GLOBALS['_phiend_actionController']->getUserVar('__kolumny');
        $pliki = array();

        // DANE
        $dane = $GLOBALS['_phiend_actionController']->getUserVar('dane');

        // CENTROWANIE
       	$this->getActiveSheet()->getStyle("A:ZZ")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // USTAWIENIE NAGŁÓWKÓW
        $i = 1;
        foreach(array_keys($kolumny) as $key) {
          	$this->getActiveSheet()->getColumnDimensionByColumn($i - 1)->setAutoSize(true);

            $this->getActiveSheet()->setCellValueByColumnAndRow($i - 1, 1, $kolumny[$key]['nazwa_kolumny']);
            $i++;
        }
        
        // ZAPIS DANYCH
        foreach ($dane as $key=>$wiersz) {
          foreach (array_keys($kolumny) as $id=>$kolumna) {
            list($table, $field) = explode('.', $kolumna);
            // Sprawdzenie czy juz zostal zaladowany odpowiedni plik
            if (!in_array($table, $pliki)) {
                array_push($pliki, $table);
                if (@file_exists(TEMPLATE_DIR . "excel/$table.php")) {
                    require_once(TEMPLATE_DIR . "excel/$table.php");
                } 
            }

            if (isset($$field) && in_array($table, $pliki)) {
                eval($$field);
                $pole = "e_$field";
                $napis = $$pole;
            } else $napis = "Brak definicji pola '$field'";
              $this->getActiveSheet()->setCellValueByColumnAndRow($id, $key + 2, $napis);
          }
        }

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"eksport_danych.xls\"");
        header("Cache-Control: max-age=0");
        $objWriter->save("php://output");
    }
}