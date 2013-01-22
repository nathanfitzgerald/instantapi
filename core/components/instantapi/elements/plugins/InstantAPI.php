<?php
/**
 *
 * InstantAPI
 *
 * A MODX plugin that instantly turns any MODX resource into a JSON feed, just by adding ".json" to the end of the URL
 *
 * @ author Aaron Ladage
 * @ version 1.0.2 - January 22, 2013
 * 
 * SYSTEM EVENTS:
 *   OnPageNotFound
 *   OnDocFormSave
 *
 * PLUGIN OPTIONS:
 *   instantapi.parse_content - defines if the content field should be parsed
 *   instantapi.parse_all_fields - defines if all fields (except content) should be parsed
 *   instantapi.cache_expire_time - defines the expiration time of the cache (0 for endless caching)
 *
**/

$eventName = $modx->event->name;

switch($eventName) {

	case 'OnPageNotFound':
		$uri = $_SERVER["REQUEST_URI"]; // Get the URI
		$uri_array = parse_url($uri);
		$error_page = $modx->getOption('error_page'); // Get the error page
		$parseContent = $modx->getOption('instantapi.parse_content', $scriptProperties, true);
		$parseAllFields = $modx->getOption('instantapi.parse_all_fields', $scriptProperties, false);
		$cacheExpireTime = $modx->getOption('instantapi.cache_expire_time', $scriptProperties, 0);
		
		if (isset($uri_array['path']) && preg_match('/\.json$/i',$uri_array['path'])) {
		    $cleanedUri = str_replace(array('.json','/'),'',$uri); 
		    $cleanedUri = preg_replace('/\?.*/', '', $cleanedUri);
		    $page = $modx->getObject('modResource',array('uri' => $cleanedUri));
		    if ($page) {
		    
		    	$cacheKey = "instantapi/" . $cleanedUri;
		    	
		    	// get json from cache
		    	$json = $modx->cacheManager->get($cacheKey);
		    	
		    	if (!$json) { // if no cached version is available
		    	
			        $fields = $page->toArray();
			        
			        // parse all fields (except content)
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
			        
			        // parse content
			        if ($parseContent) {
			            // Parse all cached tags
			            $modx->parser->processElementTags('', $fields['content'], true, false, '[[', ']]', array(), 10);
			            // Parse all uncached tags
			            $modx->parser->processElementTags('', $fields['content'], true, true, '[[', ']]', array(), 10);
			        }
			        
			        $json = $modx->toJSON($fields);
			        
			        // put $json in the modx cache
			        $modx->cacheManager->set($cacheKey,$json,$cacheExpireTime);
		    	}
		        
		        session_write_close();
		        die($json);
		    }
		    else {
		        $modx->sendForward($error_page);
		    }
		}		break;
	
	
	
	case 'OnDocFormSave':
		$modx->cacheManager->refresh(
			array('default' => 
				array('instantapi/' . $resource->get("uri"))
			)
		);
		break;
}
