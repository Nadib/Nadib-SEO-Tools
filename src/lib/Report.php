<?php
/**
 * Report
 *
 * @package SEOSmuggler
 * @author Nadib Bandi
 */
namespace SEOSmuggler;

/**
 * SEOSmuggler report
 *
 * @package SEOSmuggler
 */
class Report {
    
    /** @var string CSV formatter */
    const CSV = '\SEOSmuggler\Format\CSV';
    
    /** @var string JSON formatter */
    const JSON = '\SEOSmuggler\Format\JSON';
    
    /** @var string XML formatter */
    const XML = '\SEOSmuggler\Format\XML';
    
    /** @var array $outputs Outputs formatter */
    private $outputs = [];
    
    /** @var [string] $formats Formatted datas */
    private $formats = [];
    
    /** @var string $name Report name */
    private $name;
    
    /** @var string $sort Sort attribute */
    private $sort = 'id';
    
    /** @var string $sortDirection Sorting direction */
    private $sortDirection = 'asc';
    
    /** @var \SEOSmuggler\Query Report query  */
    private $query;
    
    /** @var array Report datas */
    private $datas;
    
    /** @var [string] Report attributes */
    private $attributes = array(
            'id',
            'url',
            'statusCode',
            'type',
            'mimetype',
            'internal',
            'referers count',
            'error');
    
    /**
     * Construct report
     * 
     * @param string $name Report name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    
    /**
     * Add output to write the report into file
     * 
     * @param string $format Report format \SEOSmuggler\Report::JSON, \SEOSmuggler\Report::CSV, \SEOSmuggler\Report::XML
     * @param string $file Path to the file to write
     */
    public function addOutput(string $format, string $file)
    {
        $this->outputs[$file] = $format;
    }
    
    /**
     * Exclude attributes from report
     * 
     * @param array $attributes An array of attributes to exclude or null to remove all attributes.
     */
    public function excludeAttributes(array $attributes = null)
    {
        if ($attributes === null) {
            $this->attributes = [];
        } else {
            $this->attributes = array_diff($this->attributes , $attributes);
        }
    }
    
    /**
     * Includes additional attributes
     * 
     * @param array $attributes An array of attributes to include in the report
     * @param bool $reset if TRUE remove all existing attributes before to includes new ones.
     */
    public function includeAttributes(array $attributes,bool $reset = false)
    {
        if ($reset === true) {
            $this->attributes = [];
        }
        foreach($attributes as $attribute){
            array_push($this->attributes, $attribute);
        }        
    }
    
    /**
     * Define the datas sorting
     * 
     * @param string $attribute Attribute key for sorting.
     * @param string $direction Sort direction
     */
    public function sort(string $attribute, string $direction = 'asc')
    {
        $this->sort = $attribute;
        $this->sortDirection = $direction;
    }
    
    /**
     * Set a query to filter report datas
     * 
     * @param \SEOSmuggler\Query $query Query object
     */
    public function setQuery(Query $query)
    {
        $this->query = $query;
    }
    
    /**
     * Buil the report
     * 
     * @param array $ressources Ressources
     */
    public function build(array $ressources)
    {           
        if ($this->query) {
            $ressources = $this->query->doQuery($ressources);
        }           
        uasort($ressources, function ($a, $b) {
             if ($a->get($this->sort) == $b->get($this->sort)) {
                return 0;
            }
            return ($a->get($this->sort) < $b->get($this->sort)) ? -1 : 1;
        });
        if ($this->sortDirection === 'desc') {
            $ressources = array_reverse($ressources);
        }
        $datas = [];
        foreach ($ressources as $ressource) {
            $row = [];
            foreach ($this->attributes as $attribute) {
                $row[$attribute] = $ressource->get($attribute);
            }
            array_push($datas, $row);
        }
        foreach ($this->outputs as $file => $formatter) {   
            $this->formats[$formatter] = $formatter::format($datas, $file);
        }
        // SHARE 
        $this->datas = $datas;
    }
    
    /**
     * Get the reported datas
     * 
     * @param string $format Output format \SEOSmuggler\Report::JSON, \SEOSmuggler\Report::CSV, \SEOSmuggler\Report::XML
     * @return array|string|null
     */
    public function getDatas(string $format = null){
        if ($this->datas === null) {
            return null;
        } elseif ($format === null) {
            return $this->datas;
        } elseif (isset($this->formats[$format]) === false) {
           $this->formats[$format] = $formatter::format($this->datas);
        }
        return $this->formats[$format];
    }
}