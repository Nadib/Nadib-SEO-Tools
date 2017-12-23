<?php
/**
 * XML Formatter
 *
 * @package SEOSmuggler\Format
 * @author Nadib Bandi
 */
namespace SEOSmuggler\Format;

/**
 * XML Formatter
 *
 * @package SEOSmuggler\Format
 */
class XML extends AbstractFormat{
    
    /**
     * @inheritDoc
     */
    public static function format(array $datas, string $output = null)
    {
        $xml = self::createNodes('ressources', 'ressource', [], $datas);
        if ($output) {
            $fp = fopen($output, 'w');
            fwrite($fp, $xml);
            fclose($fp);
        }
        return $xml;
    }
    
    /**
     * Create XML nodes
     * 
     * @param string $rootTag Root tag name
     * @param string $nodesTag Nodes tag name
     * @param array $rootAttributes Root level attributes
     * @param array $nodes Nodes array
     * @return string
     */
    private static function createNodes(string $rootTag, string $nodesTag, array $rootAttributes = array(), array $nodes = array())
    {
        $xml = '<'.$rootTag;        
        $nodeContent = '';
        foreach ($rootAttributes as $k => $v) {
            if (is_array($v)) {
                if (count($v) > 0) {
                    if ($rootTag === 'referers') {
                        $k = 'referer';
                    }
                    $nodeContent .= self::createNodes($k, '', $v);
                }
            } elseif (strlen($v) > 100) {
                $nodeContent .= '<'.$k.'>'.$v.'</'.$k.'>';
            } elseif (strlen($v) > 0) {
                $xml .= ' '.$k.'="'.$v.'"';
            }
        }
        $xml .= '>';
        $xml .= $nodeContent;
        $nodesCount = count($nodes);
        for ($i = 0; $i < $nodesCount; $i++) {
            $xml .= self::createNodes($nodesTag, '', $nodes[$i]);
        }
        $xml .= '</'.$rootTag.'>'.PHP_EOL;
        return $xml;
    }
}