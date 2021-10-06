# Cookie banner for Craft CMS 3.x

This is an internal Statik.be plugin for use with our Tag Manager tracking solution. 


## Requirements

This plugin requires Craft CMS 3.0.0 or later.

## Installation

To install the plugin, follow these instructions:

        cd /path/to/project
        composer require statikbe/craft-cookie-banner
        ./craft  plugin/install cookie-banner

## Usage

The plugin uses a Twig function to render the initial popup and the cookie settings modal.
    
        {{ craft.cookieBanner.render() }}

You can also pass an array of options:
- modal: path to the template for the modal
- banner: path to the template for the banner
- overlay: HTML string with styling that will you use as overlay behind the banner
- supportIE: wether or not the Javascript used should be compiled for ES6 or ES5 (defaults to false so using ES6)

For example:

         {{ craft.cookieBanner.render({
                modal: '_site/_snippet/_cookie/_modal',
                banner: '_site/_snippet/_cookie/_banner',
                overlay: '<div class="fixed inset-0 hidden bg-black opacity-80 z-99" id="cookiebanner-overlay"></div>'
                supportIE: true
         }) }}

It's adviced to not overwrite the templates just for styling, only for more fundamental and structural changes.



