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
                $_query = null,
                $_sql = "",
                $_count = 0,
                $_results = array(),
                $_errors = array(),
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
                        echo $schema->getExecutedSQL() . '<br/>';

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
            // Check, if there's a connection or a linkage.
            if ($this->_con == null)
            {
                array_push($this->_errors, array(
                    'type' => 'critical',
                    'message' => "Critical Error: Failed executing a query, while SQL cannot link to the database. [Error::FailedToLink]"
                ));
                return $this;
            }

            // Clear Errors
            $this->_errors = array();

            $this->_sql = $sql;

            $cannotFetch = array("UPDATE", "INSERT", "CREATE", "DELETE");
            switch($this->_type)
            {
                case 'mysqli':
                    if ($this->_query = $this->_con->prepare($sql))
                    {
                        $set = '';
                        foreach ($fields as $field)
                        {
                            switch(gettype($field))
                            {
                                case 'boolean':
                                    $set .= "b";
                                break;
                                case 'integer':
                                    $set .= "i";
                                break;
                                case 'double':
                                    $set .= "i";
                                break;
                                case 'string':
                                    $set .= "s";
                                break;
                                default:
                                    $set .= " ";
                                break;
                            }
                        }
                        $this->_query->bind_param($set, $fields);
                        
                        $this->_query->execute();

                        if(!in_array(strtoupper(explode(" ", $sql)[0]), $cannotFetch))
                        {
                            $this->_query->bind_result($this->_results);
                            $this->_query->fetch();
                        }

                        $this->_query->close();
                    }
                break;
                case 'pdo':
                    try
                    {
                        if ($this->_query = $this->_con->prepare($sql))
                        {
                            echo '<br/>' . $sql . '<br/>';
                            if (count($fields))
                                $i = 1;
                                foreach($fields as $field)
                                {
                                    $this->_query->bindValue($i, $field);
                                    $i++;
                                }   
                            
                            if($this->_query->execute())
                            {
                                if(!in_array(strtoupper(explode(" ", $sql)[0]), $cannotFetch))
                                    $this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ);
                                
                                $this->_count = $this->_query->rowCount();
                            }
                            else
                            {
                                array_push($this->_errors, array(
                                    'type' => 'warning',
                                    'message' => "Warning Error: Query failed executing the command. [". $e->getMessage() ."]"
                                ));
                            }
                        }
                    }
                    catch (PDOException $e)
                    {
                        array_push($this->_errors, array(
                            'type' => 'warning',
                            'message' => "Warning Error: Query failed executing the command. [Exception: ". $e->getMessage() ."]"
                        ));
                    }
                break;
                default:
                    array_push($this->_errors, array(
                        'type' => 'warning',
                        'message' => "Warning Error: Query was trying to execute a unsupported type of linkage. [Error::UnSupported]"
                    ));
                break;
            }

            return $this;
        }

        // Grabs data from the database's table when called.
        public function get($table, $where = array())
        {
            return $this->action("SELECT", "*", $table, $where);
        }

        // Deletes data from the database when called.
        public function delete($table, $where = array())
        {
            return $this->action("DELETE", "", $table, $where);
        }

        // Inserts data into the database when called.
        public function insert($table, $fields = array())
        {
            if (count($fields))
            {
                $keys = array_keys($fields);
                $values = "";
                $x = 1;

                foreach($fields as $field)
                {
                    $values .= "?";
                    if ($x < count($fields))
                    {
                        $values .= ', ';
                    }

                    $x++;
                }

                $sql = "INSERT INTO `" . $table . "` (`". implode('`, `', $keys) ."`) VALUES ({$values})";
                if($this->query($sql, $fields))
                {
                    return $this;
                }
            }
            return false;
        }

        public function createTable($table, $fields = array())
        {
            if (count($fields))
            {
                $keys = array_keys($fields);
                $values = "";
                $x = 1;

                foreach($fields as $field)
                {
                    $values .= ($keys[$x-1] != "" ? "`" . $keys[$x-1] . "` " : "") . $field;
                    if ($x < count($fields))
                    {
                        $values .= ', ';
                    }

                    $x++;
                }

                $sql = "CREATE TABLE IF NOT EXISTS `{$table}` ({$values})";

                if($this->query($sql))
                {
                    return $this;
                }
            }
            return false;
        }

        public function createSchema($schema)
        {
            return $this->query("CREATE DATABASE IF NOT EXISTS `$schema`");
        }

        // Updates existing data into the database when called.
        public function update($table, $lookup = array(), $fields = array())
        {
            if(count($lookup))
            {
                $set = "";
                $x = 1;

                foreach($fields as $name => $value)
                {
                    $set .= "{$name} = ?";
                    if ($x < count($fields))
                    {
                        $values .= ', ';
                    }

                    $x++;
                }

                $sql = "UPDATE {$table} SET {$set} WHERE " . $lookup[0] . " = " . $lookup[1];
                
                if(!$this->query($sql, $fields)->getError())
                {
                    return true;
                }
            }
            return false;
        }

        // <Helper> Preforms an action while building the foundations needed for the sql command.
        private function action($action, $target = "*", $table = "", $where = array())
        {
            if (count($where) % 4 == 0 || count($where) % 3 == 0)
            {
                $values = array();
    
                $operators = array('=', '>', '<', '>=', '<=');
                
                if (is_array($target))
                    $target = implode(',', $target);

                $sql = $action . ' ' . $target . ' FROM ' . $table . (count($where) == 0 ? ' ' : ' WHERE ');
                
                for ($i = 0; $i < count($where); $i += (count($where) % 3 == 0 ? 3 : 4))
                {
                    $field = $where[$i];
                    $operator = $where[$i+1];
                    $value = $where[$i+2];
                    $conditional = (count($where) % 3 == 0 ? " AND " : $where[$i+3]);

                    if (in_array($operator, $operators))
                    {
                        array_push($values, $value);

                        $sql .= "{$field} {$operator} ?";
                        if ($i > (count($where) - 1))
                            $sql .= " {$conditional} ";
                    }
                }

                if ($this->query($sql, $values))
                    return $this;
            }
            return false;
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
        
        public function getResults() : array
        {
            return $this->_results;
        }

        public function getFirst()
        {
            return $this->getResults()[0];
        }

        public function getErrors() : array
        {
            return $this->_errors;
        }
        
        public function hasErrors() : bool
        {
            return (count($this->getErrors()) > 0);
        }

        public function getExecutedSQL() : string
        {
            return $this->_sql;
        }

        public function getCount() : int
        {
            return $this->_count;
        }
    }
?>