<?php
    session_start();

    $GLOBALS['config'] = array(
        'mysql' => array(
            'host' => '127.0.0.1',
            'port' => 3306,
            'username' => 'root',
            'password' => '',
            'database' => 'githubTest'
        )
    );
    
    spl_autoload_register(function($class) {
        require_once "lib/" . $class . ".php";
    });
?>