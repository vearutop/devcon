<?php

class TableHtml {
    const HEAD = '<table class="sortable"><tbody>';
    const TR_1 = '<tr>';
    const TR_2 = '</tr>';
    const TH_1 = '<th>';
    const TH_2 = '</th>';
    const TH_3 = '';
    const TD_1 = '<td>' . (!empty($this->queryOptions['pre']) ? '<pre>' : '');
    const TD_2 = (!empty($this->queryOptions['pre']) ? '</pre>' : '') . '</td>';
    const TD_3 = '';
    const TAIL = '</tbody></table>';


    public $optionPre;
    public $optionRotate;
    public $optionEscape;


    protected $init = 0;
    protected function init($row) {
        echo static::HEAD;

        echo static::TR_1;

        foreach ($row as $k => $d) {
            echo static::TH_1 . $k . static::TH_2;
        }

        echo static::TH_3, static::TR_2;


        $this->init = 1;
    }

    public function add($row) {
        if (!$this->init) {
            $this->init($row);
        }


        if (!$this->optionRotate) {
            echo static::TR_1;
            foreach ($row as $d) {
                if (is_null($d)) {
                    $d = 'NULL';
                }
                elseif ($this->optionEscape) {
                    $d = str_replace('<', '&lt;', $d);
                }
                echo static::TD_1 . $d . static::TD_2;
            }
            echo static::TD_3, static::TR_2;
        }


        // rotated table
        else {
            $desc = $this->_fetchAssoc();
            if (!$desc) {
                return $this;
            }

            $rows = array();
            $i = 0;
            foreach ($desc as $k => $d) {
                if (is_null($d)) {
                    $d = 'NULL';
                }
                elseif ($this->optionEscape) {
                    $d = str_replace('<', '&lt;', $d);
                }
                $rows[++$i] = $th_1 . $k . $th_2 . $td_1 . $d . $td_2;
            }

            while ($desc = $this->_fetchAssoc()) {
                $i = 0;
                foreach ($desc as $d) {
                    if (is_null($d)) {
                        $d = 'NULL';
                    }
                    elseif ($html_escape) {
                        $d = str_replace('<', '&lt;', $d);
                    }

                    $rows[++$i] .= $td_1 . $d . $td_2;
                }
            }

            foreach ($rows as $line) {
                echo $tr_1 . $line . $tr_2;
            }
        }



        echo static::TAIL;
        return $this;

    }


}