# Gravity Forms Pages #

List and display Gravity Forms forms without shortcodes

## Description ##

> This WordPress plugin requires at least [WordPress](https://wordpress.org) 4.4 and [Gravity Forms](https://gravityforms.com) 2.0.

Do you find yourself regularly creating pages just to paste in a Gravity Forms shortcode and hit publish? Having too many pages in your site installation just to present a different form in each one of them? This plugin removes that trouble at once by generating urls for your forms as well as a form archive to browse through your forms.

A form archive page is generated which lists the available forms on your site. Additionally each form gets a unique url to navigate to, where the form is presented as if it were its own page. There's no need for shortcodes anymore when you just want to present a form in its own right.

Availability of a single form page honors form settings like inactive status, required user login and time schedule restrictions.

When desired, you can disable the form archive listing in the plugin's settings. Customize plugin settings in the "Pages" tab in Gravity Forms's Settings admin page.

Developers can modify the availability of a form through the `gf_pages_hide_form` filter.

### Theme Compatibility ###

The templates used for displaying both the form archive and single forms can be overridden in your theme. The plugin's theme-compat logic is similar to the ones featured in plugins like bbPress and BuddyPress. Use the following file names in your theme's `gravityforms` template folder:

* `archive-gf-pages-form.php` for the form archive page
* `single-gf-pages-form.php` for a single form page

## Installation ##

If you download Gravity Forms Pages manually, make sure it is uploaded to "/wp-content/plugins/gravityforms-pages/".

Activate Gravity Forms Pages in the "Plugins" admin panel using the "Activate" link. Plugin settings are located in the "Pages" tab in Gravity Forms's Settings admin page.

## Updates ##

This plugin is not hosted in the official WordPress repository. Instead, updating is supported through use of the [GitHub Updater](https://github.com/afragen/github-updater/) plugin by @afragen and friends.

## Contributing ##

You can contribute to the development of this plugin by [opening a new issue](https://github.com/lmoffereins/gravityforms-pages/issues/) to report a bug or request a feature in the plugin's GitHub repository.
