<?php
// Array of Elusive Icons 
// Contributed by @WhatJustHappened 
// Last updated: 14 Sept. 2013
function get_wpautbox_socials(){
        $socials = array(
						'facebook' => __('Facebook', 'wpautbox'),
						'twitter' => __('Twitter', 'wpautbox'),
						'linkedin' => __('Linkedin', 'wpautbox'),
						'instagram' => __('Instagram', 'wpautbox'),
						'googleplus' => __('Google+', 'wpautbox'),
						'pinterest' => __('Pinterest', 'wpautbox'),
						'youtube' => __('Youtube', 'wpautbox'),
						'skype' => __('Skype', 'wpautbox'),
						'github' => __('Github', 'wpautbox'),
						'flickr' => __('Flickr', 'wpautbox'),
						'vimeo' => __('Vimeo', 'wpautbox'),
						'tumblr' => __('Tumblr', 'wpautbox'),
						'foursquare' => __('Foursquare', 'wpautbox'),
						'dribbble' => __('Dribbble', 'wpautbox'),
						'stumbleupon' => __('Stumbleupon', 'wpautbox'),
						'reddit' => __('Reddit', 'wpautbox'),
						'rdio' => __('Rdio', 'wpautbox'),
						'spotify' => __('Spotify', 'wpautbox'),
						'dropbox' => __('Dropbox', 'wpautbox'),
						'soundcloud' => __('Soundcloud', 'wpautbox'),
						'google-circles' => __('Google Circles', 'wpautbox'),
						'rss' => __('RSS', 'wpautbox'),
					);
        return $socials;
}
add_filter('wpautbox/socials' , 'get_wpautbox_socials');