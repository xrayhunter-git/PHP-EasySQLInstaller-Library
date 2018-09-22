<?php
    
    class Config
    {
        public static function get($str)
        {
            if($str)
            {
                $config = $GLOBALS['config'];
                $str = explode("/", $str);

                foreach($str as $bit)
                    if (isset($config[$bit]))
                        $config = $config[$bit];
                
                return $config;
            }

            return false;
        }
    }
?>