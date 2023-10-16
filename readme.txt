=== Paid Memberships Pro - Limit Post Views Add On ===
Contributors: strangerstudios
Tags: paid memberships pro, pmpro, nytimes, new york times, post limits, limit, posts
Requires at least: 4.0
Tested up to: 6.3
Stable tag: 0.6.1

Integrates with Paid Memberships Pro to limit the number of times non-members can view posts on your site.

== Description ==

Inspired by sites like the New York Times, which limits users to 5-10 monthly article views before redirecting users to a paywall. The plugin sets a cookie for each visitor to track their views per month. This means that (just like the NYTimes sites) it is incredibly easy to circumvent this protection by using a different computer, browsers, or incognito mode, etc. The idea is not to completely block users from your site, but to gently nudge returning readers to sign up for an account.

== Installation ==

1. Make sure you have the Paid Memberships Pro plugin installed and activated.
1. Upload the `pmpro-limit-post-views` directory to the `/wp-content/plugins/` directory of your site.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Configure settings on the Limit Post Views settings page.
1. Make sure your PMPro levels page mentions the post viewing limit so users know why they are being redirected.

== Frequently Asked Questions ==

= I found a bug in the plugin. =

Please post it in the GitHub issue tracker here: https://github.com/strangerstudios/pmpro-limit-post-views/issues

= I need help installing, configuring, or customizing the plugin. =

Please visit our premium support site at http://www.paidmembershipspro.com for more documentation and our support forums.

== Changelog ==
= 0.6.1 - 2023-10-13 =
* BUG FIX/ENHANCEMENT: Marked plugin as incompatible with Multiple Memberships Per User for the PMPro v3.0 update. #52 (@dparker1005)
* BUG FIX: Fixed timezone issue where cookie expiration dates could be set in the past. (@ideadude)
* BUG FIX: Fixed errors in PHP 8+ when trying to access array indexes that do not exist. #49 (@JarrydLong)

= .6 =
* BUG FIX: Fixed issue with PMPro 2.0 menus.
* ENHANCEMENT: Cleaned up the JavaScript code a bit.
* ENHANCEMENT: WordPress coding standards review.
* ENHANCEMENT: Adding a link to the plugin settings page in the plugin's action links.
* ENHANCEMENT: Improving settings page layout and documentation.

= .5 =
* BUG: Fixed issue where current user's level ID wasn't used properly when NOT using JavaScript and counting views per level.
* BUG: Fixed PHP warning when PMPRO_LPV_USE_JAVASCRIPT wasn't defined.

= .4 =
* BUG: Fixed issue where the addon would crash if PMPro was not activated.
* ENHANCEMENt: Now tracking views per level for cases where users upgrade their level during the middle of the month/etc.
* ENHANCEMENT: Moved the redirect code into a pmpro_lpv_redirect() function.
* ENHANCEMENT: Added a pmprolpv_has_membership_access filter that can be used to override the behavior of the code that redirects users away from content.
* ENHANCEMENT: Added a pmprolpv_post_types filter that can be used to tell the addon to allow and limit views on other post types. Defaults to just array('post').

= .3 =
* BUG: Fixed issue where non-post pages and views (e.g. archive pages) were being tracked as page views. (Thanks, Squarelines)
* ENHANCEMENT: Added ability to change post view limit intervals to hour, day, week, or month.
* ENHANCEMENT: Added settings page.

= .2 =
* Added JavaScript option. Set at the top of the main plugin file.

= .1 =
* Initial version.
