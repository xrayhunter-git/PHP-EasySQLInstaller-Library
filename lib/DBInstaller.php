<?php
    namespace sql\installer;

    class DBInstaller
    {
        private static $_instance = null;
        private $_packages = array(), 
                $_db = null;

        private function __construct($type = 'pdo')
        {
            $this->_db = DB::create($type);
        }

        public static function getInstance($type = 'pdo')
        {
            if(is_null(self::$_instance))
                self::$_instance = new DB_Installer_Package($type);

            return self::$_instance;
        }

        public function addPackage(DB_Installer_Package $package)
        {
            array_push($_packages, $package);
        }

        public function execute()
        {
            foreach($this->_packages as $package)
            {
                foreach($package->build() as $instructions)
                {
                    // Read the tables and build them.
                    // Read the inserts and insert them.
                }
            }
        }
    }
?>