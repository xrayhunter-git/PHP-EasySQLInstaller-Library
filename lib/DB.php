<?php
    /***
     * Class: DB
     * The Database class helps the developer to easily switch in between libraries without changing the core mechanics of the website.
     * As well, helps to preform a centeralized development-zone.
     */
    class DB
    {
        // Singleton
        private static $_instance = null;

        private $_type = 'pdo',
                $_con = null,
                $_conData = array();

        // Constructor
        private function __construct(array $data = array(), bool $autoBuild = false)
        {
            $needs = array('sql_ip', 'sql_port', 'sql_user', 'sql_pass');
            if (!self::allInArray(array_keys($data), $needs))
            {
                // Template of the required Array.
                /*
                $data = array(
                    'type' => 'pdo', // Optional [Defaults: PDO]
                    'sql_ip' => '127.0.0.1', // Required
                    'sql_port' => 3306, // Required
                    'sql_user' => 'root', // Required
                    'sql_pass' => '', // Required
                    'sql_db' => 'test' // Optional
                );
                */
                // System auto replacer.
                $data = array(
                    'type' => 'pdo',
                    'sql_ip' => (!isset($data['sql_ip']) ? '127.0.0.1' : $data['sql_ip']),
                    'sql_port' => (!isset($data['sql_port']) ? 3306 : $data['sql_port']),
                    'sql_user' => (!isset($data['sql_user']) ? 'root' : $data['sql_user']),
                    'sql_pass' => ''
                );
            }
            $this->_type = strtolower((isset($data['type']) ? $data['type'] : "pdo"));
            $this->_conData = $data;

            // Sets up the individual libraries needs.
            switch($this->_type)
            {
                // MySQLi Library.
                case 'mysqli':
                    $this->_con = new mysqli($data['sql_ip'], $data['sql_user'], $data['sql_pass']);

                    if ($this->_con == null || $this->_con->connect_errno)
                    {
                        array_push($this->_errors, array(
                            'type' => 'critical',
                            'message' => 'Critical Error: SQL [MySQLi Exception] occured :: ' . $this->_con->connect_error
                        ));
                    }
                break;
                // PDO Library.
                case 'pdo':
                    try
                    {
                        $this->_con = new PDO("mysql:host=". $data['sql_ip'] .";port=". (!isset($data['sql_port']) ? 3306 : $data['sql_port']), $data['sql_user'], $data['sql_pass']);
                        $this->_con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    }
                    catch(PDOException $e)
                    {
                        array_push($this->_errors, array(
                            'type' => 'critical',
                            'message' => (
                            'Critical Error: SQL [PDO Exception] occured at creation :: ' . 
                            $e->getMessage()
                            )
                        ));
                    }
                break;
                // Unsupported.
                default:
                    array_push($this->_errors, array(
                        'type' => 'critical',
                        'message' => "Critical Error: SQL Access doesn't know how to link to the database. [Error::UnSupported]"
                    ));
                break;
            }

            if(isset($data['sql_db']))
            {
                if ($autoBuild)
                    if($schema = $this->createSchema($data['sql_db']))
                        //echo $schema->getExecutedSQL() . '<br/>';

                $this->query("USE " . $data['sql_db']);
            }
        }

        // Singleton public function
        public static function create(array $data = array(), bool $autoBuild = false)
        {
            if(is_null(self::$_instance))
                self::$_instance = new DB($data, $autoBuild);

            return self::$_instance;
        }

        // Executes secure query's via prepares.
        public function query($sql, $fields = array())
        {
            $query = new DB_Query($this);
            return $query->query($sql, $fields);
        }

        // Grabs data from the database's table when called.
        public function get($table, $where = array())
        {
            $query = new DB_Query($this);
            return $query->get($table, $where);
        }

        // Deletes data from the database when called.
        public function delete($table, $where = array())
        {
            $query = new DB_Query($this);
            return $query->delete($table, $where);
        }

        // Inserts data into the database when called.
        public function insert($table, $fields = array())
        {
            $query = new DB_Query($this);
            return $query->insert($table, $fields);
        }
        
        // Creates a table into the database when called.
        public function createTable($table, $fields = array())
        {
            $query = new DB_Query($this);
            return $query->createTable($table, $fields);
        }
        
        public function createSchema($schema)
        {
            $query = new DB_Query($this);
            return $query->createSchema($schema);
        }
        
        // Updates existing data into the database when called.
        public function update($table, $lookup = array(), $fields = array())
        {
            $query = new DB_Query($this);
            return $query->update($table, $lookup, $fields);
        }

        private function allInArray($needle, $haystack) : bool
        {
            $count = 0;
            foreach ($needle as $need)
            {
                if (!in_array($need, $haystack))
                    continue;
                else
                    $count++;
            }
            return (count($haystack) == $count);
        }

        public function getConnection()
        {
            return $this->_con;
        }
    }
?>