=== Wedding Portfolio Manager ===
Contributors: yourname
Tags: portfolio, wedding, photography, video, gallery, gutenberg
Requires at least: 5.0
Tested up to: 6.7
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A premium wedding portfolio manager for photographers and videographers to showcase wedding stories, films, and galleries.

== Description ==

Wedding Portfolio Manager allows you to create stunning portfolio pages for weddings. It includes:

* Custom post type for wedding portfolios
* Support for cinematic videos, vertical shorts, and photo galleries
* Dynamic video repeater with custom headings
* Gutenberg blocks for easy insertion
* A powerful React‑driven frontend
* Responsive grid with load‑more and filters

== Installation ==

1. Upload the `wedding-portfolio-manager` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Use the 'Wedding Stories' menu to add and manage portfolios.
4. Insert the `[wedding_portfolio]` shortcode or use the Gutenberg blocks.

== Frequently Asked Questions ==

= How do I display portfolios on a page? =

Use the shortcode `[wedding_portfolio]` or insert one of the Gutenberg blocks (Cinematic Videos, Vertical Shorts, Photo Galleries, Portfolio Grid).

= Can I add multiple videos? =

Yes! In the portfolio editor, you can add as many widescreen or vertical videos as you like, each with a custom heading and optional thumbnail.

= Can I filter by video type? =

Yes, use the `media_type` attribute in the shortcode: `[wedding_portfolio media_type="video"]` shows only portfolios with widescreen videos, and `media_type="vertical"` shows only those with vertical shorts.

== Changelog ==

= 1.0.0 =
* Initial release.
* Custom post type, taxonomies.
* Dynamic video repeater.
* Gutenberg blocks.
* React frontend with lightbox and load‑more.