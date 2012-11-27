<?php
/**
 *
 * InstantAPI
 *
 * A MODX plugin that instantly turns any MODX resource into a JSON feed, just by adding ".json" to the end of the URL
 *
 * @ author Aaron Ladage
 * @ version 1.0.1 - November 27, 2012
 *
**/

$uri = $_SERVER["REQUEST_URI"]; // Get the URI
$uri_array = parse_url($uri);
$error_page = $modx->getOption('error_page'); // Get the error page

if (isset($uri_array['path']) && preg_match('/\.json$/i',$uri_array['path'])) {
    $cleanedUri = str_replace(array('.json','/'),'',$uri); 
    $cleanedUri = preg_replace('/\?.*/', '', $cleanedUri);
    $page = $modx->getObject('modResource',array('uri' => $cleanedUri));
    if ($page) {
        $fields = $page->toArray();        
        $json = $modx->toJSON($fields);
        session_write_close();
        die($json);
    }
    else {
        $modx->sendForward($error_page);
    }
}