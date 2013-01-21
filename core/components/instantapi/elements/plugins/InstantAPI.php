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
$parseContent = $modx->getOption('instantapi.parse_content', null, true);
$parseAllFields = $modx->getOption('instantapi.parse_all_fields', null, false);

if (isset($uri_array['path']) && preg_match('/\.json$/i',$uri_array['path'])) {
    $cleanedUri = str_replace(array('.json','/'),'',$uri); 
    $cleanedUri = preg_replace('/\?.*/', '', $cleanedUri);
    $page = $modx->getObject('modResource',array('uri' => $cleanedUri));
    if ($page) {
        $fields = $page->toArray();
        if ($parseAllFields) {
            foreach ($fields as $key => $value) {
                if ($key == 'content') continue;
                // Parse all cached tags
                $modx->parser->processElementTags('', $value, true, false, '[[', ']]', array(), 10);
                // Parse all uncached tags
                $modx->parser->processElementTags('', $value, true, true, '[[', ']]', array(), 10);
                // Put the value back
                $fields[$key] = $value;

            }
        }
        
        if ($parseContent) {
            $fields['content'] = $page->process();
        }
        
        $json = $modx->toJSON($fields);
        session_write_close();
        die($json);
    }
    else {
        $modx->sendForward($error_page);
    }
}
