### kitFramework CMS Tool

(c) 2013 by phpManufaktur
Ralf Hertsch, Stockholmer Str. 31, 13359 Berlin (Germany)
http://phpManufaktur.de - ralf.hertsch@phpManufaktur.de

**0.29** - 2013-10-06

* bugfix: namespace for LEPTON output_interface was wrong

**0.28** - 2013-10-06

* changed search function and `kit_framework_search` addon

**0.27** - 2013-09-25

* changed access to redirected Github repositories

**0.26** - 2013-09-16

* bugfix: added missing `require_once` for `JSONFormat` 

**0.25** - 2013-09-16

* removed obsolete code, added framework option CACHE
* added JSON formatter for better readable *.json files
* changed `FRAMEWORK_TEMPLATES` in `framework.json` from comma separated string to array
* added hint for usage of `FallbackResource` instead of `mod_rewrite` in `.htaccess`
* added config parameter `[OUTPUT_FILTER][METHOD]=STANDARD`

**0.24** - 2013-08-16

* added support for WebsiteBaker 2.8.4

**0.23** - 2013-08-06

* added support for proxy authentication
* added register_filter() for BlackCat CMS

**0.22** - 2013-08-02

* moved output filters to kitFramework::Basic
* added support for BlackCat CMS
* fixed a cURL SSL problem
* added support for Windows installations

**0.20** - 2013-05-10

* added getVersion to outputFilter
* changed parameter handling in outputFilter to POST
* added FILTER handling to outputFilter

**0.19** - 2013-05-02

* added User Agent (UA) for GitHub access

**0.15** - 2013-03-28

* added output filter to enable usage of kitCommands

**0.13** - 2013-03-03

* added creation of the CMS base configuration
* added creation of the Framework base configuration

**0.10** - 2013-02-14

* initial release