=== Paid Memberships Pro - Limit Post Views Add On ===
Contributors: strangerstudios
Tags: paid memberships pro, pmpro, nytimes, new york times, post limits, limit, posts
Requires at least: 4.0
Tested up to: 4.5.3
Stable tag: .4

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
= .4 =
* BUG: Fixed issue where the addon would crash if PMPro was not activated.
* ENHANCEMENt: Now tracking views per level for cases where users upgrade their level during the middle of the month/etc.
* ENHANCEMENT: Moved the redirect code into a pmpro_lpv_redirect() function.
* ENHANCEMENT: Added a pmprolpv_has_membership_access filter that can be used to override the behavior of the code that redirects users away from content.

= .3 =
* BUG: Fixed issue where non-post pages and views (e.g. archive pages) were being tracked as page views. (Thanks, Squarelines)
* ENHANCEMENT: Added ability to change post view limit intervals to hour, day, week, or month.
* ENHANCEMENT: Added settings page.

= .2 =
* Added JavaScript option. Set at the top of the main plugin file.

= .1 =
* Initial version.
