<?php
/*
Plugin Name: Launch Check
Version: 1.1.0
Plugin URI: http://matchboxdesigngroup.com/plugins/
Description: Ensure that you have made your site visible to search engines, changed the default description and added Google's Universal Analytics before launch.
Author: Matchbox Design Group
Author URI: http://matchboxdesigngroup.com/
License: GPL v3

Pre Launch Check
Copyright (C) 2014, Matchbox Design Group - info@matchboxdesigngroup.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


/**
 * Display an error message when the blog is set to private.
 *
 * @return void
 */
function mdg_is_blog_public() {
	$blog_public = get_option( 'blog_public' );

	if ( $blog_public == 1 ) {
		return;
	}

	$alert  = '';
	$alert .= '<div id="message" class="error">';
	$alert .= '<p>';
	$alert .= '<strong>' . __( "Huge SEO Issue: You're blocking access to robots.", 'mdg-launch-check' ) . '</strong> ';
	$alert .= sprintf( __( 'You must %sgo to your Reading Settings%s and uncheck the box for Search Engine Visibility.', 'mdg-launch-check' ), '<a href="' . admin_url( 'options-reading.php' ) . '">', '</a>' );
	$alert .= '</p></div>';

	echo wp_kses( $alert, 'post' );
}
add_action( 'lc_init', 'mdg_is_blog_public' );



/**
 * Display an error message when using the default WordPress tagline.
 *
 * @return void
 */
function mdg_check_blog_description() {
	$default_desc = get_option( 'blogdescription' );

	if ( $default_desc != 'Just another WordPress site' ) {
		return;
	}

	$alert  = '';
	$alert .= '<div id="message" class="error">';
	$alert .= '<p><strong>';
	$alert .= __( "Whoa there partner: You're Still using the default tagline.", 'mdg-launch-check' );
	$alert .= '</strong> ';
	$alert .= sprintf( __( 'You must %sgo to your General Settings%s and change the Tagline to a description for this site.', 'mdg-launch-check' ), '<a href="' . admin_url( 'options-general.php' ) . '">', '</a>' );
	$alert .= '</p></div>';

	echo wp_kses( $alert, 'post' );
}
add_action( 'lc_init', 'mdg_check_blog_description' );



/**
 * Check to see if the Google Analytics for WordPress plugin is installed.
 *
 * @return void
 */
function mdg_check_analytics_plugin() {
	$default_desc = get_option( 'blogdescription' );
	if ( is_plugin_active( 'universal-analytics/universalanalytics.php' ) ) {
		return;
	}

	$alert  = '';
	$alert .= '<div id="message" class="error">';
	$alert .= '<p><strong>';
	$alert .= __( "Easy killer: You're not tracking your site yet.", 'mdg-launch-check' );
	$alert .= '</strong> ';
	$alert .= sprintf( __( 'How are you going to track those awesome visitors? Go %sinstall the Universal Analytics plugin%s now.', 'mdg-launch-check' ), '<a href="' . admin_url( 'plugin-install.php?tab=plugin-information&plugin=universal-analytics&TB_iframe=true' ) . '" class="thickbox">', '</a>' );
	$alert .= '</p></div>';

	echo wp_kses( $alert, 'post' );
}
add_action( 'lc_init', 'mdg_check_analytics_plugin' );



/**
 * Plugin setup
 *
 * @return  void
 */
function mdg_admin_check() {
	// To disable screens from displaying the alerts add the current screens base to the disabled screens
	$current_screen   = get_current_screen();
	$disabled_screens = array(
		'plugin-install',
		'update',
	);

	if ( in_array( $current_screen->base, $disabled_screens ) ) {
		return;
	} // if()

	do_action( 'lc_init' );
}
add_action( 'admin_head', 'mdg_admin_check' );
