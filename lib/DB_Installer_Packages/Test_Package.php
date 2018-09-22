<?php
    class Test_Package extends DB_Installer_Packager
    {
        public function __construct()
        {
            parent::addPackageTable("test", array(
                "id" => "INT(11) NOT NULL AUTO_INCREMENT",
                "username" => "VARCHAR(255) NOT NULL",
                "password" => "VARCHAR(255) NOT NULL"
            ));

            parent::setPackageTablePrimaryKey("test", "id");

            parent::addPackageTableRow("test", array(
                "email" => "TEXT NOT NULL",
                "verified" => "TINYINT(1) NOT NULL"
            ));

            parent::addPackageTableInsert("test", array(
                "username" => "bob",
                "password" => "password123",
                "email" => "typicalbob@bob.org",
                "verified" => 1
            ));
            
            parent::addPackageTableInsert("test", array(
                "username" => "jimmy",
                "password" => "password123",
                "email" => "typicaljim@jim.org",
                "verified" => 0
            ));
        }
    }
?>