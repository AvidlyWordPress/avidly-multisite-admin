<?php
/**
 * Plugin Name: Avidly Multisite Admin
 * Description: Allow administrators on multisite to save unfiltered HTML, edit users and add new users to network level without confirmation. Note: You must also enable "Add New Users" setting from global Network Settings.
 * Version: 1.0
 * Author: Avidly
 * Author URI: http://avidly.fi
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package Avidly_Multisite_Admin
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

// Run on plugin activation.
register_activation_hook( __FILE__, 'avidly_mu_admin_activation_hook' );
// Modify roles who can add unfiltered HTML to content.
add_filter( 'map_meta_cap', 'avidly_mu_admin_add_unfiltered_html', 1, 3 );

// Run on plugin deactivation.
register_deactivation_hook( __FILE__, 'avidly_mu_admin_deactivation_hook' );

/**
 * Add custom capabillities on plugin activation
 * Support multisite.
 *
 * @param bool $network_wide to check if change is made in network or single site.
 */
function avidly_mu_admin_activation_hook( $network_wide ) {
	if ( is_multisite() && $network_wide ) {
		foreach ( get_sites( array( 'fields' => 'ids' ) ) as $blog_id ) {
			switch_to_blog( $blog_id );
			avidly_mu_admin_check_caps( 'add' );
			restore_current_blog();
		}
	} else {
		avidly_mu_admin_check_caps( 'add' );
	}
}

/**
 * Restore default capabillities on plugin deactivation
 * Support multisite.
 *
 * @param bool $network_wide to check if change is made in network or single site.
 */
function avidly_mu_admin_deactivation_hook( $network_wide ) {
	if ( is_multisite() && $network_wide ) {
		foreach ( get_sites( array( 'fields' => 'ids' ) ) as $blog_id ) {
			switch_to_blog( $blog_id );
			avidly_mu_admin_check_caps( 'remove' );
			restore_current_blog();
		}
	} else {
		avidly_mu_admin_check_caps( 'remove' );
	}
}

/**
 * Set custom capabilities.
 *
 * @param string $function add or remove capability.
 * @return $admin_object
 */
function avidly_mu_admin_check_caps( $function = 'add' ) {
	$admin_object = get_role( 'administrator' );

	$admin_caps = array(
		'add_network_users',
		'manage_network_users',
	);

	foreach ( $admin_caps as $cap ) {
		if ( 'add' === $function ) {
			$admin_object->add_cap( $cap );
		} elseif ( 'remove' === $function ) {
			$admin_object->remove_cap( $cap );
		}
	}

	return $admin_object;
}

/**
 * Enable unfiltered_html capability for Admins.
 *
 * @param  array  $caps    The user's capabilities.
 * @param  string $cap     Capability name.
 * @param  int    $user_id The user ID.
 * @return array  $caps    The user's capabilities, with 'unfiltered_html' potentially added.
 */
function avidly_mu_admin_add_unfiltered_html( $caps, $cap, $user_id ) {
	if ( 'unfiltered_html' === $cap && user_can( $user_id, 'administrator' ) ) {
		$caps = array( 'unfiltered_html' );
	}
	return $caps;
}
