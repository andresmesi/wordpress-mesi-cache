Mesi Cache

Contributors: mesi
Tags: cache, performance, static, html, apache
Requires at least: 5.5
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.2.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Ultra-light static HTML caching system for WordPress.
Generates static files served directly by Apache for maximum performance.

Description
-----------
Mesi Cache converts your WordPress site into a static HTML cache served directly by Apache.
When a post or page is published, the plugin automatically generates a static version of that content and updates the main index cache,
ensuring your visitors receive pre-rendered pages without invoking PHP or MySQL.

Key Features
------------
- Caches front page, posts, and pages.
- Automatic regeneration when publishing or updating content.
- Direct Apache delivery via optimized .htaccess rules.
- Full-site regeneration available from the admin panel.
- Works in subdirectory installs (/wp/, /blog/, etc.).
- No cron jobs or external services, pure native WordPress.

How It Works
------------
- When you publish a post or page, a static file is generated in /cache/.../index.html.
- Apache serves that file directly, PHP and MySQL are completely bypassed.
- Only uncached pages (admin, REST, AJAX) are handled dynamically.

To verify that Apache is serving cached content:

curl -I https://yourdomain.com/

If the cache is active, the response will come without an X-Powered-By: PHP header,
meaning the file was served directly by Apache.

If you do not have curl, you can test it another way:
temporarily change your database credentials in wp-config.php.
The site will still load from static HTML if caching is working.

Installation
------------
1. Upload the mesi-cache folder to /wp-content/plugins/.
2. Activate the plugin through Plugins → Installed Plugins.
3. Go to Settings → Mesi Cache.
4. Click Generate / Update MESI-Cache Block to insert the optimized .htaccess.

If you encounter issues:
- Restore your original .htaccess (WordPress default).
- Delete the modified .htaccess.
- Regenerate it again from the Mesi Cache settings page.

Frequently Asked Questions
---------------------------
Does it work with subfolder installations (like /wp/)?
Yes. The plugin automatically detects your subpath and writes a correct rewrite block.

What happens when I publish a new post or page?
Mesi Cache generates the cache file for that specific post or page and rebuilds the main index cache automatically.

How can I confirm Apache is serving from cache?
Use:
curl -I https://yourdomain.com/post-name/
If you do not see X-Powered-By: PHP, the HTML file came straight from Apache.

What if the cache fails to load or the site breaks?
Restore the default WordPress .htaccess, delete it, and regenerate a fresh one from Settings → Mesi Cache.

Changelog
---------
1.2.3
- Improved .htaccess generation and ordering.
- Fixed page cache generation (page_id vs post_id).
- Added automatic regeneration of the main index when publishing new content.
- Enhanced exclusion rules for /wp-json/, admin-ajax.php, login, feeds, and previews.

License
-------
This plugin is free software; you can redistribute it and/or modify it under the terms of the GPLv2 or later.
