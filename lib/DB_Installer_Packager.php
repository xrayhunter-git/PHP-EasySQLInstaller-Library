<?php
    class DB_Installer_Packager
    {
        private $_product = array();

        private function __construct() { }
        
        protected function addPackageTable($table, $rows = array())
        {
            $this->_product[$table] = array(
                "rows" => $rows,
                "primary" => "",
                "inserts" => array()
            );
        }

        protected function setPackageTableRows($table, $rows = array())
        {
            $this->_product[$table]["rows"] = $rows;
        }

        protected function addPackageTableRow($table, $row = array())
        {
            $keys = array_keys($row);
            $x = 0;

            foreach($row as $val)
            {
                $this->_product[$table]["rows"][$keys[$x]] = $val;
                $x++;
            }
        }

        protected function setPackageTablePrimaryKey($table, $target)
        {
            $this->_product[$table]["primary"] = $target;
        }

        protected function addPackageTableInsert($table, $insert = array())
        {
            array_push($this->_product[$table]["inserts"], $insert);
        }

        public function build()
        {
            return $this->_product;
        }
    }
?>