=== Redirection Romeo ===
Contributors: harsh98trivedi
Donate link: https://harsh98trivedi.github.io/links
Tags: redirection, redirect, url manager, seo, link management, 301, 302, 308
Requires at least: 5.6
Tested up to: 6.9
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A modern, lightweight redirect manager. Redirect slugs to external URLs or internal posts with style, featuring a beautiful UI and 308 support.

== Description ==

**Redirection Romeo** is the modern way to manage your WordPress redirects. Say goodbye to clunky, outdated interfaces. This plugin offers a sleek, card-based dashboard that makes managing your links a joy.

Designed with performance and aesthetics in mind, Redirection Romeo supports not just standard 301 and 302 redirects, but also the modern **308 Permanent Redirect**, ensuring your SEO is future-proof.

= Key Features =

* **Modern UI/UX** — A beautiful, React-inspired interface using native JavaScript (no heavy libraries).
* **Visual Card Layout** — Manage redirects using clear, interactive cards instead of boring tables.
* **308 Support** — Full support for 308 Permanent Redirects (Preserve Method), alongside 301, 302, and 307.
* **Instant Search** — Real-time searching of your redirects by slug or target URL.
* **Internal Linking** — Intelligent autocomplete search to easily redirect to your internal Posts and Pages.
* **Hit Counting** — Track how many times your redirects are used.
* **Zero Bloat** — Lightweight and fast, keeping your site speed optimized.

= HTTP Status Codes Explained =

* **301 (Moved Permanently)** — Traditional permanent redirect for SEO.
* **302 (Found)** — Temporary redirect, search engines keep original URL indexed.
* **307 (Temporary Redirect)** — Temporary redirect that preserves the HTTP method.
* **308 (Permanent Redirect)** — Modern permanent redirect that preserves the HTTP method (recommended).

= Why Choose Redirection Romeo? =

* No bloated features you'll never use
* Clean, modern interface that's a pleasure to work with
* Built following WordPress coding standards
* Zero external dependencies for maximum performance
* Full compatibility with the latest WordPress version

== Installation ==

= From your WordPress dashboard =

1. Go to **Plugins → Add New**.
2. Search for **Redirection Romeo**.
3. Click **Install Now** and then **Activate**.

= From WordPress.org =

1. Download the plugin zip file.
2. Go to **Plugins → Add New → Upload Plugin**.
3. Upload the zip file and click **Install Now**.
4. Activate the plugin.

= Manual installation =

1. Upload the `Redirection-Romeo` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Click on "Redirection Romeo" in the admin sidebar.
4. Start creating redirects!

== Frequently Asked Questions ==

= What's the difference between 301 and 308 redirects? =

Both are permanent redirects, but 308 preserves the HTTP method (GET, POST, etc.) while 301 may change POST to GET. For most use cases, 308 is the modern recommended choice.

= Will this affect my site's performance? =

No! Redirection Romeo is built with performance in mind. It uses native JavaScript with zero heavy dependencies and optimized database queries.

= Can I redirect to internal posts? =

Yes! The plugin includes an intelligent autocomplete feature that lets you search and select any post or page on your site as a redirect target.

= Is this plugin compatible with caching plugins? =

Yes, Redirection Romeo works great alongside popular caching plugins. Redirects are processed at the PHP level before caching kicks in.

= How do I track redirect usage? =

Each redirect card displays a hit counter showing how many times it has been used. This helps you identify your most popular links.

== Screenshots ==

1. **Dashboard** — The modern card-based redirect management interface.
2. **Creator Panel** — Easily create new redirects with internal post search.
3. **Search** — Instant filtering logic for finding your links.

== Changelog ==

= 1.0.0 =
* Initial release.
* Added support for 301, 302, 307, and 308 redirects.
* Introduced modern card-based UI.
* Real-time instant search functionality.
* Internal post/page autocomplete linking.
* Hit counting for redirect tracking.
* Full WordPress coding standards compliance.

== Upgrade Notice ==

= 1.0.0 =
Initial release of Redirection Romeo. Enjoy the modern redirect management experience!
