<?php
/**
 * JSON Formatter
 *
 * @package SEOSmuggler\Format
 * @author Nadib Bandi
 */
namespace SEOSmuggler\Format;

/**
 * JSON Formatter
 *
 * @package SEOSmuggler\Format
 */
class JSON extends AbstractFormat{
    
    /**
     * @inheritDoc
     */
    public static function format(array $datas, string $output = null)
    {
        $json = json_encode($datas);
        if ($output) {
            $fp = fopen($output, 'w');
            fwrite($fp, $json);
            fclose($fp);
        }
        return $json;
    }
}