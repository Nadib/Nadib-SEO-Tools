<?php
/**
 * HTTP connector
 * 
 * @package SEOSmuggler
 * @author Nadib Bandi
 */
namespace SEOSmuggler;

/**
 * HTTP connector
 *
 * @package SEOSmuggler
 */
class Connector
{
    /** @var curl $curl Curl ressource */
    private $curl;
    
    /** @var array $requestHeader HTTP request header */
    private $requestHeader=[];
    
    /** @var int $index Connection index */
    private $index=1;

    /**
     * HTTP connector constructor.
     */
    public function __construct()
    {
        $this->setRequestHeader('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8');
        $this->setRequestHeader('Accept-Language', 'fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7');
        $this->setRequestHeader('Cache-Control', 'max-age=0');
        $this->setRequestHeader('Connection', 'keep-alive');
    }
    
    /**
     * Close connection properly
     */
    public function close()
    {
        if($this->curl){
            curl_close($this->curl);
            $this->curl = null;
        }
    }
    
    /**
     * Set HTTP request header element
     * 
     * @param string $name Header element name. 
     * @param string $value Header element value
     */
    public function setRequestHeader($name, $value)
    {
        $this->requestHeader[$name] = $value;
    }
    
    /**
     * Execute HTTP GET request.
     * 
     * @param string $url URL to request.
     * @param bool $onlyHeader If TRUE only header is requested.
     * @return array|string If only header is requested the function will return an array of info. Otherwise it return the HTTP body.
     */
    public function get($url, $onlyHeader=false)
    {
        $this->initialize();
        $this->setRequestHeader('Host', parse_url($url, PHP_URL_HOST));
        
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->generateRequestHeader());
        curl_setopt($this->curl, CURLOPT_URL, $url);
	
        if ($onlyHeader === true) {
            curl_setopt($this->curl, CURLOPT_HEADER, true);
            curl_setopt($this->curl, CURLOPT_NOBODY, true);
        } else {
            curl_setopt($this->curl, CURLOPT_HEADER, false);
            curl_setopt($this->curl, CURLOPT_NOBODY, false);
        }
        
        ob_start();
        $success = curl_exec($this->curl);
        $result = ob_get_contents();
        ob_end_clean();
        
        $returnDatas = [];
        $returnDatas['url'] = $url;
        if ($onlyHeader === true) {
            $returnDatas['id'] = $this->index;
        }
        
        if ($success !== false) {            
            if ($onlyHeader === true) {
                $returnDatas['statusCode'] = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
                $returnDatas['mimetype'] = curl_getinfo($this->curl, CURLINFO_CONTENT_TYPE);
                $returnDatas['redirectURL'] = curl_getinfo($this->curl, CURLINFO_REDIRECT_URL);
                $returnDatas['header'] = $result;
            } else {
                return $result;
            }
        } else {
            $returnDatas['error'] = curl_error($this->curl);
        }
        $this->index++;
        return $returnDatas;  
    }
    
    /**
     * Initialize HTTP connection
     */
    private function initialize()
    {
        if ($this->curl === null) {
            $this->curl = curl_init();
            curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($this->curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36");
            $cookiefile = sys_get_temp_dir().'/SEOSmugglerCookie.txt';
            curl_setopt( $this->curl, CURLOPT_COOKIESESSION, true );
            curl_setopt( $this->curl, CURLOPT_COOKIEJAR,  $cookiefile );
            curl_setopt( $this->curl, CURLOPT_COOKIEFILE, $cookiefile );
        }     
    }
    
    /**
     * Generate the request header.
     * 
     * @return array A numeric array of the header elements.
     */
    private function generateRequestHeader()
    {
        $requestHeader = [];
        foreach ($this->requestHeader as $k => $v) {
            array_push($requestHeader, $k.': '.$v);
        }
        return $requestHeader;
    }   
}