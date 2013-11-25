<?php

class xlsReport {
    static private $book = null;
    static private $sheet = null;
    static private $sheet_headers = array();
    static private $sheet_line = 0;
    /**
     * Init xls output
     * @static
     * @return void
     */
    static private function init() {
        if (is_null(self::$book)) {
            // Loading library
            set_include_path(get_include_path() . PATH_SEPARATOR . './_system/application/libraries/PEAR/');
            require_once 'Spreadsheet/Excel/Writer.php';
            // Creating a workbook
            self::$book = new Spreadsheet_Excel_Writer();
            self::$book->setTempDir(sys_get_temp_dir());

            // sending HTTP headers
            self::$book->send('report.xls');
            self::$book->setVersion(8);

            ob_end_clean();
        }
    }
    /**
     * Add new sheet to xls output (fill with data optionally)
     * @static
     * @param string $title
     * @param null $data
     * @return void
     */
    static public function addSheet($title = 'default', $data = null) {
        self::init();
        self::$sheet =& self::$book->addWorksheet($title);
        // Encoding
        self::$sheet->setInputEncoding('UTF-8');
        self::$sheet_headers = array();
        self::$sheet_line = 0;

        if (is_array($data)) {
            foreach ($data as $r) {
                self::addRow($r);
            }
        }
    }
    /**
     * Add row to current sheet
     * @static
     * @param $r
     * @return void
     */
    static public function addRow($r) {
        if (!self::$sheet) {
            self::addSheet();
        }

        if (!self::$sheet_headers) {
            $i = 0;
            foreach ($r as $k => $v) {
                self::$sheet_headers[$k] = $i;
                self::$sheet->write(self::$sheet_line, $i++, $k);
                self::$sheet->setColumn(self::$sheet_line, $i, 15);
            }
            ++self::$sheet_line;
        }
        foreach (self::$sheet_headers as $k => $i) {
            self::$sheet->write(self::$sheet_line, $i, !isset($r[$k]) ? '' : $r[$k]);
        }
        ++self::$sheet_line;
    }
    /**
     * Finalizes xls output and exits in case of xls started
     * @static
     * @return void
     */
    static public function finalize() {
        if (self::$book) {
            // Let's send the file
            self::$book->close();
            exit();
        }
    }
}

