Description
InstantAPI is a plugin that automatically turns any page on your MODX site into a JSON feed. This can be useful when building out an AJAX-based site or web service.

Installation
1. Install the plugin via the MODX repository, and make sure the "OnPageNotFound" event is checked.
2. Visit any page on your site, and add ".json" to the end of the URL. The page will return a JSON-formatted string containing all of the page's data.

Roadmap/Wishlist
1. Fix problem with tags in the content area not being parsed before JSONification (help me, Hamstra-wan Kenobi, you're my only hope)
2. Add support for template variable content