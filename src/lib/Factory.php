<?php
/**
 * Ressource factory
 *
 * @package SEOSmuggler
 * @author Nadib Bandi
 */
namespace SEOSmuggler;

/**
 * Ressource factory
 *
 * @package SEOSmuggler
 */
class Factory
{
    
    /** @var \SEOSmuggler\Connector $connector HTTP Connector */
    private $connector;
    
    /** @var string $rootURL Root URL (root domain */
    private $rootURL;
    
    /** @var [\SEOSmuggler\Ressource] $ressources Array of built ressources */
    private $ressources = [];
    
    /** @var [string] $tags HTML Tags supported for ressources */
    private $tags = ['a' => 'href', 'link' => 'href', 'script' => 'src', 'img' => 'src'];
    
    /** @var [string] $mimetypeMapping Mimetype mapping array */
    private $mimetypeMapping = [
        'javascript'=>'Javascript',
        'css' => 'CSS',
        'html' => 'HTML',
        'plain' => 'Text',
        'pdf' => 'PDF',
        'json' => 'JSON',
        'image' => 'Image',
        'audio' => 'Audio',
        'video' => 'Video'
    ];
    
    /**
     * Factory constructor
     * 
     * @param string $rootURL Root URL
     */
    public function __construct($rootURL)
    {
        libxml_use_internal_errors(true);
        $this->connector = new Connector();
        $this->rootURL = $rootURL;
    }

    /**
     * Create or get an allready created ressource
     * 
     * @param string $url Ressource URL
     * @return \SEOSmuggler\Ressource Ressource object
     */
    public function getRessource($url=null)
    {
        if ($url === null) {
            $url = $this->rootURL;
        }
        
        if (isset($this->ressources[$url]) === true) {
            return $this->ressources[$url];
        }
        
        $datas = $this->connector->get($url, true);
        $datas['internal'] = false;
        
        if (parse_url($datas['url'], PHP_URL_HOST) === parse_url($this->rootURL, PHP_URL_HOST)) {
            $datas['internal'] = true;
        }
        
        $this->ressources[$url] = $this->createRessource($datas);       
        
        if ($this->ressources[$url]->get('type') === 'HTML' && $datas['internal'] === true) {
            $this->parseLinks($this->ressources[$url]);
        } 
        
        return $this->ressources[$url];
    }
    
    /**
     * Get all ressources
     * 
     * @return [\SEOSmuggler\Ressource] Ressources
     */
    public function getRessources()
    {
        return $this->ressources; 
    }
    
    /**
     * Create ressources objects
     * 
     * @param array $datas Ressource datas
     * @return \SEOSmuggler\Ressource Created ressource object
     */
    private function createRessource(array $datas)
    {

        if (isset($datas['error']) === true) {
            $datas['type'] = 'Error';
        } else {
            $datas['type'] = 'Generic';
            foreach ($this->mimetypeMapping as $k => $v) {
                $pos = strpos($datas['mimetype'], $k);
                if ($pos !== false) {
                    if ($k === 'html' && $datas['internal'] === true) {
                        $datas['body'] = $this->connector->get($datas['url']);
                    }
                    $datas['type'] = $v;
                    break;
                }
            }
        }
        return new Ressource($datas);
    }
    
    /**
     * Parse links into internal web page
     * 
     * @param \SEOSmuggler\Ressource $page Page object.
     */
    private function parseLinks(Ressource $page)
    {    
        $DOMDocument = new \DOMDocument();
        $DOMDocument->strictErrorChecking = FALSE;
        $DOMDocument->loadHTML($page->get('body'));        
        foreach ($this->tags as $tag => $attribute) {
            $links = $DOMDocument->getElementsByTagName($tag);
            foreach ($links as $link) {
                $cleanLink = $this->formatURL($link->getAttribute($attribute));
                if ($cleanLink) {
                    $refererDatas = [
                        'url' => $page->get('url'), 
                        'tag' => $tag, 
                        'anchor' => $link->nodeValue,
                        'target' => $link->getAttribute('target'),
                        'title' => $link->getAttribute('title'),
                        'alt' => $link->getAttribute('alt'),
                        'rel' => $link->getAttribute('rel')
                    ];
                    $this->getRessource($cleanLink)->addReferer($refererDatas);
                }
            }   
        }
    }
    
    /**
     * Format URL
     * 
     * @param string $url URL to format
     * @return type
     */
    private function formatURL($url)
    {
        if (parse_url($url, PHP_URL_HOST) === null) {
            if (substr($url, 0,1) === '/') {
                $url = $this->rootURL.$url;
            } else {
                $url = $this->rootURL.'/'.$url;
            }
        }
        $exp_link_get = explode('?', $url);
	$url = $exp_link_get[0];
	$exp_link_anc = explode('#', $url);
	$url = $exp_link_anc[0];
        
        return $url;
    }
}