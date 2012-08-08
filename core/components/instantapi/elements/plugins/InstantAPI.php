<?php

/**
 *
 * InstantAPI
 *
 * A MODX plugin that instantly turns any MODX resource into a JSON feed, just by adding ".json" to the end of the URL
 *
 * @ author Aaron Ladage
 * @ version 1.0.0 - August 8, 2012
 *
 * Save as a plugin, and check the OnPageNotFound system event.
**/

/* Set parameters */
$uri = $_SERVER["REQUEST_URI"]; // Get the URI
$extension = pathinfo($uri, PATHINFO_EXTENSION); // Get the extension from the URI
$cleanedUri = str_replace(array('.json'),'',$uri);
$cleanedUri = preg_replace('/\?.*/', '', $cleanedUri);
$error_page = $modx->getOption('error_page');

if (($extension != 'ico') && ($extension == 'json')) {
    $page = $modx->getObject('modResource',array('uri' => $cleanedUri));
    if ($page) {
        $fields = $page->toArray();
        $fields['url'] = $modx->getOption('site_url').$page->get('uri');
        $json = $modx->toJSON($fields);
        session_write_close();
        die($json);
    }
    else {
        $modx->sendForward($error_page);
    }
}