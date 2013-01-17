=== Image Wall ===
Contributors: parakoos
Tags: gallery, galleries, images, ajax, image, media, photo, photos, shortcode, 
Requires at least: 3.5
Tested up to: 3.5
Stable tag: 2.3
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