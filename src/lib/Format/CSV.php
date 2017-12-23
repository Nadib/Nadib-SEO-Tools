<?php
/**
 * CSV formatter
 *
 * @package SEOSmuggler\Format
 * @author Nadib Bandi
 */
namespace SEOSmuggler\Format;

/**
 * CSV formatter
 *
 * @package SEOSmuggler\Format
 */
class CSV extends AbstractFormat{
   
    /**
     * @inheritDoc
     */
    public static function format(array $datas, string $output = null)
    {
        if($output){
            $CSVFile = fopen($output, 'w');
        }
        $countRows = count($datas);
        $attributes = [];
        $csvCode = '';
        for ($i=0; $i < $countRows; $i++) {
            $row = [];
            foreach($datas[$i] as $attribute => $cell){
                if ($i === 0) {
                    array_push($attributes, $attribute);
                }
                if(is_array($cell) === true){
                    $cell = json_encode($cell);
                }
                array_push($row, $cell);
            }
            if ($output) {
                if ($i === 0) {
                    fputcsv($CSVFile, $attributes);
                }
                fputcsv($CSVFile, $row);
            }
            if ($i === 0) {
                $csvCode = implode(',', $attributes).'\r\n';
            }
            $csvCode .= implode("','", $row)."\r\n";
        }
        if($output){
            fclose($CSVFile);
        }
        return $csvCode;
    }
}