<?php
/*
Module Name: Bad URL Access.
Description: Deny access to some sensitive files.
Main Module: sensitive_data
Author: SecuPress
Version: 1.0
*/
defined( 'SECUPRESS_VERSION' ) or die( 'Cheatin&#8217; uh?' );

/*------------------------------------------------------------------------------------------------*/
/* ACTIVATION / DEACTIVATION ==================================================================== */
/*------------------------------------------------------------------------------------------------*/

add_action( 'secupress.modules.activate_submodule_' . basename( __FILE__, '.php' ), 'secupress_bad_url_access_activation' );
/**
 * On module activation, maybe write the rules.
 *
 * @since 1.0
 */
function secupress_bad_url_access_activation() {
	global $is_apache, $is_nginx, $is_iis7;

	// Apache.
	if ( $is_apache ) {
		$rules = secupress_bad_url_access_apache_rules();
	}
	// IIS7.
	elseif ( $is_iis7 ) {
		$rules = secupress_bad_url_access_iis7_rules();
	}
	// Nginx.
	elseif ( $is_nginx ) {
		$rules = secupress_bad_url_access_nginx_rules();
	}
	// Not supported.
	else {
		$rules = '';
	}

	secupress_add_module_rules_or_notice( array(
		'rules'  => $rules,
		'marker' => 'bad_url_access',
		'title'  => __( 'Bad URL Access', 'secupress' ),
	) );
}


add_action( 'secupress.modules.deactivate_submodule_' . basename( __FILE__, '.php' ), 'secupress_bad_url_access_deactivate' );
/**
 * On module deactivation, maybe remove rewrite rules from the `.htaccess`/`web.config` file.
 *
 * @since 1.0
 */
function secupress_bad_url_access_deactivate() {
	secupress_remove_module_rules_or_notice( 'bad_url_access', __( 'Bad URL Access', 'secupress' ) );
}


add_filter( 'secupress.plugins.activation.write_rules', 'secupress_bad_url_access_plugin_activate', 10, 2 );
/**
 * On SecuPress activation, add the rules to the list of the rules to write.
 *
 * @since 1.0
 *
 * @param (array) $rules Other rules to write.
 *
 * @return (array) Rules to write.
 */
function secupress_bad_url_access_plugin_activate( $rules ) {
	global $is_apache, $is_nginx, $is_iis7;
	$marker = 'bad_url_access';

	if ( $is_apache ) {
		$rules[ $marker ] = secupress_bad_url_access_apache_rules();
	} elseif ( $is_iis7 ) {
		$rules[ $marker ] = array( 'nodes_string' => secupress_bad_url_access_iis7_rules() );
	} elseif ( $is_nginx ) {
		$rules[ $marker ] = secupress_bad_url_access_nginx_rules();
	}

	return $rules;
}


/*------------------------------------------------------------------------------------------------*/
/* RULES ======================================================================================== */
/*------------------------------------------------------------------------------------------------*/

/**
 * Bad URL Access: get rules for apache.
 *
 * @since 1.0
 *
 * @return (string)
 */
function secupress_bad_url_access_apache_rules() {
	/**
	 * ^php\.ini$
	 *
	 * ^wp-admin/admin-functions\.php$
	 * ^wp-admin/install\.php$
	 * ^wp-admin/menu-header\.php$
	 * ^wp-admin/menu\.php$
	 * ^wp-admin/setup-config\.php$
	 * ^wp-admin/upgrade-functions\.php$
	 *
	 * ^wp-admin/includes/.+\.php$
	 *
	 * ^wp-admin/network/menu\.php$
	 *
	 * ^wp-admin/user/menu\.php$
	 *
	 * ^wp-includes/.+\.php$
	 */
	$bases     = secupress_get_rewrite_bases();
	$base      = $bases['base'];
	$site_from = $bases['site_from'];
	$pattern   = '^(' . $bases['home_from'] . 'php\.ini|' . $site_from . WPINC . '/.+\.php|' . $site_from . 'wp-admin/(admin-functions|install|menu-header|setup-config|([^/]+/)?menu|upgrade-functions|includes/.+)\.php)$';

	// Trigger a 404 error, because forbidding access to a file is nice, but making it also invisible is more fun :).
	$rules  = "<IfModule mod_rewrite.c>\n";
	$rules .= "    RewriteEngine On\n";
	$rules .= "    RewriteBase $base\n";
	$rules .= "    RewriteCond %{REQUEST_URI} !^{$site_from}wp-includes/js/tinymce/wp-tinymce\.php$\n";
	$rules .= "    RewriteRule $pattern [R=404,L]\n";
	$rules .= "</IfModule>\n";

	return $rules;
}


/**
 * Bad URL Access: get rules for iis7.
 *
 * @since 1.0
 *
 * @return (string)
 */
function secupress_bad_url_access_iis7_rules() {
	$marker    = 'bad_url_access';
	$spaces    = str_repeat( ' ', 8 );
	$bases     = secupress_get_rewrite_bases();
	$site_from = $bases['site_from'];
	$pattern   = '^(' . $bases['home_from'] . 'php\.ini|' . $site_from . WPINC . '/.+\.php|' . $site_from . 'wp-admin/(admin-functions|install|menu-header|setup-config|([^/]+/)?menu|upgrade-functions|includes/.+)\.php)$';

	$rules  = "<rule name=\"SecuPress $marker\" stopProcessing=\"true\">\n";
	$rules .= "$spaces  <match url=\"$pattern\"/>\n";
	$rules .= "$spaces  <conditions>\n";
	$rules .= "$spaces    <add input=\"{REQUEST_URI}\" pattern=\"^{$site_from}wp-includes/js/tinymce/wp-tinymce\.php$\" negate=\"true\"/>\n";
	$rules .= "$spaces  </conditions>\n";
	$rules .= "$spaces  <action type=\"CustomResponse\" statusCode=\"404\"/>\n";
	$rules .= "$spaces</rule>";

	return $rules;
}


/**
 * Bad URL Access: get rules for nginx.
 *
 * @since 1.0
 *
 * @return (string)
 */
function secupress_bad_url_access_nginx_rules() {
	$marker    = 'bad_url_access';
	$bases     = secupress_get_rewrite_bases();
	$site_from = ltrim( $bases['site_from'], '/' );
	$pattern   = '^/(' . ltrim( $bases['home_from'], '/' ) . 'php\.ini|' . $site_from . WPINC . '/((?:(?!js/tinymce/wp-tinymce).)+)\.php|' . $site_from . 'wp-admin/(admin-functions|install|menu-header|setup-config|([^/]+/)?menu|upgrade-functions|includes/.+)\.php)$';

	$rules = "
server {
	# BEGIN SecuPress $marker
	location ~* $pattern {
		return 404;
	}
	# END SecuPress
}";

	return trim( $rules );
}
