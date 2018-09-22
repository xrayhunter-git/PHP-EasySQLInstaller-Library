<?php
    class DBInstaller
    {
        private static $_instance = null;
        private $_packages = array(), 
                $_db = null,
                $_executions = array();

        private function __construct($type = array())
        {
            $this->_db = DB::create();
        }

        public static function create($data = array()) : DBInstaller
        {
            if(is_null(self::$_instance))
                self::$_instance = new DBInstaller($data);

            return self::$_instance;
        }

        public function addPackage(DB_Installer_Packager $package) : bool
        {
            if ($package == null)
            {
                return false;
            }

            array_push($this->_packages, $package);
            return true;
        }

        public function execute()
        {
            $this->_executions = array();
            foreach($this->_packages as $package)
            {
                foreach($package->build() as $instructions)
                {
                    $table = array_keys($package->build())[0];
                    
                    if (isset($instructions['primary']))
                        array_push($instructions["rows"], "PRIMARY KEY(`". $instructions["primary"] ."`)");

                    array_push($this->_executions, $this->_db->createTable($table, $instructions['rows']));
                    
                    foreach($instructions['inserts'] as $insert)
                    {
                        array_push($this->_executions, $this->_db->insert($table, $insert));
                    }
                }
            }
            return $this;
        }

        public function getExecutions()
        {
            return $this->_executions;
        }

        public function getErrors()
        {
            $temp = array();
            foreach($this->getExecutions() as $query)
            {
                array_push($temp, $query->getErrors());
            }
            return $temp;
        }
        
        public function hasErrors()
        {
            return count($this->getErrors()) > 0;
        }

        public function getExecutedSQLs()
        {
            $temp = array();
            foreach($this->getExecutions() as $query)
            {
                array_push($temp, $query->getExecutedSQL());
            }
            return $temp;
        }
    }
?>