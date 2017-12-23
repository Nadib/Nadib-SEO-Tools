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
class Smuggler 
{
    /** @var string HTML a tag */
    const TAG_A = 'a';
    /** @var string HTML link tag */
    const TAG_LINK = 'link';
    /** @var string HTML script tag */
    const TAG_SCRIPT = 'script';
    /** @var string HTML img tag */
    const TAG_IMG = 'img';
    
    /** @var string Javascript type */
    const TYPE_JAVASCRIPT = 'javascript';
    /** @var string CSS type */
    const TYPE_CSS = 'css';
    /** @var string HTML type */
    const TYPE_HTML = 'html';
    /** @var string Text type */
    const TYPE_TEXT = 'text';
    /** @var string PDF type */
    const TYPE_PDF = 'pdf';
    /** @var string JSON type */
    const TYPE_JSON = 'json';
    /** @var string Image type */
    const TYPE_IMAGE = 'image';
    /** @var string Audio type */
    const TYPE_AUDIO = 'audio';
    /** @var string Video type */
    const TYPE_VIDEO = 'video';
    /** @var string Error type */
    const TYPE_ERROR = 'error';
    /** @var string Generic type */
    const TYPE_GENERIC = 'generic';
    
    /** @var \SEOSmuggler\Factory $factory Ressources factory */
    private $factory;

    public function __construct(string $rootURL, \SEOSmuggler\Options $options = null)
    {
        if($options === null){
            $options = new Options();
        }
        $this->factory = new \SEOSmuggler\Factory($rootURL, $options);
        $this->factory->getRessource();
        $this->factory->clean();
        foreach ($options->getReports() as $report) {
            $report->build($this->factory->getRessources());
        }
    }
    
    /**
     * Get Report datas
     * 
     * @param string $report Report name
     * @param string $format Output format \SEOSmuggler\Report::JSON, \SEOSmuggler\Report::CSV, \SEOSmuggler\Report::XML
     * @return array|string|null
     */
    public function getReportDatas(string $report, string $format = null)
    {
        if ($this->factory) {
            $reportObject = $this->factory->getOptions()->getReport($report);
            if ($reportObject) {
                return $reportObject->getDatas($format);
            }
        }
        return null;
    }
    
    /**
     * Get all parsed ressources
     * @return [\SEOSmuggler\Ressource]|null
     */
    public function getRessources(){
        if ($this->factory) {
            return $this->factory->getRessources();
        }
        return null;
    }
}