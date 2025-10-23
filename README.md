=== Mesi Cache ===

Contributors: mesi

Tags: cache, performance, static, html, apache

Requires at least: 5.5

Tested up to: 6.8

Requires PHP: 7.4

Stable tag: 1.2.3

License: GPLv2 or later

License URI: https://www.gnu.org/licenses/gpl-2.0.html



Ultra-light static HTML caching system for WordPress. Generates static files served directly by Apache for maximum performance.



== Description ==

Mesi Cache converts your WordPress site into a static HTML cache served directly by Apache, dramatically reducing PHP-FPM load.



\* Generates static HTML files upon publishing or manual regeneration.

\* Optional full-site regeneration.

\* Direct Apache `.htaccess` integration for instant delivery.

\* Works with both root and subdirectory installations (/wp/).

\* No cron jobs, no external services — pure simplicity.



== Installation ==

1\. Upload the `mesi-cache` folder to `/wp-content/plugins/`.

2\. Activate the plugin through the "Plugins" menu in WordPress.

3\. Go to \*\*Settings → Mesi Cache\*\* to configure.

4\. Click \*\*Generate / Update MESI-Cache Block\*\* to enable Apache integration.



== Frequently Asked Questions ==



= Does it work with subfolder installations (like /wp/)? =

Yes. The plugin auto-detects your site path and writes the correct `.htaccess` rewrite block.



= Is it compatible with other cache plugins? =

You should disable other full-page cache plugins to avoid conflicts.



