<?php
/**
 * SEOSmuggler
 * @package SEOSmuggler
 */
namespace SEOSmuggler;
use SEOSmuggler;

include_once('autoloader.php');

if (empty($argv)) {
    throw new \Exception('SEOMuggler require CLI.');
}
if (isset($argv[1]) === false) {
    throw new \Exception('SEOMuggler Missing argument 1.');
}

$smuggler = Smuggler::get($argv[1]);
$ressources = $smuggler->analyse();

$csv = fopen('/Users/Busfront-Nomad/desktop/seo.csv', 'w');
fputcsv($csv, array(
    'index', 
    'url', 
    'http_status_code',
    'type', 
    'mimetype',
    'internal',
    'referers count',
    'referers',
    'error'));

foreach ($ressources as $ressource) {
    $source_pages = '';
    fputcsv($csv, array(
       $ressource->get('id'), 
       $ressource->get('url'), 
       $ressource->get('statusCode'), 
       $ressource->get('type'), 
       $ressource->get('mimetype'), 
       $ressource->get('internal'),
       $ressource->get('referersCount'),
       /*$ressource->get('referers')*/'',
       $ressource->get('error')));
}
fclose($csv);