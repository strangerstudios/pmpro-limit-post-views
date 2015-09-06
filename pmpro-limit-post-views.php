<?php
/*
Plugin Name: Paid Memberships Pro - Limit Post Views Add On
Plugin URI: http://www.paidmembershipspro.com/wp/pmpro-limit-post-views/
Description: Integrates with Paid Memberships Pro to limit the number of times non-members can view posts on your site.
Version: .2
Author: Stranger Studios
Author URI: http://www.strangerstudios.com
*/
/*
	The Plan
	- Track a cookie on the user's computer.
	- Only track on pages the user doesn't have access to.
	- Allow up to 4 views without a membership level.
	- On 4th view each month, redirect to a specific page to get them to sign up.
*/
define('PMPRO_LPV_LIMIT', 3);			//<-- how many posts can a user view per month
define('PMPRO_LPV_USE_JAVASCRIPT', false);

//php limit (deactivate JS version below if you use this)
add_action("wp", "pmpro_lpv_wp");
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
			//ignore non-posts
			global $post;			
			if($post->post_type != "post")
				return;
			
			//if we're using javascript, just give them access and let JS redirect them
			if(PMPRO_LPV_USE_JAVASCRIPT)
			{			
				wp_enqueue_script('wp-utils', includes_url('/js/utils.js'));
				add_action("wp_footer", "pmpro_lpv_wp_footer");
				add_filter("pmpro_has_membership_access_filter", "__return_true");
				return;
			}
			
			//PHP is going to handle cookie check and redirect
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
				wp_redirect(home_url("paywall"));	//here is where you can change which page is redirected to
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

/*
	javascript limit (hooks for these are above)
	this is only loaded on pages that are locked for members
*/
function pmpro_lpv_wp_footer()
{	
	?>
	<script>		
		//vars
		var pmpro_lpv_count;		//stores cookie
		var parts;					//cookie convert to array of 2 parts
		var count;					//part 0 is the view count
		var month;					//part 1 is the month
		
		//what is the current month?
		var d = new Date();
		var thismonth = d.getMonth();
		
		//get cookie
		pmpro_lpv_count = wpCookies.get('pmpro_lpv_count');
		
		if(pmpro_lpv_count)
		{
			//get values from cookie
			parts = pmpro_lpv_count.split(',');
			month = parts[1];
			if(month == thismonth)
				count = parseInt(parts[0])+1;	//same month as other views
			else
			{
				count = 1;						//new month
				month = thismonth;
			}	
		}
		else
		{
			//defaults
			count = 1;
			month = thismonth;
		}				
		
		//if count is above limit, redirect, otherwise update cookie
		if(count > <?php echo intval(PMPRO_LPV_LIMIT); ?>)
		{
			window.location.replace('<?php echo home_url("paywall");?>');				
		}
		else
		{
			//track the view
			wpCookies.set('pmpro_lpv_count', String(count) + ',' + String(month), 3600*24*60, '/');				
		}
	</script>
	<?php
}



/*
Function to add links to the plugin row meta
*/
function pmpro_lpv_plugin_row_meta($links, $file) {
	if(strpos($file, 'pmpro-limit-post-views.php') !== false)
	{
		$new_links = array(
			'<a href="' . esc_url('http://www.paidmembershipspro.com/add-ons/plugins-on-github/pmpro-limit-post-views/')  . '" title="' . esc_attr( __( 'View Documentation', 'pmpro' ) ) . '">' . __( 'Docs', 'pmpro' ) . '</a>',
			'<a href="' . esc_url('http://paidmembershipspro.com/support/') . '" title="' . esc_attr( __( 'Visit Customer Support Forum', 'pmpro' ) ) . '">' . __( 'Support', 'pmpro' ) . '</a>',
		);
		$links = array_merge($links, $new_links);
	}
	return $links;
}
add_filter('plugin_row_meta', 'pmpro_lpv_plugin_row_meta', 10, 2);