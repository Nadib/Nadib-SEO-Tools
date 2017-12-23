<?php
/**
 * Abstract formatter
 *
 * @package SEOSmuggler\Format
 * @author Nadib Bandi
 */
namespace SEOSmuggler\Format;

/**
 * Abstract formatter
 *
 * @package SEOSmuggler\Format
 */
abstract class AbstractFormat {
    
    /**
     * Format datas
     * 
     * @param array $datas Datas to format
     * @param string $output Write output in file.
     * @return string Formatted datas as string
     */
    abstract public static function format(array $datas, string $output = null);
}