kWpMarkdown
===========

This is a very simple post syntax replacer I wrote to replace the heavy WYSIWYG editor
in Wordpress.

Features/Limitations:
  
 * Uses [`erusev/parsedown`](https://github.com/erusev/parsedown) as Markdown parser.
 * Adds syntax hilighting via [highlight.js](https://highlightjs.org/).
 * Disables a bunch of boat filters that wordpress applies to post body and title
   which usually messes up code syntax. Sure, wordpress has a `[code]` tag but it's
   just not the same.
 * This plugin is offered as is and probably has a very limited scope of application.
   It's really aimed at developers who like their code to have a very small footprint.
