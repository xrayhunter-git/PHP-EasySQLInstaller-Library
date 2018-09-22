<?php
    class DBInstaller
    {
        private static $_instance = null;
        private $_packages = array(), 
                $_db = null;

        private function __construct($type = array())
        {
            $this->_db = DB::create();
        }

        public static function create($data = array())
        {
            if(is_null(self::$_instance))
                self::$_instance = new DBInstaller($data);

            return self::$_instance;
        }

        public function addPackage(DB_Installer_Packager $package)
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
            foreach($this->_packages as $package)
            {
                foreach($package->build() as $instructions)
                {
                    // Read the tables and build them.
                    var_dump($instructions);
                    // Read the inserts and insert them.
                }
            }
        }
    }
?>