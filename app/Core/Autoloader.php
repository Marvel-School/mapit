<?php

namespace App\Core;

class Autoloader
{
    /**
     * Class loader
     * 
     * @param string $className
     * @return void
     */
    public static function load($className)
    {
        // Replace namespace separator with directory separator
        $className = str_replace('\\', '/', $className);
        
        // Define the base directory
        $baseDir = dirname(dirname(__DIR__));
        
        // Replace App namespace with app directory
        $className = str_replace('App/', 'app/', $className);
        
        // Create the file path
        $file = $baseDir . '/' . $className . '.php';
        
        // Check if the file exists and include it
        if (file_exists($file)) {
            require $file;
        }
    }
}
