# opencart-trs-sitemap

Timeout Resistant, Static Sitemap generator for OpenCart

## Editions

* [OpenCart 3.x](https://github.com/doctorOC/opencart-trs-sitemap/tree/opencart-3.x)
  * [ocStore 3.x](https://github.com/doctorOC/opencart-trs-sitemap/tree/ocstore-3.x)
* [OpenCart 2.x](https://github.com/doctorOC/opencart-trs-sitemap/tree/opencart-2.x)
  * [ocStore 2.x](https://github.com/doctorOC/opencart-trs-sitemap/tree/ocstore-2.x)

## Install

1. Copy files into the web root directory;
2. Go to extension/feed section, find TRS Sitemap Feed and install it;
3. Enable extension, provide Items per file limit (max 50k per file) and Generation time in hours, depend on your pages quantity. More time for generation - less load on the server;
4. Setup cron settings as provided in Crontab Task example. Script execution should be 1 time per minute.
5. Make sure, sitemap URL changed to `/sitemap.xml` in the `robots.txt` file
6. Remove or comment following line in the `.htaccess` file: `# RewriteRule ^sitemap.xml$ index.php?route=feed/google_sitemap [L]`

## License

Attribution-NonCommercial-ShareAlike 4.0 International License (CC BY-NC-SA 4.0)

It is also available through the world-wide-web at this URL:\
https://creativecommons.org/licenses/by-nc-sa/4.0/legalcode
