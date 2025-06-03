<?php

namespace App\Core;

class Autoloader //Onnodig en maakt je code slechter leesbaar, het is echt heel slechte code om uberhaupt te schrijven.
// Je kan gewoon de classes die je nodig hebt direct includen. Een autoloader eigenlijk alleen nodig als je een heel complex plugin systeem hebt. En dat hebben jullie niet. En je moet geen code schrijven voor iets wat je niet nodig hebt.
//Autoloaders saboteren alle veiligheidstools van IDEs etc. voor weinig tot geen voordeel.
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
