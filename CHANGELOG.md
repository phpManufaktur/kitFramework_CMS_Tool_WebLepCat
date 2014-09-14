### kitFramework CMS Tool

(c) 2013 by phpManufaktur
Ralf Hertsch, Stockholmer Str. 31, 13359 Berlin (Germany)
http://phpManufaktur.de - ralf.hertsch@phpManufaktur.de

**0.41** - 2014-09-14

* Changed formatting within the `tool.php` to grant that the background is always white and the extensions have a padding of 5px around to the CMS
* enable the CMS Tool to toggle the page tree in BlackCat installations
* process the finishing update for installing new kitFramework releases

**0.40** - 2014-05-17

* changed check for SMTP to compensate a problem with LEPTON CMS settings

**0.39** - 2014-04-28

* solved problem with exceeded download limit at Github

**0.37** - 2014-02-03

* added `framework_info.php` 

**0.36** - 2013-12-17 

* changed the backend look and feel to responsive Bootstrap 3 

**0.35** - 2013-12-01

* extended `precheck.php` with check for MySQL version `>= 5.0.3`

**0.34** - 2013-11-25

* extended `precheck.php` with check for MySQL InnoDB support

**0.33** - 2013-10-25

* improved CMS type and version detecting
* additional setup information

**0.32** - 2013-10-11

* bugfix: forgot xcopy() command ... 

**0.31** - 2013-10-11

* bugfix: wrong namespace for output filter at BlackCat CMS
* changed: moved the handling of the kitFramework Search Function to BASIC

**0.30** - 2013-10-09

* bugfix: the precheck enabled a installation at PHP 5.3.2 but the kitFramework really need PHP 5.3.3 at minimum.

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