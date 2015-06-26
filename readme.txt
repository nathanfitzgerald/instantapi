Description
InstantAPI is a plugin that automatically turns any page on your MODX site into a JSON feed. This can be useful when building out an AJAX-based site or web service.
Fields are optionally parsed and cached.
Template Variables can be optionally included.

Installation
1. Install the plugin via the MODX repository, and make sure the "OnPageNotFound" and "OnDocFormSave" events are checked.
2. Visit any page on your site, and add ".json" to the end of the URL. The page will return a JSON-formatted string containing all of the page's data.

Properties
parse_content - defines if the content field should be parsed
parse_all_fields - defines if all fields (except content) should be parsed
include_tvs - defines if TemplateVar values should be included
include_tv_list - an optional comma-delimited list of TVs to include explictly if includeTVs is 1
prepare_tvs - defines if media source-dependent TemplateVar values are prepared
prepare_tv_list - limits the TVs that are prepared to those specified by name in a comma-delimited list
process_tvs - indicates if TemplateVar values should be rendered
process_tv_list - an optional comma-delimited list of TemplateVar names to process explicitly
cache_expire_time - defines the expiration time of the cache (0 for endless caching)