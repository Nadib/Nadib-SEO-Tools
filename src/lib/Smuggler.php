<?php
/**
 * SEOSmuggler Analyser (multiton).
 *
 * @package SEOSmuggler
 * @author Nadib Bandi
 */

namespace SEOSmuggler;

/**
 * SEOSmuggler Analyser (multiton).
 *
 * @package SEOSmuggler
 */
class Smuggler {
	
    /** @var [\SEOSmuggler\Smuggler] $instances Smuggler multiton instances.*/ 
    private static $instances;
    
    /** @var \Ressource\Factory $factory Factory */
    private $factory;
    
        
    /**
     * Static access to Smuggler singleton instance
     * 
     * @param string $baseURL Website URL
     * 
     * @return \SEOSmuggler\Smuggler Smuggler instance
     */
    public static function get($baseURL)
    {
        if (empty(self::$instances[$baseURL])) {
            self::$instances[$baseURL] = new Smuggler($baseURL);
        }
        return self::$instances[$baseURL];
    }
    
    /**
     * Start analyse
     * 
     * @return [\SEOSmuggler\Ressource]
     */
    public function analyse()
    {   
        $this->factory->getRessource();
        return $this->factory->getRessources();
    }
	
    /**
     * Constructor
     * 
     * @param string $baseURL Website URL
     */
    protected function __construct($rootURL)
    {
        $this->factory = new Factory($rootURL);
    }
    
    private function __clone(){}	
    private function __wakeup(){}	
}