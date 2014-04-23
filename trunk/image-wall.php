<?php
/*
	Plugin Name: Image Wall
	Plugin URI: http://www.themodernnomad.com/image-wall-plugin/#utm_campaign=Image_Wall&utm_source=wordpress&utm_medium=website&utm_content=plugin_link
	Description: Browse posts/pages by their images, displayed randomly on an infinitely scrollable page. The images link back to the posts where they are attached.
	Version: 2.15
	Author: Gustav Andersson
	Author URI: http://www.themodernnomad.com/about/#utm_campaign=Image_Wall&utm_source=wordpress&utm_medium=website&utm_content=author_link
*/
/*  Copyright 2012  Gustav Andersson  (email : mail@themodernnomad.com)

    There are two parts of the license of the Image Wall. The first part is 
    the bit that I have written, which is everything in image-wall.css, image-wall.js and
    image-wall.php. Those items are copyrighted under the GPLv2 license described below.
        
    The included 'Infinite Scroll' code is licensed and protected as described 
    at http://www.infinite-scroll.com/.i. the  MIT licence.
    
    GPLv2:

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/************************************************************************/
/*			PLUGIN ACTIVATION				*/
/************************************************************************/
register_activation_hook(__FILE__,'tmn_iw_plugin_activation');
function tmn_iw_plugin_activation()
{
	// The below three clears are for users of older versions that may have older triggers around 
	wp_clear_scheduled_hook('tmn_iw_attachment_hash_regenerate');
	wp_clear_scheduled_hook('tmn_iw_regenerate_action');
	wp_clear_scheduled_hook('tmn_tmp_iw_regenerate_action');
	wp_clear_scheduled_hook('iw_attachment_hash_regenerate');
	wp_schedule_single_event(time(), 'iw_attachment_hash_regenerate');
	
	tmn_iw_set_default_variables();
}

function tmn_iw_set_default_variables()
{	
	// Set up our initial variables.
	add_option( "image_wall_regen", "tmn_iw_never" );
	add_option( "image_wall_regen_method", "hashing" );
	add_option( "image_wall_regen_salt", rand(1, 1000) ); 
} 


/************************************************************************/
/*			PLUGIN DE-ACTIVATION				*/
/************************************************************************/

register_deactivation_hook(__FILE__,'tmn_iw_plugin_deactivation');
function tmn_iw_plugin_deactivation()
{
	wp_clear_scheduled_hook('iw_attachment_hash_regenerate');
	delete_option( "image_wall_regen" );
	delete_option( "image_wall_regen_method" );
	delete_option( "image_wall_regen_salt" );	
	global $wpdb;
	$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = 'tmn-iw-hash'");
}


/************************************************************************/
/*			ATTACHMENT HASHING				*/
/************************************************************************/

add_action('iw_attachment_hash_regenerate','tmn_iw_attachment_hash_regenerate');
add_action('add_attachment','tmn_iw_attachment_hash_regenerate');


/* Regenerates the Image Wall hash, salted by the time of the request to simulate randomness. 
   If the argument is a valid attachment_id, only that attachment is updated. 
   If it is empty, all attachments are regenerated. Else, an error is thrown. 
   Returns true on success, false otherwise, or maybe a cheerful error.
*/
function tmn_iw_attachment_hash_regenerate($attachment_id) {
	if( !isset($attachment_id) ) {

		$myposts = get_posts( array(
			'post_type' 	=> 'attachment',
			'nopaging' 	=> true,
			'post_status' 	=> 'all',
			'post_parent' 	=> null
		) );

		$tmn_time = time();
		foreach( $myposts as $mypost ) {
			update_post_meta($mypost->ID, 'tmn-iw-hash', md5($mypost->ID + $tmn_time));
		}
		return true;
	} else {
		return update_post_meta($attachment_id, 'tmn-iw-hash', md5($attachment_id));
	}
}

/************************************************************************/
/*		REPEATABLE RANDOM ORDER FILTER 				*/
/************************************************************************/

// Retrieve consistent random set of posts with pagination
function tmn_iw_posts_query_override($query) {
	global $tmn_iw_posts_query;

	if ($tmn_iw_posts_query && strpos($query, 'ORDER BY RAND()') !== false) {
		$query = str_replace('ORDER BY RAND()',$tmn_iw_posts_query,$query);
	}
	return $query;
}
add_filter('query','tmn_iw_posts_query_override');


/************************************************************************/
/*			    ADMIN MENU  				*/
/************************************************************************/

// ------------------------------------------------------------------
// Add the menu page.
// ------------------------------------------------------------------

add_action( 'admin_menu', 'tmn_iw_plugin_menu' );
function tmn_iw_plugin_menu() {
	add_options_page( 'Image Wall Options', 'Image Wall', 'manage_options', 'image-wall-settings', 'image_wall_options');
}
function image_wall_options() {
	if ( !current_user_can( 'manage_options' ) ) { 	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );}
	tmn_iw_set_default_variables();
?>


	<div class="wrap" style="max-width: 730px;">
		<style>
			p.success {
				border: 1px solid green;
				background: palegreen;
				padding: 3px;
				font-weight: bold;
			}
			
			p.error {
				border: 1px solid red;
				background: lightsalmon;
				padding: 3px;
				font-weight: bold;
			}
				
		</style>
		<div id="icon-options-general" class="icon32"></div>
		
		<h2>The Image Wall</h2> 

		<div id="donate" style="float: right;border: 1px solid black;width: 206px;margin: -6px 0 10px 20px;background: lightcyan;padding: 5px;text-align: justify;">
			<h3>Support This Plugin</h3>
			<p>Have you found this plugin useful? Please help support it's continued development with a donation.</p>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" style="text-align: center;">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="NU9BQUY8MTEAQ">
				<input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online.">
				<img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
			</form>
			<h3>Short on funds?</h3>
			<ul style="list-style: initial; padding-left: 18px;">
				<li><a href="http://wordpress.org/support/view/plugin-reviews/image-wall" target="_blank">Give the Image Wall a 5★ rating</a>.</li>
				<li>Link back to the <a href="http://www.themodernnomad.com/image-wall-plugin/" target="_blank">plugin page</a>.</li>
				<li>Give my blog, <a href="http://www.themodernnomad.com#utm_campaign=Image_Wall&utm_source=wordpress&utm_medium=website&utm_content=donate" target="_blank">The Modern Nomad</a>, a read. You might like it!</li>
			</ul>
		</div>
		
		<h3>What is the Image Wall for?</h3>
		
		<p>The Image Wall allows visitors to browse posts or pages by their images, displayed randomly on an infinitely scrollable Image Wall. The images link back to the post or page on which they are attached. It is an engaging way to keep your visitors' fickle attention and perhaps bring some traffic to your older posts.</p>
		<p><a href="http://www.themodernnomad.com/image-wall/#utm_campaign=Image_Wall&utm_source=wordpress&utm_medium=website&utm_content=settings">Please visit the plugin author's own Image Wall for a demo.</a></p>
		
		<h3>How to use the  Image Wall</h3>
		
		<ol>
			<li>Select or create a page where you want to place the image wall.</li>
			<li>On the selected page, enter the shortcode <code>[image_wall]</code>. Having text before the shortcode (like an introduction) is fine, but text underneath the shortcode will be pushed away by the image wall, so probably won't be seen.</li>
			<li>You can have several pages with their own <code>[image_wall]</code> shortcode with different settings.</li>
		</ol>

		<p>The [image_wall] shortcode comes with a number of options to customize its behavhiour. The available options are shown below with the corresponding default settings. For the full documentation of how to use these settings, please see the <a href="http://www.themodernnomad.com/image-wall-plugin/#utm_campaign=Image_Wall&utm_source=wordpress&utm_medium=website&utm_content=settings">Image Wall Plugin Page</a>.</p>		

		<p><code>[image_wall image_sizes='thumbnail, medium' column_width='' batch_size='50' buffer_pixels='2000' support_author='false' move_to_end='false' column_proportion_restrictions='2.0' open_links_in_new_window='true' include_categories='' exclude_categories='' include_tags='' exclude_tags='' include_pages='true' background_color='black' gutter_pixels='8' corner_radius='8']</code></p>
		
		<h3>Image Order Regeneration Schedule</h3>
		
		<p>The ordering of the images is randomized when you activate the plugin. New images are slotted into the order at random as you add them to your media library.</p>
		
		<p>By default, this is done once at plugin activation and the same order is then maintained. If you wish to schedule a new order to be generated at a given interval, you can choose to do so below.</p>
	
		<p>If you have a caching plugin, some images may never appear and some may be duplicated if your caching plugin caches and maintains old batches of images. Either accept this, disable caching for the Image Wall page or clear out your cache after generating a new order.</p>
		
		<p>When you click the button, a new order will be generated immediately and the schedule updated. If you want to do a once-off order generation without setting a schedule, set or leave the radio button at 'Never' and click the button.</p>

		<?php 
				
		if( isset($_POST[ "image_wall_regen" ]) ) {
			if(	$_POST[ "image_wall_regen" ] == "tmn_iw_never"   || 
				$_POST[ "image_wall_regen" ] == "tmn_iw_daily"   ||
				$_POST[ "image_wall_regen" ] == "tmn_iw_weekly"  ||
				$_POST[ "image_wall_regen" ] == "tmn_iw_monthly" )
			{
				update_option( "image_wall_regen", $_POST[ "image_wall_regen" ] );
				
				if(get_option( "image_wall_regen_method" ) == "hashing"){
					wp_clear_scheduled_hook('iw_attachment_hash_regenerate');

					if($_POST[ "image_wall_regen" ] == "tmn_iw_never") {
						wp_schedule_single_event(time(), 'iw_attachment_hash_regenerate');
						echo '<p class="success">A one-off image order hash generation has begun.</p>';
					} else {
						wp_schedule_event( time(), $_POST[ "image_wall_regen" ], 'iw_attachment_hash_regenerate');
						echo '<p class="success">An image order hash generation has begun and the schedule has been updated.</p>';
					}
				} elseif(get_option( "image_wall_regen_method" ) == "calculation"){ 
					update_option( "image_wall_regen_salt", rand(1, 1000) ); 
					echo '<p class="success">The image re-generation schedule (using the calculation method) has been updated.</p>';
				}								
			} else {
				echo '<p class="error">The given schedule of [' . $_POST[ "image_wall_regen" ] . '] is not a valid schedule!</p>';
			}
		}
		
		$tmn_iw_schedule = get_option("image_wall_regen" );
		?>
		
		
		<form method="post">
			<input type="radio" name="image_wall_regen" value="tmn_iw_never"   <?php checked( $tmn_iw_schedule, 'tmn_iw_never', true ); ?>> Never
			<input type="radio" name="image_wall_regen" value="tmn_iw_daily"   <?php checked( $tmn_iw_schedule, 'tmn_iw_daily',   true ); ?> style="margin-left: 5px"> Daily
			<input type="radio" name="image_wall_regen" value="tmn_iw_weekly"  <?php checked( $tmn_iw_schedule, 'tmn_iw_weekly',  true ); ?> style="margin-left: 5px"> Weekly
			<input type="radio" name="image_wall_regen" value="tmn_iw_monthly" <?php checked( $tmn_iw_schedule, 'tmn_iw_monthly', true ); ?> style="margin-left: 5px"> Monthly <br/>
			<input type='submit' value='Generate New Order And Update Schedule' class='button-primary'  style="margin-top: 5px"/>
		</form>
		
		<h3>Image Order Generator Method</h3>
		
		<p>By default, the Image Wall uses a Hashing method to create and save the randomized image ordering. This is resource heavy at the time of hashing, but then it is fast. This is the preferred method, but sometimes this hashing method stalls. If the Image Wall shows an error telling you that it can find no images to show, and waiting a few hours doesn't help, then try the Calculation method.</p>
		
		<p>The Calculation method doesn't do any up-front order hashing but calculates a random order when the Image Wall is accesses. Slightly slower than the Hashing method, but it bypasses the Hashing which can, on some WordPress installation, stall and break. Use this method if the "Can't find any images" error message doesn't go away a few hours after plugin activation.</p>

		<?php 
				
		if( isset($_POST[ "image_wall_regen_method" ]) ) {
			$tmn_iw_old_method = get_option("image_wall_regen_method" );
			if( $_POST[ "image_wall_regen_method" ] == "hashing" && $tmn_iw_old_method != $_POST[ "image_wall_regen_method" ]) {
				update_option( "image_wall_regen_method", "hashing" );
				
				wp_clear_scheduled_hook('iw_attachment_hash_regenerate');
				$tmn_iw_schedule = get_option("image_wall_regen" );

				if($tmn_iw_schedule == "tmn_iw_never") {
					wp_schedule_single_event(time(), 'iw_attachment_hash_regenerate');
					echo '<p class="success">Hashing method activated and a one-off image order hash generation has begun.</p>';
				} else {
					wp_schedule_event( time(), $tmn_iw_schedule, 'iw_attachment_hash_regenerate');
					echo '<p class="success">Hashing method activated and an image order hash generation has begun.</p>';
				}
			} elseif( $_POST[ "image_wall_regen_method" ] == "calculation"  && $tmn_iw_old_method != $_POST[ "image_wall_regen_method" ]) {
				update_option( "image_wall_regen_method", "calculation" );
				echo '<p class="success">Calculation method activated.</p>';
			} else {
				echo '<p class="error">The existing image randomization method has been kept active.</p>';
			}
		}
		
		$tmn_iw_method = get_option( "image_wall_regen_method" );
		?>
		
		<form method="post">
			<input type="radio" name="image_wall_regen_method" value="hashing"   	<?php checked( $tmn_iw_method, 'hashing',     true ); ?>> Hashing
			<input type="radio" name="image_wall_regen_method" value="calculation"	<?php checked( $tmn_iw_method, 'calculation', true ); ?> style="margin-left: 5px"> Calculation<br />
			<input type='submit' value='Update the Image Randomization Method' class='button-primary'  style="margin-top: 5px"/>
		</form>
		
		<h3>What to do if the image wall doesn't function</h3>
		
		<p>First, see the <a href="http://www.themodernnomad.com/image-wall-plugin/#utm_campaign=Image_Wall&utm_source=wordpress&utm_medium=website&utm_content=settings">Image Wall Plugin Page</a> to see if your problem is already covered there. If not, please <a href="http://www.themodernnomad.com/contact/#utm_campaign=Image_Wall&utm_source=wordpress&utm_medium=website&utm_content=settings">contact me</a> and ask for help. Please don't leave a bad rating before you've given me a chance to fix the problem. Thank you.</p>
		
		<h3>About the plugin author</h3>
		
		<img src="<?php echo plugins_url( 'TMNlogo.png'  , __FILE__ ); ?>" width="144" height="144" alt="Live Free. The Modern Nomad" style="float: right; margin: 0 10px 10px 0">
		<p>Gustav Andersson is the author behind <a href="http://www.themodernnomad.com/#utm_campaign=Image_Wall&utm_source=wordpress&utm_medium=website&utm_content=settings">The Modern Nomad</a>, a site exploring nomadic lifestyles that frees people to live and work anywhere, anytime. He is a <a href="http://www.themodernnomad.com/2012/tango/#utm_campaign=Image_Wall&utm_source=wordpress&utm_medium=website&utm_content=settings">tango-dancing</a>, <a href="http://www.themodernnomad.com/2011/rodeo/#utm_campaign=Image_Wall&utm_source=wordpress&utm_medium=website&utm_content=settings">steer-wrestling</a> <a href="http://www.themodernnomad.com/2011/the-burning-man-guide/#utm_campaign=Image_Wall&utm_source=wordpress&utm_medium=website&utm_content=settings">burner</a> who strives to inspire people to actively and bravely choose how to live their lives.</p>
		<p>If you use the Image Wall plugin, please show your appreciation by visiting <a href="http://www.themodernnomad.com/#utm_campaign=Image_Wall&utm_source=wordpress&utm_medium=website&utm_content=settings">The Modern Nomad</a> and sharing it on your favourite social network. And don't forget to give the Image Wall a good rating!</p>
		<p>Many thanks!<br />Gustav Andersson<br />Your friendly nomad, surfing the luminiferous aether.</p>
		
	</div>
<?php }

 // ------------------------------------------------------------------
 // Add the Image Wall Image Order Regeneration Schedule options
 // ------------------------------------------------------------------
function my_add_intervals($schedules) {
	$schedules['tmn_iw_daily'] = array(
		'interval' => 86400,
		'display' => __('Once Daily')
	);
	$schedules['tmn_iw_weekly'] = array(
		'interval' => 604800,
		'display' => __('Once Weekly')
	);
	$schedules['tmn_iw_monthly'] = array(
		'interval' => 2635200,
		'display' => __('Once a month')
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'my_add_intervals'); 
  

/************************************************************************/
/*			ENQUEUE ON SHORTCODE USAGE			*/
/************************************************************************/

function tmn_iw_enqueue() {
	global $post;
	if (   preg_match('/(?<!\[)\[image_wall.*?\]/', $post->post_content) ) {
		wp_register_script ( 'imagesLoaded', plugins_url( 'imagesloaded.pkgd.min.js'  , __FILE__ ), array(), '3.1.4', true );
		wp_register_script ( 'infinitescroll', plugins_url( 'jquery.infinitescroll.js'  , __FILE__ ), array('imagesLoaded'), '2.0b2.120519', true );
		wp_enqueue_script  ('tmniwjs', plugins_url( 'image-wall.js'  , __FILE__ ), array('jquery', 'jquery-masonry', 'infinitescroll', 'imagesLoaded'), '2', true);
/*
		$translation_array = array( 
			'msgTextLoading' => __( "Congratulations, you've reached the end of the internet.", 'image-wall' ) ,
			'msgTextNextLoading' => __( 'Loading the next set of posts...', 'image-wall' ) ,
			'msgTextloading' => __("Loading ... ", "image-wall") ,
			'msgTextMorePictures' => __("Loading more pictures ... ", "image-wall") ,
			'finishedMsg' => __("You have reached the end of the internet!", "image-wall")
 
		);
		wp_localize_script( 'tmniwjs', 'tmniwjsi18n', $translation_array );
*/
		
		wp_enqueue_style ('tmniwcss', plugins_url( 'image-wall.css' , __FILE__ ), array(), '1');
		add_filter('body_class','tmn_iw_body_class');
	}
}    

function tmn_iw_body_class($classes) {
	$classes[] = 'image-wall';	
	return $classes;
}

add_action('wp_enqueue_scripts', 'tmn_iw_enqueue');


/************************************************************************/
/*			SHORTCODE	 				*/
/************************************************************************/

/* See http://www.themodernnomad.com/image-wall-plugin/ for example usage.*/
add_shortcode('image_wall', 'image_wall_sc');
function image_wall_sc($atts) {
	global $wp_version;

	if (version_compare($wp_version, '3.5') < 0) {
		$output = "<div id='tmn-image-wall-error'><h3>
		" . __("Uh oh! I've detected a problem setting up the Image Wall!", "image-wall") . "
		</h3><p>
		" . __("The Image Wall requires Wordpress version 3.5 or later. Please upgrade to the latest Wordpress version and try again.<br />Your current version is ", "image-wall") . $wp_version . "
		</p></div>\n";
		return $output;
	}
	
	global $wp_query;	
	
	extract( shortcode_atts( array(	
		'image_sizes' 			=> 'medium' , 
		'column_width' 			=> '', 
		'batch_size' 			=> '50',
		'buffer_pixels' 		=> '2000',
		'support_author' 		=> 'false',
		'move_to_end'			=> 'false', 
		'column_proportion_restrictions'=> '2.0',
		'open_links_in_new_window' 	=> 'true',
		'include_categories' 		=> '',
		'exclude_categories' 		=> '',
		'include_tags' 			=> '',
		'exclude_tags' 			=> '',
		'include_pages' 		=> 'true',
		'background_color'		=> 'black',
		'gutter_pixels'			=> '8',
		'corner_radius'			=> '8'

	), $atts ) );

	echo("<!-- Image Wall Arguments: image_sizes=$image_sizes ; column_width=$column_width ; batch_size=$batch_size ; buffer_pixels=$buffer_pixels ; support_author=$support_author ; move_to_end=$move_to_end ; column_proportion_restrictions=$column_proportion_restrictions ; open_links_in_new_window=$open_links_in_new_window ; include_categories=$include_categories ; exclude_categories=$exclude_categories ; include_tags=$include_tags ; exclude_tags=$exclude_tags ; include_pages=$include_pages ; background_color=$background_color ; gutter_pixels=$gutter_pixels ; corner_radius=$corner_radius -->");
	
	// Find, process and verify the image sizes //	
	$image_sizes = preg_split("/[\s,]+/",$image_sizes, NULL, PREG_SPLIT_NO_EMPTY);
	$erroneous_image_sizes = array_diff($image_sizes, get_intermediate_image_sizes());
	if(! empty($erroneous_image_sizes) ) {
		$output = "<div id='tmn-image-wall-error'><h3>
		" . __("Uh oh! I've detected a problem setting up the Image Wall!", "image-wall") . "
		</h3><p>
		" . __("The following image sizes are not registered in this wordpress instance. Please remove and try again.", "image-wall") . "
		</p><ul>";
		foreach ($erroneous_image_sizes as $erroneous_image_size) { $output .= "<li>".$erroneous_image_size."</li>\n"; }
		$output .= "</ul>\n</div>\n";
		return $output;
	}

	// This really should be available as a wordpress function!!!!
	global $_wp_additional_image_sizes;

	foreach ( $image_sizes as $s ) {
		$sizes[$s] = array( 'width' => '', 'height' => '', 'crop' => FALSE );
		if ( isset( $_wp_additional_image_sizes[$s]['width'] ) )
			$sizes[$s]['width'] = intval( $_wp_additional_image_sizes[$s]['width'] ); // For theme-added sizes
		else
			$sizes[$s]['width'] = get_option( "{$s}_size_w" ); // For default sizes set in options
		if ( isset( $_wp_additional_image_sizes[$s]['height'] ) )
			$sizes[$s]['height'] = intval( $_wp_additional_image_sizes[$s]['height'] ); // For theme-added sizes
		else
			$sizes[$s]['height'] = get_option( "{$s}_size_h" ); // For default sizes set in options
		if ( isset( $_wp_additional_image_sizes[$s]['crop'] ) )
			$sizes[$s]['crop'] = intval( $_wp_additional_image_sizes[$s]['crop'] ); // For theme-added sizes
		else
			$sizes[$s]['crop'] = get_option( "{$s}_crop" ); // For default sizes set in options
	}
	$image_sizes = $sizes;
	$sizes = null;

	// Find, process and verify the column_width //

	// Find the smallest image size width available
	
	$minimum_width = PHP_INT_MAX;
	foreach ( $image_sizes as $size_name => $size_details) {
		if($size_details['width'] > 0){
			$minimum_width = min($minimum_width, $size_details['width']);
		}
	}
	
	if(empty($column_width) ){
		if ($minimum_width == PHP_INT_MAX) {
			return "
	<div id='tmn-image-wall-error'><h3>
" . __("Uh oh! I've detected a problem setting up the Image Wall!", "image-wall") . "
	</h3><p>
" . __("None of the given image sizes have a defined width. You must manually enter the column_width in the shortcode as I can't guess it.", "image-wall") . "
	</p></div>\n";
		} else {
			$column_width = $minimum_width;
		}
	}
	
	if($column_width < 18) {
		return "<div id='tmn-image-wall-error'><h3>
" . __("Uh oh! I've detected a problem setting up the Image Wall!", "image-wall") . "
		</h3><p>
" . sprintf( __('The column width (%1$s) is either too small or not a number at all. I require an integer column width of minimum 18 px.', 'image-wall' ), $column_width) . "		</p>
	</div>\n" ;
	}
		
	// Find, process and verify the column proportion restrictions //
	$use_column_proportion_restrictions = $column_proportion_restrictions != 'none';
	if($use_column_proportion_restrictions){
		$column_proportion_restrictions = preg_split("/[\s,]+/","0,".$column_proportion_restrictions, NULL, PREG_SPLIT_NO_EMPTY);
		for($i = 0; $i < count($column_proportion_restrictions) ; $i ++ ) {
			if(! is_numeric($column_proportion_restrictions[$i])) {
				return "<div id='tmn-image-wall-error'><h3>
				" . __("Uh oh! I've detected a problem setting up the Image Wall!", "image-wall") . "
						</h3><p>
				" . sprintf( __('The column proportion restriction (%1$s) is not a number that can be converted to a float (decimal value). The column_proportion_restrictions argument must be a comma-separated list of numbers or the string "none".', 'image-wall' ), $column_proportion_restrictions[$i]) . "		</p>
				</div>\n" ;
			} else {
				$column_proportion_restrictions[$i] = floatval($column_proportion_restrictions[$i]);
			}
		}
	}
	
	// Find, process and verify the batch size //
	$batch_size = intval($batch_size);
	if($batch_size <= 0) {
		return "<div id='tmn-image-wall-error'><h3>
" . __("Uh oh! I've detected a problem setting up the Image Wall!", "image-wall") . "
		</h3><p>
" . __("I couldn't parse the given batch size to a positive integer.", "image-wall") . "
		</p></div>\n" ;
	}

	// Find, process and verify the move_to_end argument //
	if($move_to_end != "true" && $move_to_end != "false") {
		return "<div id='tmn-image-wall-error'><h3>
" . __("Uh oh! I've detected a problem setting up the Image Wall!", "image-wall") . "
		</h3><p>
" . __("I couldn't parse the given move_to_end argument. Valid values are [true/false].", "image-wall") . "
		</p></div>\n" ;
	}

	// Find, process and verify the batch size //
	$buffer_pixels = intval($buffer_pixels);
	if($buffer_pixels <= 0) {
		return "<div id='tmn-image-wall-error'><h3>
" . __("Uh oh! I've detected a problem setting up the Image Wall!", "image-wall") . "
		</h3><p>
" . __("I couldn't parse the given buffer pixels to a positive integer.", "image-wall") . "
		</p></div>";		
	}
	
	// Find, process and verify the open links in new windowe //
	if($open_links_in_new_window == 'false') {
		$target_string = '';
	} elseif($open_links_in_new_window == 'true') {
		$target_string = ' target="_blank" '; 
	} else {
		return "<div id='tmn-image-wall-error'><h3>
		" . __("Uh oh! I've detected a problem setting up the Image Wall!", "image-wall") . "
				</h3><p>
		" . __("I couldn't parse the given open_links_in_new_window argument. Valid values are [true/false].", "image-wall") . "
				</p></div>";		
	}


	// Unpackage the include/exclude arrays
	$include_categories = preg_split("/[\s,]+/",$include_categories, NULL, PREG_SPLIT_NO_EMPTY);
	$exclude_categories = preg_split("/[\s,]+/",$exclude_categories, NULL, PREG_SPLIT_NO_EMPTY);
	$include_tags = preg_split("/[\s,]+/",$include_tags, NULL, PREG_SPLIT_NO_EMPTY);
	$exclude_tags = preg_split("/[\s,]+/",$exclude_tags, NULL, PREG_SPLIT_NO_EMPTY);

	if($include_pages == 'true') {
		$include_pages = true;
	} else {
	 	$include_pages = false; 
	}
	
	// Find, process and verify the 'support_author' 
	if($support_author == 'hidden') {
		$support_author = true;
		$support_author_visibility = false;
	} elseif($support_author == 'true') {
		$support_author = true;
		$support_author_visibility = true;
	} elseif($support_author == 'false') {
		$support_author = false;
	} else {
		return "<div id='tmn-image-wall-error'><h3>" . __("Uh oh! I've detected a problem setting up the Image Wall!", "image-wall") . "</h3><p>" . __("I couldn't parse the given support_author argument. Valid values are [true/hidden/false].", "image-wall") . "</p></div>\n" ;
	}
	
	
	// Find, process and verify the gutter width //
	$gutter_pixels = intval($gutter_pixels);
	if($gutter_pixels < 0) {
		return "<div id='tmn-image-wall-error'><h3>" . __("Uh oh! I've detected a problem setting up the Image Wall!", "image-wall") . "</h3><p>" . __("I couldn't parse the given gutter pixel width to a positive integer.", "image-wall") . "</p></div>\n" ;
	}
	$gutter_pixels = $gutter_pixels + $gutter_pixels % 2; // Round the number up to closest even number.

	// Find, process and verify the gutter width //
	$corner_radius = intval($corner_radius);
	if($corner_radius < 0) {
		return "<div id='tmn-image-wall-error'><h3>" . __("Uh oh! I've detected a problem setting up the Image Wall!", "image-wall") . "</h3><p>" . __("I couldn't parse the given corner radius to a positive integer.", "image-wall") . "</p></div>\n" ;
	}

	// Grab and remember the page we are on, if paged.
	$tmn_page = isset($_GET["tmn_iw_page"]) ? $_GET["tmn_iw_page"] : '1' ;
	$image_wall_items = array();	
	$expect_more_posts = true;

	tmn_iw_set_default_variables();
	
	$iw_image_wall_regen_method = get_option( "image_wall_regen_method" );

	if($iw_image_wall_regen_method == "hashing") {
		// Done unpacking and verifying the arguments. Let's get some real coding done!
		// Create our new image query and run the loop
		$iw_query = new WP_Query( array(
			'post_type' 	 => 'attachment',
			'post_mime_type' =>'image',
			'posts_per_page' => $batch_size,
			'nopaging' 	 => false,
			'post_status' 	 => 'all',
			'meta_key' 	 => 'tmn-iw-hash',
			'orderby' 	 => 'meta_value',
			'paged' 	 => $tmn_page
		) );	
	} elseif ($iw_image_wall_regen_method == "calculation") {
			$iw_image_wall_regen_salt   = get_option( "image_wall_regen_salt" );	 
			$iw_image_wall_regen        = get_option( "image_wall_regen" );
			
			if($iw_image_wall_regen == "tmn_iw_never"){
				$seed = $iw_image_wall_regen_salt;
			} elseif ($iw_image_wall_regen == "tmn_iw_daily"){
				$seed = $iw_image_wall_regen_salt . date('Ymd');
			} elseif ($iw_image_wall_regen == "tmn_iw_weekly"){
				$seed = $iw_image_wall_regen_salt . date('YmW');
			} elseif ($iw_image_wall_regen == "tmn_iw_monthly"){
				$seed = $iw_image_wall_regen_salt . date('Ym');
			} else {
				return "<div id='tmn-image-wall-error'><h3>Uh oh! I've detected a problem setting up the Image Wall!</h3><p>This is serious. I've been told to generate the image wall using a order regeneration of '".print_r($iw_image_wall_regen)."', but I don't know what that is.  Please <a href='http://www.themodernnomad.com/contact/'>contact me</a> and let me know that you have a problem, and I will take a look at it!</p></div>";
			}			
			global $tmn_iw_posts_query;
			$tmn_iw_posts_query = " ORDER BY RAND($seed) "; // Turn on filter
			
			// Done unpacking and verifying the arguments. Let's get some real coding done!
			// Create our new image query and run the loop
			$iw_query = new WP_Query( array(
				'post_type' 	 => 'attachment',
				'post_mime_type' =>'image',
				'posts_per_page' => $batch_size,
				'nopaging' 	 => false,
				'post_status' 	 => 'all',
				'orderby' 	 => 'rand',
				'paged' 	 => $tmn_page
			) );	
		
			$tmn_iw_posts_query = ''; // Turn off filter

	} else {
			return "<div id='tmn-image-wall-error'><h3>Uh oh! I've detected a problem setting up the Image Wall!</h3><p>This is serious. I've been told to generate the image wall using a order method that I don't recognize. What is '".print_r($iw_image_wall_regen_method)."' anyway? Please <a href='http://www.themodernnomad.com/contact/'>contact me</a> and let me know that you have a problem, and I will take a look at it!</p></div>";		
	}
	

	// Loop over each of our fetched attachments.
	if( ! $iw_query->have_posts()) {
		if( $tmn_page == 1 ) {
			return "<div id='tmn-image-wall-error'><h3>" . __("Uh oh! I have no photos to display!", "image-wall") . "</h3><p>" . __("If you just activated the Image Wall plugin, then I am most likely still working on processing your images and generating a random order. Get a cup of tea and check back in an hour. If this error is still here, then go to the 'Settings -> Image Wall' admin screen and switch to the 'Calculation' image order method. If you've done so and still see this message? Then please <a href='http://www.themodernnomad.com/contact/'>contact me</a> and let me know that you have a problem, and I will take a look at it!", "image-wall") . "</p></div>";
		} else {
			exit(404); // This tells infinite scroll to stop looking for more images.
		}
	}

	while ( $iw_query->have_posts() ) : 
		$iw_query->the_post();
		$parent_ID = get_post_field('post_parent', get_the_id() );

		// Don't show orphaned or un-published images.
		if ( !$parent_ID || get_post_field('post_status', $parent_ID ) != 'publish' ) continue; 
		if ( ! $include_pages && get_post_type( $parent_ID ) == 'page' ) continue;
		if ( get_post_type( $parent_ID ) != 'page' ) {
			if ( !empty($include_categories) && ! in_category( $include_categories, $parent_ID ) ) continue;
			if ( !empty($exclude_categories) &&   in_category( $exclude_categories, $parent_ID ) ) continue;
			if ( !empty($include_tags) && ! has_tag( $include_tags, $parent_ID ) ) continue;
			if ( !empty($exclude_tags) &&   has_tag( $exclude_tags, $parent_ID ) ) continue;
		}
		
		// We need to find the best available image size to use. 
		// That is the image size that will span the most columns (legally) while using the fewest pixels.
		// There must also be a pre-generated version of the image size so we don't use the full image and 
		// totally explode the memory usage out of this thing.

		$columns_spanned_array = array();
		$width_array = array();
		$height_array = array();
		$url_array = array();

		foreach ($image_sizes as $image_size_name => $image_size_details) {				

			$wp_image_details = wp_get_attachment_image_src(get_the_id(), $image_size_name);

			$url    = $wp_image_details[0];
			$width  = $wp_image_details[1];
			$height = $wp_image_details[2];
			$image_size_generated = $wp_image_details[3]; 

			if(!is_numeric($width) || !is_numeric($height)) {
				$actual_image_details = getimagesize($url);
				if($actual_image_details){
					$width  = $actual_image_details[0];
					$height = $actual_image_details[1];
				} else {
					if($image_size_details['crop']){
						$width  = $image_size_details['width'];
						$height = $image_size_details['height'];
					} else {
						$width  = $image_size_details['width'];
						$height = 'unknown';				
					}
				}		
				$image_size_generated = $width == $image_size_details['width'];
			}
			
			
			if(!$image_size_generated) {
				// Uh oh! We are dealing with an image size that hasn't been generated yet. To save the user from downloading potentiall huge full size images,
				// we skip this.
				continue;
			}

			$max_number_of_columns = intval(( $width ) / $column_width);

			if($use_column_proportion_restrictions && $height != 'unknown'){
				for($i = count($column_proportion_restrictions) ; $i > 0 ; $i -- ) {
					if( $width / $height >= $column_proportion_restrictions[$i-1] ) {
						$number_of_columns = min($i, $max_number_of_columns);
						break;
					} else {

					}
				}
			} else {
				$number_of_columns = $max_number_of_columns;
			}	

			$columns_spanned_array[] = $number_of_columns;
			$width_array[] = $width;
			$height_array[] = $height;
			$url_array[] = $url;
		}

		// Check that we have at least one viable image version good enough for the wall. 
		if(count($url_array) == 0) continue;

		array_multisort($columns_spanned_array, SORT_ASC, $width_array, SORT_DESC, $url_array, $height_array);

		$columns_spanned = array_pop($columns_spanned_array);
		$width = array_pop($width_array);
		$height = array_pop($height_array);
		$url = array_pop($url_array);

		$alt_and_title = trim(strip_tags(get_the_title() . " (". get_the_title($parent_ID) .")" ));

		$image_wall_items[] = '<a class="tmn-image-wall-item-link" href="'.get_permalink( $parent_ID ).'" '.$target_string.' rel="nofollow"><img width="'.$width.'" height="'.$height.'" src="'.$url.'" class="tmn-image-wall-item tmn-image-wall-span-cols-'.$columns_spanned.'" alt="'.$alt_and_title.'" title="'.$alt_and_title.'" /></a>';
	endwhile;	// Done extracting images from batch of posts
		
	// Our work here is done! Restore the original query and return.
	wp_reset_postdata();
	
	// Time to pull together the HTML output.	
	
	$output  = '';
	if($support_author) {
		if($support_author_visibility) {
			$support_author_visibility_class = "class='shown'";
		} else {
			$support_author_visibility_class = "class='hidden'";
		}
		$output .= "<p id='tmn-image-wall-support' ". $support_author_visibility_class .">" . __("(Image Wall plugin created by <a href='http://www.themodernnomad.com' alt='A blog about living a geo-independent sustainable nomadic life of continuous and indefinite travel.' title='A blog about living a geo-independent sustainable nomadic life of continuous and indefinite travel.'>The Modern Nomad</a>.)") ."</p>";
	}
	$output .= '<div id="tmn-image-wall" scroll_img_url="'.plugins_url( 'loading.gif' , __FILE__ ).'" column_width="'.$column_width.'" buffer_pixels="'.$buffer_pixels.'" move_to_end="'.$move_to_end.'" style="background-color: '.$background_color.'; -webkit-border-radius: '.$corner_radius.'px; -moz-border-radius: '.$corner_radius.'px; border-radius: '.$corner_radius.'px; padding: '.$gutter_pixels/2 .'px;"></div>';
	$output .= '<div id="tmn-image-wall-prep">';
	$output .= join('', $image_wall_items);	
	if( $expect_more_posts ) {
		$output .= '<a id="tmn-image-wall-next" rel="nofollow" href="' . add_query_arg( 'tmn_iw_page', $tmn_page+1 , get_permalink()) .'">' . __('Next', 'image-wall') . '</a>';
	} else {
		$output .= '<!-- There are no more images for the Image Wall. -->';
	}
	$output .= '</div>';

	$output .= "\n"."<style>";
	$output .= "\n".'#tmn-image-wall img.tmn-image-wall-item { margin: '.$gutter_pixels/2 .'px; -webkit-border-radius: '.$corner_radius.'px; -moz-border-radius: '.$corner_radius.'px; border-radius: '.$corner_radius.'px; }';
	
	for($i = 1; $i <= 10; $i++) { 	
		$max_image_width_for_columns_spanned = $column_width * $i - $gutter_pixels;

		$output .= "\n".'#tmn-image-wall img.tmn-image-wall-item.tmn-image-wall-span-cols-'.$i.' { max-width: '.$max_image_width_for_columns_spanned.'px; width: '.$max_image_width_for_columns_spanned.'px;}';	
		//$output .= "\n".'#tmn-image-wall.tmn-image-wall-cols-'.$i.' img.tmn-image-wall-item { max-width: '.$max_image_width_for_columns_spanned.'px; }';	
	}
	$output .= "\n".'</style>';
	
	return $output;		
		
}


?>