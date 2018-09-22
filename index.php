<?php
    require_once 'core/init.php';

    // Load Database and create a schema.
    $db = DB::create(
        array(
            'type' => 'pdo', // Optional [Defaults: PDO]
            'sql_ip' => '127.0.0.1', // Required
            'sql_port' => 3306, // Required
            'sql_user' => 'root', // Required
            'sql_pass' => '', // Required
            'sql_db' => 'easySQLLib' // Optional
        ),
        true
    );

    // Easy Database
    // Query and Grabbing data.
    /*if ($result = $db->query("SELECT ? + ?", array(5, 5)))
    {
        if ($result->hasErrors())
        {
            foreach($result->getErrors() as $err)
                echo $err['message'] . '<br/>';
        }
        else
        {
            echo 'Mathmatical sql statement successful:<br/>';
            foreach($result->getResults() as $res)
                var_dump($res);
        }  
    }*/

    // Manually Creating a Table
    if ($result = $db->createTable("users", array(
        "id" => "INT(11) NOT NULL AUTO_INCREMENT",
        "username" => "VARCHAR(255) NOT NULL",
        "password" => "VARCHAR(255) NOT NULL",
        "" => "PRIMARY KEY(`id`)"
    )))
    {
        if ($result->hasErrors())
        {
            foreach($result->getErrors() as $err)
                echo $err['message'] . '<br/>';
        }
        else
        {
            echo 'Created the user\' table:<br/>';
        }
    }

    // Insert
    if ($result = $db->insert("users", array(
        "username" => "John Doe",
        "password" => "password123",
    )))
    {
        if ($result->hasErrors())
        {
            foreach($result->getErrors() as $err)
                echo $err['message'] . '<br/>';
        }
        else
        {
            echo 'Created the user\' with in the table:<br/>';
        }
    }
    // Get
    if ($result = $db->get("users", array('username', '=', 'John Doe')))
    {
        if ($result->hasErrors())
        {
            foreach($result->getErrors() as $err)
                echo $err['message'] . '<br/>';
        }
        else
        {
            echo 'User\' Data:<br/>';
            var_dump($result->getFirst());
        }
    }

    // Delete
    
    if ($result = $db->delete("users", array("username", "=", "John Doe")))
    {
        if ($result->hasErrors())
        {
            foreach($result->getErrors() as $err)
                echo $err['message'] . '<br/>';
        }
        else
        {
            echo 'Deleted User:<br/>';
        }
    }
    
    /* 
        Results:
        * A Scheme called 'easysqllib' should be created.
        * A table called 'users' should be created.
        * A user named 'John Doe' with the password 'password123' should be created
        Then immediately removed. Therefore you shouldn't see it in the database.
    */
    
    // Easy Database Installer
?>