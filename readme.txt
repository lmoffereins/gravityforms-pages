=== Gravity Forms Pages ===
Contributors: Offereins
Tags: gravity forms, pages, list, forms, single form
Requires at least: 4.4, GF 2.0
Tested up to: 5.4.2, GF 2.4.17
Stable tag: 1.0.2
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

List and display Gravity Forms forms without shortcodes.

== Description ==

Do you find yourself regularly creating pages just to paste in a Gravity Forms shortcode and hit publish? Having too many pages in your site installation just to present a different form in each one of them? This plugin removes that trouble at once by generating urls for your forms as well as a form archive to browse through your forms.

A form archive page is generated which lists the available forms on your site. Additionally each form gets a unique url to navigate to, where the form is presented as if it were its own page. There's no need for shortcodes anymore when you just want to present a form in its own right.

Availability of forms as pages can be set globally and on a per-form basis. When made available, form settings like inactive status, required user login and time schedule restrictions are respected before the form is displayed.

When desired, you can disable the form archive listing in the plugin's settings. Customize plugin settings in the "Pages" tab in Gravity Forms's Settings admin page.

Developers can modify the availability of a form through the `gf_pages_hide_form` filter.

=== Theme Compatibility ===

The templates used for displaying both the form archive and single forms can be overridden in your theme. The plugin's theme-compat logic is similar to the ones featured in plugins like bbPress and BuddyPress. Use the following file names in your theme's `gravityforms` template folder:

* `archive-gf-pages-form.php` for the form archive page
* `single-gf-pages-form.php` for a single form page

== Installation ==

If you download Gravity Forms Pages manually, make sure it is uploaded to "/wp-content/plugins/gravityforms-pages/".

Activate Gravity Forms Pages in the "Plugins" admin panel using the "Activate" link.

This plugin is not hosted in the official WordPress repository. Instead, updating is supported through use of the [GitHub Updater](https://github.com/afragen/github-updater/) plugin by @afragen and friends.

== Changelog ==

= 1.0.3 =
* Fixed compatability with GF 2.5
* Added support for the GravityForms Submit Once plugin. Forms that are marked submit-once will be hidden for users who have already submitted an entry.

= 1.0.2 =
* Fixed compatability issues with GF up to version 2.4.17

= 1.0.1 =
* Added View Form admin-bar link when editing a form

= 1.0.0 =
* Initial release
