Description
InstantAPI is a plugin that automatically turns any page on your MODX site into a JSON feed. This can be useful when building out an AJAX-based site or web service.
Fields are optionally parsed and cached.

Installation
1. Install the plugin via the MODX repository, and make sure the "OnPageNotFound" event is checked.
2. Visit any page on your site, and add ".json" to the end of the URL. The page will return a JSON-formatted string containing all of the page's data.

Options
instantapi.parse_content - defines if the content field should be parsed
instantapi.parse_all_fields - defines if all fields (except content) should be parsed
instantapi.cache_expire_time - defines the expiration time of the cache (0 for endless caching)