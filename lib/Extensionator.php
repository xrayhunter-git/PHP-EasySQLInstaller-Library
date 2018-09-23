<?php
    class Extensionator
    {
        public static function addExtension($dir)
        {
            if(is_null($GLOBALS['extensions']))
                $GLOBALS['extensions'] = array();

            array_push($GLOBALS['extensions'], $dir);
        }
    }
?>