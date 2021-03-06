<?php
/**
 *
 * InstantAPI
 *
 * A MODX plugin that instantly turns any MODX resource into a JSON feed, just by adding ".json" to the end of the URL
 *
 * @ author Aaron Ladage
 * @ updated for MODX 2.3.x by Nathan Fitzgerald
 * @ version 1.0.3 - June 25, 2015
 * 
 * SYSTEM EVENTS:
 *   OnPageNotFound
 *   OnDocFormSave
 *
 * PLUGIN PROPERTIES:
 *   parse_content - defines if the content field should be parsed
 *   parse_all_fields - defines if all fields (except content) should be parsed
 *   include_tvs - defines if TemplateVar values should be included
 *   include_tv_list - an optional comma-delimited list of TVs to include explictly if includeTVs is 1
 *   prepare_tvs - defines if media source-dependent TemplateVar values are prepared
 *   prepare_tv_list - limits the TVs that are prepared to those specified by name in a comma-delimited list
 *   process_tvs - indicates if TemplateVar values should be rendered
 *   process_tv_list - an optional comma-delimited list of TemplateVar names to process explicitly
 *   cache_expire_time - defines the expiration time of the cache (0 for endless caching)
 *
**/

$eventName = $modx->event->name;

switch($eventName) {

	case 'OnPageNotFound':
		$uri = $_SERVER["REQUEST_URI"]; // Get the URI
		$uri_array = parse_url($uri);
		$error_page = $modx->getOption('error_page'); // Get the error page
		$parseContent = $modx->getOption('parse_content', $scriptProperties, true);
		$parseAllFields = $modx->getOption('parse_all_fields', $scriptProperties, true);
    $includeTVs = $modx->getOption('include_tvs', $scriptProperties, false);
    $includeTVList = !empty($modx->getOption('include_tv_list', $scriptProperties, false)) ? explode(',', $modx->getOption('include_tv_list', $scriptProperties, false)) : array();
    $prepareTVs = $modx->getOption('prepare_tvs', $scriptProperties, true);
    $prepareTVList = !empty($modx->getOption('prepare_tv_list', $scriptProperties, false)) ? explode(',', $modx->getOption('prepare_tv_list', $scriptProperties, false)) : array();
    $processTVs = $modx->getOption('process_tvs', $scriptProperties, false);
    $processTVList = !empty($modx->getOption('process_tv_list', $scriptProperties, false)) ? explode(',', $processTVList) : array();
		$cacheExpireTime = $modx->getOption('cache_expire_time', $scriptProperties, 0);
		$maxIterations= (integer) $modx->getOption('parser_max_iterations', null, 10);
		
		if (isset($uri_array['path']) && preg_match('/\.json$/i', $uri_array['path'])) {
		  $cleanedUri = str_replace(array('.json','/'),'',$uri); 
		  $cleanedUri = preg_replace('/\?.*/', '', $cleanedUri);

      $htmlContentType = $modx->getObject('modContentType', array('name' => 'HTML'));

      $c = $modx->newQuery('modResource');
      $c->where(array(
        array(
          'uri:IN' => array(
            $cleanedUri . $htmlContentType->get('file_extensions'),
            $cleanedUri . '/',
            $cleanedUri,
          )
        ),
        array(
          'context_key' => $modx->context->key
        )
      ));

		  $page = $modx->getObject('modResource', $c);
      
		  if ($page) {
		    
		  	$cacheKey = 'instantapi/' . $modx->context->key . '/' . $cleanedUri;
		  	
		  	// check if resource is cacheable
		  	if ($page->get('cacheable')) {
			  	// get $fields from cache
			  	$fields = $modx->cacheManager->get($cacheKey);
		  	}
		  	
		  	if ($fields) { // cached version available
		  	
		  		$fields['instantAPI'] = "cached";
		  		$modx->resource =& $page;
		  	}
        else { // no cached version available
		  	
			    $fields = $page->toArray();
			    
			    // parse all fields (except content)
			    if ($parseAllFields) {
			      foreach ($fields as $key => $value) {
			        if ($key == 'content') continue;
			        // Parse all cached tags
			        $modx->parser->processElementTags('', $fields[$key], false, false, '[[', ']]', array(), $maxIterations);
			      }
			    }
			    
			    // parse content
			    if ($parseContent) {
			      // Parse all cached tags
			      $modx->parser->processElementTags('', $fields['content'], false, false, '[[', ']]', array(), $maxIterations);
			    }

          // include TVs
          if ($includeTVs) {
            $templateVars = array();
            if (!empty($includeTVList)) {
              $templateVars = $modx->getCollection('modTemplateVar', array('name:IN' => $includeTVList));
            }
            else {
              $templateVars = $page->getMany('TemplateVars');
            }
            foreach ($templateVars as $tvId => $templateVar) {
              if (!empty($includeTVList) && !in_array($templateVar->get('name'), $includeTVList)) continue;
              if ($processTVs && (empty($processTVList) || in_array($templateVar->get('name'), $processTVList))) {
                $fields['tvs'][$templateVar->get('name')] = $templateVar->renderOutput($fields['id']);
              }
              else {
                $value = $templateVar->getValue($fields['id']);
                if ($prepareTVs && method_exists($templateVar, 'prepareOutput') && (empty($prepareTVList) || in_array($templateVar->get('name'), $prepareTVList))) {
                  $value = $templateVar->prepareOutput($value);
                }
                $fields['tvs'][$templateVar->get('name')] = $value;
              }
            }
          }

			    // put $fields in the modx cache
			    $modx->cacheManager->set($cacheKey, $fields, $cacheExpireTime);
			    
			    // add "uncached" value, since current output is uncached
			    $fields['instantAPI'] = 'uncached';
		  	}
		      
		     
		    // Parse uncached tags in all fields and content
		    if ($parseAllFields) {
		      foreach ($fields as $key => $value) {
		        if ($key == 'content') continue;
		        $modx->parser->processElementTags('', $fields[$key], true, true, '[[', ']]', array(), $maxIterations);
		      }
		    }
		    if ($parseContent) {
		      $modx->parser->processElementTags('', $fields['content'], true, true, '[[', ']]', array(), $maxIterations);
		    }
			    
			        
		    $mtime= microtime();
		    $mtime= explode(" ", $mtime);
			  $mtime= $mtime[1] + $mtime[0];
			  $tsum= round(($mtime - $modx->startTime) * 1000, 0) . " ms";
			
			  $fields['rendertime'] = $tsum;
		    
		    $json = $modx->toJSON($fields);
		    session_write_close();
		    die($json);
		  }
		  else {
		    $modx->sendForward($error_page);
		  }
		}
    break;
	
	case 'OnDocFormSave':
    $uri = $resource->get('uri');
    $cleanedUri = str_replace(array('.json','/'),'',$uri); 
    $cleanedUri = preg_replace('/\?.*/', '', $cleanedUri);
    $cacheKey = 'instantapi/' . $resource->get('context_key') . '/' . $cleanedUri;
    $modx->cacheManager->delete($cacheKey);
		break;
}