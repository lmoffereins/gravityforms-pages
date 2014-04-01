=== Gravity Forms Pages ===
Contributors: Offereins
Tags: gravity forms, pages, list, forms, single form
Requires at least: 3.8
Tested up to: 3.9
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

List and show your Gravity Forms forms on your site without shortcodes.

== Description ==

The plugin creates a page that lists all Gravity Forms forms on your site. Additionally each form gets a unique page to navigate to, so there's no need anymore to create single-purpose pages with form shortcodes.

Single form pages honor form settings like requiring user login and time restrictions.

Customize form visibility in form queries can be done through the `pre_get_posts` filter, or more specifically the `gf_pages_forms_request` filter.
Template markup is based on _s by Automattic. Replacement of template files can be done through the `gf_pages_get_template_part` filter.

== Installation ==

1. Place the 'gravityforms-pages' folder in your '/wp-content/plugins/' directory.
2. Activate Gravity Forms Pages.
3. Visit the /forms/ page to view the forms listing and view any single form.

== Changelog ==

= 1.0.0 =
* Initial release