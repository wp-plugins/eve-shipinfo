=== EVE ShipInfo ===
Contributors: AeonOfTime
Tags: EVE Online
Requires at least: 3.5
Tested up to: 4.1
Stable tag: 1.3
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.txt

Puts an EVE Online ships database in your WordPress website, along with high quality screenshots and specialized shortcodes.

== Description ==

Using shortcodes, you can link EVE Online ship names in your posts and show inline information about EVE Online ships, including custom ship screenshots independent from the EVE Online website. Each ship also gets its own, fully detailed page in your blog with virtual pages. All EVE Online ships are bundled, including the skinned variants (Abaddon Tash-Murkon Edition) and special edition ships (Mimir) or even those you cannot fly (Immovable Enigma).

= Features =

*   Portable EVE Online ships database with all 418 ships
*   836 high quality custom ship screenshots (front/side)
*   Link ship names to info popups or virtual ship pages within your blog
*   Extremely customizable ship lists shortcode
*   Ship galleries shortcode
*   Full integrated shortcode reference
*   Entirely translation-ready, including all ship attribute labels
*   For developers: easy object-oriented access to the ships database
*   Self-contained: no dependencies

= Ship screenshots pack =

Due to the size of the ship screenshots gallery, they are available as a separate download. Including
it would have made it impossible to install the plugin on most shared hosting packs, so on wordpress.org's
recommendation I did not include them in the repository.

You can download the screenshots pack here:

[Screenshots pack download page](http://aeonoftime.com/EVE_Online_Tools/EVE-ShipInfo-WordPress-Plugin/download.php)

Note: to install the screenshots pack, you will need access to your wordpress's plugins folder, for 
example via FTP. You have to upload the "gallery" folder to the "eve-shipinfo" plugin folder.

== Installation ==

1. Install from your WordPress plugin manager
1. Activate the plugin
1. Go to your permalink settings, and save the settings without changing anything (to refresh the permalinks)
1. Optional: Download and install the screenshots pack


== Changelog ==

= 1.3 =
* Added more filtering options to the ships collection filtering API (Cargo bay size, Drone bandwidth, Drone bay size, Piloteable, Tech level, Turret slots, Launcher slots)
* Added the new filtering options to the list and gallery shortcodes
* Added more list columns
* Improved ordering, added secondary property ordering 
* Improved the layout of the shortcode help pages with collapsible boxes
* Added missing default values to the shortcodes reference
* Made attribute type labels more meaningful in the shortcodes reference
* Checked WordPress 4.1 compatibility
* Reordered readme.txt somewhat for a better description
* Fixed a few localizeable strings
* Fixed the launcher slots in the ship info popup

= 1.2 =
* Updated data files for Phoebe with the new ship skin variants

= 1.1 =
* Made the screenshots gallery optional
* Removed reference to wp-load

= 1.0 = 
* Initial release