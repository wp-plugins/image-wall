=== Image Wall ===
Contributors: parakoos
Tags: gallery, galleries, images, ajax, image, media, photo, photos, shortcode, 
Requires at least: 3.5
Tested up to: 3.5.1
Stable tag: 2.13
Donate link: http://www.themodernnomad.com/#utm_campaign=Image_Wall&utm_source=wordpress&utm_medium=website&utm_content=donation
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


Browse posts/pages by their images, displayed randomly on an infinitely scrollable page. The images link back to where they are attached.

== Description ==

This wordpress plugin allows visitors to browse posts or pages by their images, displayed randomly on an infinitely scrollable Image Wall. The images link back to the post or page on which they are attached.

Images are powerful. They catch our attention like nothing else. You probably use them in your blog posts to anchor the fickle attention span of today's readers. Display all these great images on a nice looking page, and your visitors will browse for a while, captivated. Hopefully, one image will stand out for whatever reason and compel the visitor to click it to find out more. And just like that, some old blog post you thought buried by the sands of time has gotten another view, thanks to your Image Wall.

You can see the plugin in action on my own [Image Wall](http://www.themodernnomad.com/image-wall/#utm_campaign=Image_Wall&utm_source=wordpress&utm_medium=website&utm_content=description) and the full plugin documentation on the [Image Wall Plugin page](http://www.themodernnomad.com/image-wall-plugin/#utm_campaign=Image_Wall&utm_source=wordpress&utm_medium=website&utm_content=description).

== Installation ==

1. Search for 'Image Wall' on the Wordpress plugin directory and install it from there, or download the latest version from the [Image Wall Plugin page](http://www.themodernnomad.com/image-wall-plugin/#utm_campaign=Image_Wall&utm_source=wordpress&utm_medium=website&utm_content=installation) and unzip it into your plugin directory.
1. Activate the plugin under 'Plugins -> Installed Plugins'
1. Add the shortcode [[image_wall]] to the page where you want to show the image wall.
1. You can control how the image order is re-generated under 'Settings -> Image Wall'.

You can customize much of the functionality of the Image Wall through shortcode arguments. For a complete installation guide and a list of arguments, please see the [Image Wall Plugin page](http://www.themodernnomad.com/image-wall-plugin/#utm_campaign=Image_Wall&utm_source=wordpress&utm_medium=website&utm_content=installation).

== Screenshots ==

1. An example image wall.
2. The admin screen.

== Changelog ==

= 0.1 =
* Initial beta version. I'll keep the plugin in beta for a bit to iron out any kinks. Please let me know of any issues so I can fix them!

= 0.2 =
* Changed the default setting of 'move_to_end' to false.
* Changed the default batch_size to 50 and buffer_pixels to 2000.
* Added a new option, open_links_in_new_window which defaults to 'true'.
* The plugin starts loading new images immediately, not requiring the reader to do an initial scroll action. 

= 1.0 =
* Tested it for Wordpress 3.3.2. I've had a few people beta test the plugin, and it seems to work. However, if you do find a bug, please let me know and give me chance to fix it before down-rating me!

= 1.1 =
* Added option to filter out images attached to pages.
* Added option to filter out images by the categories and/or tags of the parent post.

= 1.2 =
* Added translation into German. (Thanks to Konrad Tadesse for coding and translation!).

= 1.3 =
* Fixed an issue with multiple cron jobs regenerating images.

= 2.0 =
* Replaced Isotope for Masonry, which is licenced under MIT and therefore can be featured on the Wordpress plugin directory.
* Added shortcode arguments background_color, gutter_pixels and corner_radius. 

= 2.1 =
* Fixed an incompatibility issue with Jetpack Photon.

= 2.2 =
* Fixed a problem where certain PHP installations couldn't query the image size of external images.

= 2.3 =
* Added an error message if the Image Wall is used on an incompatible Wordpress version, i.e. a Wordpress version earlier than 3.5.

= 2.4 =
* Fixed a CSS issue with the width of the images where it was easy for other stylesheets to override and screw up the width of the image wall images.

= 2.5 =
* Added an alternative method for generating random image order for those whose WordPress installation couldn't handle the hashing method.

= 2.6 =
* Overriding max-height CSS styles from parent stylesheets wich could make the images seem very short.

= 2.7 =
* Fixed an error where upgrading from v2.5 would break the image wall.

= 2.8 =
* Setting back the box-sizing CSS setting of the container to content-box (in case a theme has set this to border-box)

= 2.9 =
* Fixed a bug where using both include_category/tag and include_pages didn't work.

= 2.10 =
* Changes the default value of the 'image_sizes' attribute to 'medium' from the previous 'thumbnail, medium'. If you used to rely on the default value, and want to keep both thumbnails and medium sizes, you must now specify it explicitly! (The reason for this change is that most users ran into issues with the thumbnails usually being cropped, and I've had to take a lot of support calls where the solution is to set the image_sizes to just medium.

= 2.11 =
* Added debug information to help me debug issues when I'm contacted for help.
* Added a donation section in the Settings page.

= 2.12 =
* Fixed an issue where certain PHP versions would print out a silly mistake a made which forced PHP to (rightly) assume that a variable was actually a string.

= 2.13 =
Fixed a bug introduced in 2.12. Don't use 2.12. It won't load more than the first batch of photos. Apologies.