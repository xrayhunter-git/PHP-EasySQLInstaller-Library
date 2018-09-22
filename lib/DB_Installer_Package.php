<?php
    namespace sql\installer;

    class DB_Installer_Package
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
            array_push($this->_product[$table]["rows"], $row);
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
            foreach ($this->_product as $table)
            {
                if ($table["primary"] != "")
                {
                    array_push($table["rows"], "PRIMARY KEY(`". $table["primary"] ."`)");
                    $table["primary"] = null;
                }
            }

            return $this->_product;
        }
    }
?>