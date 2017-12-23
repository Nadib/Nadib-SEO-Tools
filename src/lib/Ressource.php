<?php
/**
 * SEOSmuggler ressource
 *
 * @package SEOSmuggler
 * @author Nadib Bandi
 */
namespace SEOSmuggler;

/**
 * SEOSmuggler Abstract ressource
 *
 * @package SEOSmuggler
 */
class Ressource {
    
    /** @var array $datas Array of ressource datas */
    private $datas;
        
    /**
     * Ressource constructor
     * 
     * @param array $datas Ressource datas
     *     @var string  $body           HTTP response body
     *     @var string  $error          Error string
     *     @var string  $header         HTTP response header
     *     @var int     $id             Ressource id
     *     @var bool    $internal       Internal or not
     *     @var string  $mimetype       Ressource mimetype
     *     @var string  $redirectURL    Redirection URL
     *     @var int     $referersCount  Referers count
     *     @var array   $referers       Referers array
     *     @var int     $statusCode     HTTP status code
     *     @var string  $type           Ressource type
     *     @var string  $url            Ressource url
     */
    public function __construct(array $datas)
    {
        $this->datas = $datas;
        $this->datas['referersCount'] = 0;
        $this->datas['referers'] = [];
        if ($this->get('statusCode') >= 400 || $this->get('error')) {
            $color = "\033[31m";
        } elseif ($this->get('statusCode') < 300) {
            $color = "\033[32m";
        } elseif ($this->get('statusCode') < 400) {
            $color = "\033[33m";
        }
        echo $color.' '.$this->get('id').', '.$this->get('url').', '.$this->get('statusCode').", ".$this->get('ressourceType')." \033[0m ".PHP_EOL;
    }
    
    /**
     * Get attribute value
     * 
     * @param string $attribute Attribute name.
     * @return mixed Found value or null.
     */
    public function get($attribute)
    {
        if (isset($this->datas[$attribute]) === true) {
            return $this->datas[$attribute];
        }
        return null;
    }
    
    /**
     * Add a referer to the current ressource
     * 
     * @param array $datas Array of ressource
     *     @var string $alt     Alt attribute for images
     *     @var string $anchor  Link anchor text
     *     @var string $rel     Rel attribute
     *     @var string $tag     HTML tag of the referer
     *     @var string $target  Link target
     *     @var string $url     Referer URL
     */
    public function addReferer($datas)
    {
        array_push($this->datas['referers'], $datas);
        $this->datas['referersCount']++;
    }
}