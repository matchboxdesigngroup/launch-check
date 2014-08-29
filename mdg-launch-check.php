<?php
/*
Plugin Name: Launch Check
Version: 1.2.0
Plugin URI: http://matchboxdesigngroup.com/plugins/
Description: Ensure that you have made your site visible to search engines, changed the default description, added Google's Universal Analytics and installed BruteProtect before launch.
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
 * Handles checking if a plugin is active on both single and multi-site installs.
 *
 * <code>mdg_is_plugin_active( 'plugin-directory/plugin-file.php')</code>
 *
 * @param   string    $plugin  The name of the plugin sub-directory/file.
 *
 * @return  boolean            if plugin is actived.
 */
function mdg_is_plugin_active( $plugin ) {
	if ( is_plugin_active_for_network( $plugin ) or is_plugin_active( $plugin ) ) {
		return true;
	} // if()

	return false;
} // mdg_is_plugin_active()



/**
 * Sets the dismiss status for a warning for the current user.
 *
 * <code>
 * <a href="{$current_page_url}?warning_get_var_key=0"
 * add_action( 'admin_init', 'mdg_warning_dismiss' );
 * </code>
 *
 * @since   1.1.1
 *
 * @return  void
 */
function mdg_warning_dismiss() {
	global $current_user;
	$user_id = $current_user->ID;

	// The possible warning $_GET var key.
	// @todo possibly global or if moved into class make it a property.
	$warning_get_var_keys = array(
		'lc_brute_protect',
	);

	// If user clicks to dismiss the notice, add that to their user meta
	foreach ( $warning_get_var_keys as $key_value ) {
		if ( isset($_GET[$key_value]) && '0' == $_GET[$key_value] ) {
			add_user_meta( $user_id, "_{$key_value}_dismiss_warning", 'true' );
		} // if()
	} // foreach()
} // mdg_warning_dismiss()
add_action( 'admin_init', 'mdg_warning_dismiss' );



/**
 * Checks if the user has dismissed the current warning.
 *
 * <code>
 * if ( mdg_warning_has_been_dismissed( 'warning_key' ) ) {
 * 	return;
 * }
 *
 * @todo solve the warning aka update-nag class better than injecting a style into the element.
 *
 * @param   string   $key  The key value of the current warning.
 *
 * @return  boolean        If the user has dismissed the warning.
 */
function mdg_warning_has_been_dismissed( $key ) {
	global $current_user;
	$user_id        = $current_user->ID;
	$user_dismissed = get_user_meta( $user_id, "_{$key}_dismiss_warning", true );

	if ( $user_dismissed != '' or $user_dismissed == 'true' ) {
		return true;
	} // if()

	return false;
} // mdg_warning_has_been_dismissed()


function mdg_alert_wrap( $alert_content, $class = 'error', $alert_key = '' ) {
	if ( $alert_content == '' ) {
		return '';
	} // if()

	if ( $class == 'warning' ) {
		$disable_link = '';
		if ( $alert_key != '' ) {
			$reuquest_uri = str_replace( '/wp-admin', '', $_SERVER['REQUEST_URI'] );
			$current_page = admin_url( $reuquest_uri );
			$disbale_url  = ( strpos( $current_page, '?' ) === false ) ? "{$current_page}?{$alert_key}=0" : "{$current_page}&{$alert_key}=0";
			$disable_link = " <a href='{$disbale_url}'>Dismiss</a>";
		} // if()

		return "<div class='error' style='border-left: 4px solid #ffba00;'><p>{$alert_content}{$disable_link}</p></div>";
	} // if()

	return "<div class='{$class}'><p>{$alert_content}</p></div>";
}



/**
 * Display an error message when the blog is set to private.
 *
 * <code>add_action( 'lc_init', 'mdg_is_blog_public' );</code>
 *
 * @since 1.0.0
 *
 * @return void
 */
function mdg_is_blog_public() {
	$blog_public = get_option( 'blog_public' );

	if ( $blog_public == 1 ) {
		return;
	}

	$alert  = '';
	$alert .= '<strong>';
	$alert .= __( "Huge SEO Issue: You're blocking access to robots.", 'mdg-launch-check' );
	$alert .= '</strong> ';
	$alert .= sprintf( __( 'You must %sgo to your Reading Settings%s and uncheck the box for Search Engine Visibility.', 'mdg-launch-check' ), '<a href="' . admin_url( 'options-reading.php' ) . '">', '</a>' );

	echo wp_kses( mdg_alert_wrap( $alert ), 'post' );
}
add_action( 'lc_init', 'mdg_is_blog_public' );



/**
 * Display an error message when using the default WordPress tagline.
 *
 * <code>add_action( 'lc_init', 'mdg_check_blog_description' );</code>
 *
 * @since 1.0.0
 *
 * @return void
 */
function mdg_check_blog_description() {
	$default_desc = get_option( 'blogdescription' );

	if ( $default_desc != 'Just another WordPress site' ) {
		return;
	}

	$alert  = '';
	$alert .= '<strong>';
	$alert .= __( "Whoa there partner: You're Still using the default tagline.", 'mdg-launch-check' );
	$alert .= '</strong> ';
	$alert .= sprintf( __( 'You must %sgo to your General Settings%s and change the Tagline to a description for this site.', 'mdg-launch-check' ), '<a href="' . admin_url( 'options-general.php' ) . '">', '</a>' );

	echo wp_kses( mdg_alert_wrap( $alert ), 'post' );
}
add_action( 'lc_init', 'mdg_check_blog_description' );



/**
 * Check to see if the Google Analytics for WordPress plugin is installed.
 *
 * <code>add_action( 'lc_init', 'mdg_check_analytics_plugin' );</code>
 *
 * @since 1.0.0
 *
 * @return void
 */
function mdg_check_analytics_plugin() {
	$default_desc = get_option( 'blogdescription' );

	if ( mdg_is_plugin_active( 'universal-analytics/universalanalytics.php' ) ) {
		return;
	}

	$alert  = '';
	$alert .= '<strong>';
	$alert .= __( "Easy killer: You're not tracking your site yet.", 'mdg-launch-check' );
	$alert .= '</strong> ';
	$alert .= sprintf( __( 'How are you going to track those awesome visitors? Go %sinstall the Universal Analytics plugin%s now.', 'mdg-launch-check' ), '<a href="' . admin_url( 'plugin-install.php?tab=plugin-information&plugin=universal-analytics&TB_iframe=true' ) . '" class="thickbox">', '</a>' );

	echo wp_kses( mdg_alert_wrap( $alert ), 'post' );
}
add_action( 'lc_init', 'mdg_check_analytics_plugin' );



/**
 * Check to see if the BruteProtect WordPress plugin is installed and activated.
 *
 * @return  void
 */
function mdg_check_brute_protect() {
	// Check for ButeProtect
	$plugin = 'bruteprotect/bruteprotect.php';
	if ( mdg_is_plugin_active( 'bruteprotect/bruteprotect.php' ) ) {
		return;
	} // if()

	$key = 'lc_brute_protect';

	if ( mdg_warning_has_been_dismissed( $key ) ) {
		return;
	} // if()

	$alert  = '';
	$alert .= '<strong>';
	$alert .= __( 'Hackers are jerks!', 'mdg-launch-check' );
	$alert .= '</strong> ';
	$alert .= sprintf( __( 'You need to protect your site against brute force attacks. Go %sinstall the BruteProtect plugin%s now. It\'s FREE!', 'mdg-launch-check' ), '<a href="' . admin_url( 'plugin-install.php?tab=plugin-information&plugin=bruteprotect&TB_iframe=true' ) . '" class="thickbox">', '</a>' );

	echo wp_kses( mdg_alert_wrap( $alert, 'warning', $key ), 'post' );
}
add_action( 'lc_init', 'mdg_check_brute_protect' );



/**
 * Plugin setup
 *
 * <code>add_action( 'admin_head', 'mdg_admin_check' );</code>
 *
 * @since 1.0.0
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
