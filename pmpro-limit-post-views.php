<?php
/*
Plugin Name: PMPro Limit Post Views
Plugin URI: http://www.paidmembershipspro.com/wp/pmpro-limit-post-views/
Description: Integrates with Paid Memberships Pro to limit the number of times non-members can view posts on your site.
Version: .1
Author: Stranger Studios
Author URI: http://www.strangerstudios.com
*/
/*
	The Plan
	- Track a cookie on the user's computer.
	- Only track on pages the user doesn't have access to.
	- Allow up to 3 views without a membership level.
	- On 3rd view each month, redirect to a specific page to get them to sign up.
*/
define('PMPRO_LPV_LIMIT', 3);			//<-- how many posts can a user view per month

function pmpro_lpv_wp()
{	
	if(function_exists("pmpro_has_membership_access"))
	{
		/*
			If we're viewing a page that the user doesn't have access to...
			Could add extra checks here.
		*/
		if(!pmpro_has_membership_access())
		{
			$thismonth = date("n");
		
			//check for past views
			if(!empty($_COOKIE['pmpro_lpv_count']))
			{
				$parts = explode(",", $_COOKIE['pmpro_lpv_count']);				
				$month = $parts[1];
				if($month == $thismonth)
					$count = intval($parts[0])+1;	//same month as other views
				else
				{
					$count = 1;						//new month
					$month = $thismonth;
				}				
			}
			else
			{
				//new user
				$count = 1;
				$month = $thismonth;
			}
				
			//if count is above limit, redirect, otherwise update cookie
			if($count > PMPRO_LPV_LIMIT)
			{
				wp_redirect(pmpro_url("levels"));	//here is where you can change which page is redirected to
				exit;
			}
			else
			{
				//give them access and track the view
				add_filter("pmpro_has_membership_access_filter", "__return_true");
				setcookie("pmpro_lpv_count", $count . "," . $month, time()+3600*24*31, "/");			
			}
		}				
	}
}
add_action("wp", "pmpro_lpv_wp");

