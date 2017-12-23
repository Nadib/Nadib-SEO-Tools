<?php
/**
 * SEOSmuggler options
 *
 * @package SEOSmuggler
 * @author Nadib Bandi
 */
namespace SEOSmuggler;

/**
 * SEOSmuggler options
 *
 * @package SEOSmuggler
 */
class Options
{
    
    /** @var [string] $tags HTML Tags supported for ressources */
    private $tags = [Smuggler::TAG_A => 'href', Smuggler::TAG_LINK => 'href', Smuggler::TAG_SCRIPT => 'src', Smuggler::TAG_IMG => 'src'];
    
    /** @var [\SEOSmuggler\Report] Report objects */
    private $reports = [];
    
    /** @var bool Follow links */
    private $follow = true;
    
    /**
     * Add a report
     * 
     * @paran string $name Report name
     * @return \SEOSmuggler\Report The output object
     */
    public function addReport(string $name)
    {
        $this->reports[$name] = new Report($name);
        return $this->reports[$name];
    }
    
    /**
     * No follow parsed links
     */
    public function noFollow(){
        $this->follow = false;
    }
    
    /**
     * Get the links follow behavior
     * 
     * @return bool TRUE or FALSE
     */
    public function canFollow(){
        return $this->follow;
    }
    
    /**
     * Get the report objects
     * 
     * @return [\SEOSmuggler\Report]
     */
    public function getReports()
    {
        return $this->reports;
    }
    
    /**
     * Get a report instance
     * 
     * @param string $name The name of the report to get
     * @return SEOSmuggler\Report|null This method will return null if the report was not found.
     */
    public function getReport($name)
    {
        if (isset($this->reports[$name]) === true) {
            return $this->reports[$name];
        }
        return null;
    }
    
    /**
     * Exclude HTML tags from analyse
     * 
     * @param [string] $tags An array of tags to exclude
     */
    public function excludeTags(array $tags)
    {
       foreach ($tags as $tag) {
           if (isset($this->tags[$tag])) {
               unset($this->tags[$tag]);
           }
       } 
    }
    
    /**
     * Get the tags to inspect during HTML parsing
     * 
     * @return [string] An associative array
     */
    public function getTags()
    {
        return $this->tags;
    }
}