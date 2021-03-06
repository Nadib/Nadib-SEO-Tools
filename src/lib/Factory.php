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
    /** @var \SEOSmuggler\Options $options Options */
    private $options;
    
    /** @var \SEOSmuggler\Connector $connector HTTP Connector */
    private $connector;
    
    /** @var string $rootURL Root URL */
    private $rootURL;
    
    /** @var [\SEOSmuggler\Ressource] $ressources Array of built ressources */
    private $ressources = [];
    
    /** @var [string] $mimetypeMapping Mimetype mapping array */
    private $mimetypeMapping = [
        'javascript' => Smuggler::TYPE_JAVASCRIPT,
        'css' => Smuggler::TYPE_CSS,
        'html' => Smuggler::TYPE_HTML,
        'plain' => Smuggler::TYPE_TEXT,
        'pdf' => Smuggler::TYPE_PDF,
        'json' => Smuggler::TYPE_JSON,
        'image' => Smuggler::TYPE_IMAGE,
        'audio' => Smuggler::TYPE_AUDIO,
        'video' => Smuggler::TYPE_VIDEO
    ];
    
    /**
     * Factory constructor
     * 
     * @param string $rootURL Root URL
     * @param \SEOSmuggler\Options $options Smuggler options
     */
    public function __construct(string $rootURL, \SEOSmuggler\Options $options)
    {
        libxml_use_internal_errors(true);
        $this->connector = new \SEOSmuggler\Connector();
        $this->rootURL = $rootURL;
        $this->options = $options;
    }

    /**
     * Create or get an allready created ressource
     * 
     * @param string $url Ressource URL
     * @return \SEOSmuggler\Ressource Ressource object
     */
    public function getRessource(string $url=null)
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
        if ($this->ressources[$url]->get('type') === Smuggler::TYPE_HTML && $datas['internal'] === true) {        
            $follow = true;
            if ($this->options->canFollow() === false && $this->rootURL !== $url) {
                $follow = false;
            }
           
            
            if ($follow) {                
                $this->parseLinks($this->ressources[$url]);
            }
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
     * Get options object
     * 
     * @return \SEOSmuggler\Options
     */
    public function getOptions(){
        return $this->options;
    }
    
    /**
     * Clean properly the factory
     */
    public function clean()
    {
        $this->connector->close();
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
            $datas['type'] = Smuggler::TYPE_ERROR;
        } else {
            $datas['type'] = Smuggler::TYPE_GENERIC;
            foreach ($this->mimetypeMapping as $k => $v) {
                $pos = strpos($datas['mimetype'], $k);
                if ($pos !== false) {
                    if ($k === 'html' && $datas['internal'] === true) {
                        $datas['body'] = $this->connector->get($datas['url']);
                        $datas['content'] = $this->analysePageContent($datas['body']);
                    }
                    $datas['type'] = $v;
                    break;
                }
            }
        }
        return new Ressource($datas);
    }
    
    /**
     * Analyse page content.
     * @param string $htmlBody
     */
    private function analysePageContent(string $htmlBody)
    {
        $DOMDocument = new \DOMDocument();
        $DOMDocument->strictErrorChecking = FALSE;
        $DOMDocument->loadHTML($htmlBody);
        
        $analyse = [];
        $htmls = $DOMDocument->getElementsByTagName('html');
        foreach ($htmls as $htm) {
            $analyse['lang'] =  $htm->getAttribute('lang');
        }
        $titles = $DOMDocument->getElementsByTagName('title');
        foreach ($titles as $title) {
            $analyse['title'] = $title->nodeValue;
        }
        $metas = $DOMDocument->getElementsByTagName('meta');
        foreach ($metas as $meta) {
            if ($meta->getAttribute('name') === 'description') {
                $analyse['description'] = $meta->nodeValue;
            }
        }
        
        $headings = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        $analyse['headings'] = [];
        foreach ($headings as $heading) {
            $heads = $DOMDocument->getElementsByTagName($heading);
            if ($heads->length > 0) {
                $analyse['headings'][$heading] = array();
                foreach ($heads as $head) {
                    array_push($analyse['headings'][$heading], $head->nodeValue);
                }
            }
        }
        
        $bodys = $DOMDocument->getElementsByTagName('body');
        foreach ($bodys as $body) {
            $analyse['content'] = $body->nodeValue;
        }
        
        $as = $DOMDocument->getElementsByTagName('a');
        $analyse['links'] = array();
        foreach ($as as $a) {
           $curLink = array('href' => $a->getAttribute('href'), 
                        'anchor' => $a->nodeValue,
                        'target' => $a->getAttribute('target'),
                        'title' => $a->getAttribute('title'),
                        'rel' => $a->getAttribute('rel'));
           array_push($analyse['links'], $curLink);
        }
        
        $images = $DOMDocument->getElementsByTagName('img');
        $analyse['images'] = array();
        foreach ($images as $img) {
            $curImage = array('src' => $img->getAttribute('src'),
                'title' => $img->getAttribute('title'),
                'alt' => $img->getAttribute('alt'),
                'srcset' => $img->getAttribute('srcset'),
                'sizes' => $img->getAttribute('sizes'),
                'width' => $img->getAttribute('width'),
                'height' => $img->getAttribute('height')
                );
            array_push($analyse['images'], $curImage);
        }
        
        return $analyse;
    }
    
    /**
     * Parse links into internal web page
     * 
     * @param \SEOSmuggler\Ressource $page Page object.
     */
    private function parseLinks(\SEOSmuggler\Ressource $page)
    {    
        $DOMDocument = new \DOMDocument();
        $DOMDocument->strictErrorChecking = FALSE;
        $DOMDocument->loadHTML($page->get('body'));        
        foreach ($this->options->getTags() as $tag => $attribute) {
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
                    
                    if($this->options->canAnalyse($cleanLink)){
                        $this->getRessource($cleanLink)->addReferer($refererDatas);
                    }
                    
                                        
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
    private function formatURL(string $url)
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