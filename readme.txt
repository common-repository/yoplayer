=== Yoplayer ===
Version: 4.1.33.2
Contributors: yospace
Tags: yoplayer, yospace, video
Requires at least: 3.0
Tested up to: 4.4.3
Stable tag: 4.1.33.2
License: GPLv2 or later

Yoplayer allows you to play videos from your yospaceCDS account in your WordPress website

== Description ==

Yoplayer is a Flash and HTML5 video player for your website which, in addition
to being able to play video files, can also play HTTP Live Stream format video
(The format used by iPad and iPhone).

The Yoplayer plugin for WordPress allows you to quickly and easily surface your
content from yospaceCDS into your WordPress site without having to write any
Javascript or embed any objects!

Simply insert a yoplayer tag, with the miid set to the Media Item ID and fid
set to the Feed ID from your yospaceCDS account and the plugin does the rest.

If you have more than one video, set up a feed and pass your yospaceCDS master
feed ID to the yoplayer tag to create a fully CSS styleable playlist. 

For ease of use, parameters which are often the same, such as fid and skin,
may be set to site-wide defaults configured through the settings page.

Yoplayer features:

* Automatically supports Web (Using Flash), iOS and Android (Using HTML5).
* Playback video files (So long as the browser or flash supports them)
* Google IMA advertising (Supports virtually all VAST and VPAID advertising)
* Playback HLS video streams, both Live and VoD. (1)
* CEA608 and WebVTT Closed Captions (HLS Only) (2)
* Client side interactivity for server side advert insertion (3)

(1) Playback from yospaceCDS. Other domains require a licence.

(2) Requires a licence.

(3) Requires a licence and content delivered using yospaceCDS.

== Frequently Asked Questions ==

Do I have to buy a license to use the player?

No, without a license you can play HLS from your yospaceCDS account or video
files from anywhere, however with a licence you will be able to play HLS content
from any sites you specified when you purchased your license, as well as
skinning the player, supporting CEA608 and WebVTT captions in HLS and client
side events interactivity for server side advert insertion.

Once you have purchased your licence you should place it in the root of your
wordpress site and all the players on your site will find it automatically.

== Go Pro! ==

The following features are available to licenced users:

* Play from anywhere - Add the ability to play videos from your site or CDN.
* Player skinning - Make the player match your site or product.
* Closed Captions - Both CEA608 and WebVTT format in HLS streams.
* Server Moderated VAST - Add support for client events from your CSM driven
  live streams.

== Installation ==

Upload the plugin your your WordPress site then click activate.

== Changelog ==

= 4.1.31 =
* Update yoplayer to version 4.1 build 31
* Major new release
* Many new features
* Improved compatibility with new releases of Flash

= 2.1.40 =
* Update yoplayer to version 2.1 build 40
* Fixes to support changes in Adobe Flash 18

= 2.1.38 =
* Upgrade yoplayer to version 2.1 build 38
* Fixed support for file URLs

= 2.1.33 =
* Upgraded yoplayer to version 2.1 build 33
* Fixed a typo in yoplayer.php

= 2.1.30 =
* Upgraded yoplayer to version 2.1 build 30
* Improved synchronisation for pre-roll adverts where the content downloads
  faster than the adverts.
* Fixed bug where an impatient viewer who clicks play while pre-roll adverts
  are loading could cause the video and advert to play at the same time.

= 2.1.29 =
* Upgrade to yoplayer version 2.1 including WebVTT closed captions and server
  moderated VAST integration.
* Switch from swfobject to our own javascript library that tries not to conflict
  with other libraries that may be loaded in the page.

= 2.0 =
* First release
