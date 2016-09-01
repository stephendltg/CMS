<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

/*------------------------------------------------------------------------------------------------*/
/* CSS, JS, FOOTER ============================================================================== */
/*------------------------------------------------------------------------------------------------*/

add_action( 'admin_enqueue_scripts', '__secupress_add_settings_scripts' );
/**
 * Add some CSS and JS to our settings pages.
 *
 * @since 1.0
 *
 * @param (string) $hook_suffix The current admin page.
 */
function __secupress_add_settings_scripts( $hook_suffix ) {

	$suffix    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	$version   = $suffix ? SECUPRESS_VERSION : time();
	$css_depts = array();
	$js_depts  = array( 'jquery' );

	// Sweet Alert.
	if ( 'secupress_page_' . SECUPRESS_PLUGIN_SLUG . '_modules' === $hook_suffix || 'toplevel_page_' . SECUPRESS_PLUGIN_SLUG . '_scanners' === $hook_suffix ) {
		// CSS.
		$css_depts = array( 'wpmedia-css-sweetalert2' );
		wp_enqueue_style( 'wpmedia-css-sweetalert2', SECUPRESS_ADMIN_CSS_URL . 'sweetalert2' . $suffix . '.css', array(), '1.3.4' );
		// JS.
		$js_depts  = array( 'jquery', 'wpmedia-js-sweetalert2' );
		wp_enqueue_script( 'wpmedia-js-sweetalert2', SECUPRESS_ADMIN_JS_URL . 'sweetalert2' . $suffix . '.js', array(), '1.3.4', true );
	}

	// WordPress Common CSS.
	wp_enqueue_style( 'secupress-wordpress-css', SECUPRESS_ADMIN_CSS_URL . 'secupress-wordpress' . $suffix . '.css', $css_depts, $version );

	// WordPress Common JS.
	wp_enqueue_script( 'secupress-wordpress-js', SECUPRESS_ADMIN_JS_URL . 'secupress-wordpress' . $suffix . '.js', $js_depts, $version, true );

	$localize_wp = array(
		'isPro'               => (int) secupress_is_pro(),
		'recoveryEmailNeeded' => __( 'Recovery Email Needed', 'secupress' ),
		'confirmText'         => __( 'OK', 'secupress' ),
		'cancelText'          => __( 'Cancel' ),
		'forYourSecurity'     => sprintf( __( 'For your security you should set a recovery email, %1$s will use it in case of hack as a rescue email address. <a href="%2$s">Do it now!</a>', 'secupress' ), SECUPRESS_PLUGIN_NAME, get_edit_profile_url( get_current_user_id() ) . '#secupress_recovery_email' ),
	);

	wp_localize_script( 'secupress-wordpress-js', 'SecuPressi18n', $localize_wp );

	$pages = array(
		'toplevel_page_' . SECUPRESS_PLUGIN_SLUG . '_scanners'  => 1,
		'secupress_page_' . SECUPRESS_PLUGIN_SLUG . '_modules'  => 1,
		'secupress_page_' . SECUPRESS_PLUGIN_SLUG . '_settings' => 1,
		'secupress_page_' . SECUPRESS_PLUGIN_SLUG . '_logs'     => 1,
	);

	if ( ! isset( $pages[ $hook_suffix ] ) ) {
		return;
	}

	// SecuPress Common CSS.
	wp_enqueue_style( 'secupress-common-css', SECUPRESS_ADMIN_CSS_URL . 'secupress-common' . $suffix . '.css', array( 'secupress-wordpress-css' ), $version );

	// WordPress Common JS.
	wp_enqueue_script( 'secupress-common-js', SECUPRESS_ADMIN_JS_URL . 'secupress-common' . $suffix . '.js', array( 'secupress-wordpress-js' ), $version, true );

	wp_localize_script( 'secupress-common-js', 'SecuPressi18nCommon', array(
		'confirmText'         => __( 'OK', 'secupress' ),
		'cancelText'          => __( 'Cancel' ),
		'closeText'           => __( 'Close' ),
		/*'authswal'     => array(
			'title'  => __( 'Authentication', 'secupress' ),
			'email'  => __( 'Enter your email', 'secupress' ),
			'apikey' => __( 'Enter your API Key', 'secupress' ),
			'where'  => __( 'Where can I find my API Key?', 'secupress' ),
			'save'   => __( 'Save and continue to first scan', 'secupress' ),
		),*/
	) );

	// Settings page.
	if ( 'secupress_page_' . SECUPRESS_PLUGIN_SLUG . '_settings' === $hook_suffix ) {
		// CSS.
		wp_enqueue_style( 'secupress-settings-css', SECUPRESS_ADMIN_CSS_URL . 'secupress-settings' . $suffix . '.css', array( 'secupress-common-css' ), $version );
	}
	// Modules page.
	elseif ( 'secupress_page_' . SECUPRESS_PLUGIN_SLUG . '_modules' === $hook_suffix ) {
		// CSS.
		wp_enqueue_style( 'secupress-modules-css',  SECUPRESS_ADMIN_CSS_URL . 'secupress-modules' . $suffix . '.css', array( 'secupress-common-css' ), $version );

		// JS.
		wp_enqueue_script( 'secupress-modules-js',  SECUPRESS_ADMIN_JS_URL . 'secupress-modules' . $suffix . '.js', array( 'secupress-common-js' ), $version, true );

		$already_scanned = array_filter( (array) get_site_option( SECUPRESS_SCAN_TIMES ) ) ? 1 : 0;

		wp_localize_script( 'secupress-modules-js', 'SecuPressi18nModules', array(
			// Roles.
			'selectOneRoleMinimum' => __( 'Select 1 role minimum', 'secupress' ),
			// Generic.
			'confirmTitle'         => __( 'Are you sure?', 'secupress' ),
			'confirmText'          => __( 'OK', 'secupress' ),
			'cancelText'           => __( 'Cancel' ),
			'error'                => __( 'Error', 'secupress' ),
			'unknownError'         => __( 'Unknown error.', 'secupress' ),
			'delete'               => __( 'Delete', 'secupress' ),
			'done'                 => __( 'Done!', 'secupress' ),
			// Backups.
			'confirmDeleteBackups' => __( 'You are about to delete all your backups.', 'secupress' ),
			'yesDeleteAll'         => __( 'Yes, delete all backups', 'secupress' ),
			'deleteAllImpossible'  => __( 'Impossible to delete all backups.', 'secupress' ),
			'deletingAllText'      => __( 'Deleting all backups&hellip;', 'secupress' ),
			'deletedAllText'       => __( 'All backups deleted', 'secupress' ),
			// Backup.
			'confirmDeleteBackup'  => __( 'You are about to delete a backup.', 'secupress' ),
			'yesDeleteOne'         => __( 'Yes, delete this backup', 'secupress' ),
			'deleteOneImpossible'  => __( 'Impossible to delete this backup.', 'secupress' ),
			'deletingOneText'      => __( 'Deleting Backup&hellip;', 'secupress' ),
			'deletedOneText'       => __( 'Backup deleted', 'secupress' ),
			// Backup actions.
			'backupImpossible'     => __( 'Impossible to backup the database.', 'secupress' ),
			'backupingText'        => __( 'Backuping&hellip;', 'secupress' ),
			'backupedText'         => __( 'Backup done', 'secupress' ),
			// Ban IPs.
			'noBannedIPs'          => __( 'No Banned IPs anymore.', 'secupress' ),
			'IPnotFound'           => __( 'IP not found.', 'secupress' ),
			'IPremoved'            => __( 'IP removed.', 'secupress' ),
			'searchResults'        => _x( 'See search result below.', 'adjective', 'secupress' ),
			'searchReset'          => _x( 'Search reset.', 'adjective', 'secupress' ),
			// First scan.
			'alreadyScanned'       => $already_scanned,
			'firstScanTitle'       => __( 'Before setting modules,<br>launch your first scan.', 'secupress' ),
			'firstScanText'        => __( 'It’s an automatic process that will help you secure your website.', 'secupress' ),
			'firstScanButton'      => __( 'Scan my website', 'secupress' ),
			'firstScanURL'         => esc_url( wp_nonce_url( secupress_admin_url( 'scanners' ), 'first_oneclick-scan' ) ) . '&oneclick-scan=1',
			'firstScanImage'       => SECUPRESS_ADMIN_IMAGES_URL . 'icon-radar.png',
			// Expand Textareas.
			'expandTextOpen'       => __( 'Show More', 'secupress' ),
			'expandTextClose'      => __( 'Close' ),
		) );

	}
	// Scanners page.
	elseif ( 'toplevel_page_' . SECUPRESS_PLUGIN_SLUG . '_scanners' === $hook_suffix ) {
		// CSS.
		wp_enqueue_style( 'secupress-scanner-css',  SECUPRESS_ADMIN_CSS_URL . 'secupress-scanner' . $suffix . '.css', array( 'secupress-common-css' ), $version );

		// JS.
		$depts   = array( 'secupress-common-js' );
		$is_main = is_network_admin() || ! is_multisite();

		if ( $is_main ) {
			$depts[] = 'secupress-chartjs';
			$counts  = secupress_get_scanner_counts();

			wp_enqueue_script( 'secupress-chartjs', SECUPRESS_ADMIN_JS_URL . 'chart' . $suffix . '.js', array(), '1.0.2.1', true );

			wp_localize_script( 'secupress-chartjs', 'SecuPressi18nChart', array(
				'good'          => array( 'value' => $counts['good'],          'text' => __( 'Good', 'secupress' ) ),
				'warning'       => array( 'value' => $counts['warning'],       'text' => __( 'Warning', 'secupress' ) ),
				'bad'           => array( 'value' => $counts['bad'],           'text' => __( 'Bad', 'secupress' ) ),
				'notscannedyet' => array( 'value' => $counts['notscannedyet'], 'text' => __( 'Not Scanned Yet', 'secupress' ) ),
			) );
		}

		wp_enqueue_script( 'secupress-scanner-js',  SECUPRESS_ADMIN_JS_URL . 'secupress-scanner' . $suffix . '.js', $depts, $version, true );

		$localize = array(
			'pluginSlug'         => SECUPRESS_PLUGIN_SLUG,
			'step'               => $is_main ? secupress_get_scanner_pagination() : 0,
			'confirmText'        => __( 'OK', 'secupress' ),
			'cancelText'         => __( 'Cancel' ),
			'error'              => __( 'Error', 'secupress' ),
			'fixed'              => __( 'Fixed', 'secupress' ),
			'fixedPartial'       => __( 'Partially fixed', 'secupress' ),
			'notFixed'           => __( 'Not Fixed', 'secupress' ),
			'fixit'              => __( 'Fix it', 'secupress' ),
			'oneManualFix'       => __( 'One fix requires your intervention.', 'secupress' ),
			'someManualFixes'    => __( 'Some fixes require your intervention.', 'secupress' ),
			'spinnerUrl'         => admin_url( 'images/wpspin_light-2x.gif' ),
			'reScan'             => _x( 'Scan', 'verb', 'secupress' ),
			'scanDetails'        => __( 'Scan Details', 'secupress' ),
			'fixDetails'         => __( 'Fix Details', 'secupress' ),
			'firstScanURL'       => esc_url( wp_nonce_url( secupress_admin_url( 'scanners' ), 'first_oneclick-scan' ) ) . '&oneclick-scan=1',
			'supportTitle'       => __( 'Ask for Support', 'secupress' ),
			'supportButton'      => __( 'Open a ticket', 'secupress' ),
			'supportContentFree' => __( '<p>During the test phase, the support is done by sending a manual email on <b>support@secupress.me</b>. Thank you!</p>', 'secupress' ), // ////.
			// 'supportContentFree' => __( '<p>Using the free version you have to post a new thread in the free wordpress.org forums.</p><p><a href="https://wordpress.org/support/plugin/secupress-free#postform" target="_blank" class="secupress-button secupress-button-mini"><span class="icon" aria-hidden="true"><i class="icon-wordpress"></i></span><span class="text">Open the forum</span></a></p><p>When using the Pro version, you can open a ticket directly from this popin: </p><br><p style="text-align:left">Summary: <input class="large-text" type="text" name="summary"></p><p style="text-align:left">Description: <textarea name="description" disabled="disabled">Please provide the specific url(s) where we can see each issue. e.g. the request doesn\'t work on this page: example.com/this-page</textarea></p>', 'secupress' ), // ////.
			'supportContentPro'  => '<input type="hidden" id="secupress_support_item" name="secupress_support_item" value=""><p style="text-align:left">Summary: <input class="large-text" type="text" name="summary"></p><p style="text-align:left">Description: <textarea name="description" disabled="disabled">Please provide the specific url(s) where we can see each issue. e.g. the request doesn\'t work on this page: example.com/this-page</textarea></p>', // ////.
			'a11y' => array(
				'scanEnded'    => __( 'Security Scan just finished.', 'secupress' ),
				'bulkFixStart' => __( 'Currently Fixing…', 'secupress' ) . ' ' . __( 'Please wait until fixing is complete.', 'secupress' ),
			),
			'comingSoon'       => __( 'Coming Soon', 'secupress' ),
			'docNotReady'      => __( 'The documentation is actually under construction, thank you for your patience.', 'secupress' ),
		);

		if ( $is_main ) {
			$localize['i18nNonce'] = wp_create_nonce( 'secupress-get-scan-counters' );
		}

		if ( ! empty( $_GET['oneclick-scan'] ) && ! empty( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'first_oneclick-scan' ) && current_user_can( secupress_get_capability() ) ) {
			$localize['firstOneClickScan'] = 1;

			$_SERVER['REQUEST_URI'] = remove_query_arg( array( '_wpnonce', 'oneclick-scan' ) );
		}

		wp_localize_script( 'secupress-scanner-js', 'SecuPressi18nScanner', $localize );
	}
	// Logs page.
	elseif ( 'secupress_page_' . SECUPRESS_PLUGIN_SLUG . '_logs' === $hook_suffix ) {
		// CSS.
		wp_enqueue_style( 'secupress-logs-css',  SECUPRESS_ADMIN_CSS_URL . 'secupress-logs' . $suffix . '.css', array( 'secupress-common-css' ), $version );
	}

	// Old WordPress Versions: before WordPress 3.9.
	if ( ! secupress_wp_version_is( '3.9' ) ) {
		wp_enqueue_style( 'secupress-wordpress-3-7',  SECUPRESS_ADMIN_CSS_URL . 'secupress-wordpress-3-7' . $suffix . '.css', array( 'secupress-common-css' ), $version );
	}

	// SecuPress version in footer.
	add_filter( 'update_footer', '__secupress_print_version_number_in_footer', 12, 1 );
}


/**
 * Add SecuPress version number next to WP version in footer
 *
 * @since  1.0
 * @author Geoffrey
 *
 * @param (string) $footer Text to print in footer.
 *
 * @return (string)
 */
function __secupress_print_version_number_in_footer( $footer ) {
	return ( $footer ? "$footer | " : '' ) . '<b>' . SECUPRESS_PLUGIN_NAME . ' v.' . SECUPRESS_VERSION . '</b>';
}


/*------------------------------------------------------------------------------------------------*/
/* PLUGINS LIST ================================================================================= */
/*------------------------------------------------------------------------------------------------*/

add_filter( 'plugin_action_links_' . plugin_basename( SECUPRESS_FILE ), '__secupress_settings_action_links' );
/**
 * Add links to the plugin row.
 *
 * @since 1.0
 *
 * @param (array) $actions An array of links.
 *
 * @return (array) The array of links + our links.
 */
function __secupress_settings_action_links( $actions ) {
	/*if ( ! secupress_is_white_label() ) { ////
		array_unshift( $actions, sprintf( '<a href="%s">%s</a>', 'https://secupress.me/support/', __( 'Support', 'secupress' ) ) );

		array_unshift( $actions, sprintf( '<a href="%s">%s</a>', 'http://docs.secupress.me', __( 'Docs', 'secupress' ) ) );
	}*/

	array_unshift( $actions, sprintf( '<a href="%s">%s</a>', esc_url( secupress_admin_url( 'settings' ) ), __( 'Settings' ) ) );

	return $actions;
}


/*------------------------------------------------------------------------------------------------*/
/* ADMIN MENU =================================================================================== */
/*------------------------------------------------------------------------------------------------*/

add_action( ( is_multisite() ? 'network_' : '' ) . 'admin_menu', 'secupress_create_menus' );
/**
 * Create the plugin menu and submenus.
 *
 * @since 1.0
 */
function secupress_create_menus() {
	global $menu;

	// Add a counter of scans with bad result.
	$count = sprintf( ' <span class="update-plugins count-%1$d"><span class="update-count">%1$d</span></span>', secupress_get_scanner_counts( 'bad' ) );
	$cap   = secupress_get_capability();

	// Main menu item.
	add_menu_page( SECUPRESS_PLUGIN_NAME, 'secupress', $cap, SECUPRESS_PLUGIN_SLUG . '_scanners', '__secupress_scanners', 'dashicons-shield-alt' );

	// Sub-menus.
	add_submenu_page( SECUPRESS_PLUGIN_SLUG . '_scanners', __( 'Scanners', 'secupress' ), __( 'Scanners', 'secupress' ) . $count, $cap, SECUPRESS_PLUGIN_SLUG . '_scanners', '__secupress_scanners' );
	add_submenu_page( SECUPRESS_PLUGIN_SLUG . '_scanners', __( 'Modules', 'secupress' ),  __( 'Modules', 'secupress' ),           $cap, SECUPRESS_PLUGIN_SLUG . '_modules',  '__secupress_modules' );
	add_submenu_page( SECUPRESS_PLUGIN_SLUG . '_scanners', __( 'Settings' ),              __( 'Settings' ),                       $cap, SECUPRESS_PLUGIN_SLUG . '_settings', '__secupress_global_settings' );
	// Pro page should be a module too… I edit, take a look ////.
	if ( secupress_is_pro() ) {
		add_submenu_page( SECUPRESS_PLUGIN_SLUG . '_scanners', __( 'Support', 'secupress' ), __( 'Support', 'secupress' ), $cap, SECUPRESS_PLUGIN_SLUG . '_modules&module=services', '__return_false' );
	} else {
		add_submenu_page( SECUPRESS_PLUGIN_SLUG . '_scanners', __( 'PRO Version', 'secupress' ), __( 'PRO Version', 'secupress' ), $cap, SECUPRESS_PLUGIN_SLUG . '_modules&module=get-pro', '__return_false' );
	}

	// Fix `add_menu_page()` nonsense.
	end( $menu );
	$key = key( $menu );
	$menu[ $key ][0] = SECUPRESS_PLUGIN_NAME . $count;
}


/*------------------------------------------------------------------------------------------------*/
/* SETTINGS PAGES =============================================================================== */
/*------------------------------------------------------------------------------------------------*/

/**
 * Settings page.
 *
 * @since 1.0
 */
function __secupress_global_settings() {
	if ( ! class_exists( 'SecuPress_Settings' ) ) {
		secupress_require_class( 'settings' );
	}

	$class_name = 'SecuPress_Settings_Global';

	if ( ! class_exists( $class_name ) ) {
		secupress_require_class( 'settings', 'global' );
	}

	if ( function_exists( 'secupress_pro_class_path' ) ) {
		$class_name = 'SecuPress_Pro_Settings_Global';

		if ( ! class_exists( $class_name ) ) {
			secupress_pro_require_class( 'settings', 'global' );
		}
	}

	$class_name::get_instance()->print_page();
}


/**
 * Modules page.
 *
 * @since 1.0
 */
function __secupress_modules() {
	if ( ! class_exists( 'SecuPress_Settings' ) ) {
		secupress_require_class( 'settings' );
	}
	if ( ! class_exists( 'SecuPress_Settings_Modules' ) ) {
		secupress_require_class( 'settings', 'modules' );
	}

	SecuPress_Settings_Modules::get_instance()->print_page();
}

/**
 * Scanners page.
 *
 * @since 1.0
 */
function __secupress_scanners() {
	$counts      = secupress_get_scanner_counts();
	$items       = array_filter( (array) get_site_option( SECUPRESS_SCAN_TIMES ) );
	$reports     = array();
	$last_report = '—';

	if ( $items ) {
		$last_percent = -1;

		foreach ( $items as $item ) {
			$reports[]    = secupress_formate_latest_scans_list_item( $item, $last_percent );
			$last_percent = $item['percent'];
		}

		$last_report = end( $items );
		$last_report = date_i18n( _x( 'M dS, Y \a\t h:ia', 'Latest scans', 'secupress' ), $last_report['time'] );
	}

	if ( isset( $_GET['step'] ) && 1 === (int) $_GET['step'] ) {
		secupress_set_old_report();
	}

	$currently_scanning_text = '
		<span aria-hidden="true" class="secupress-second-title">' . esc_html__( 'Currently scanning', 'secupress' ) . '</span>
		<span class="secupress-scanned-items">
			' . sprintf(
				__( '%1$s&nbsp;/&nbsp;%2$s points' , 'secupress' ),
				'<span class="secupress-scanned-current">0</span>',
				'<span class="secupress-scanned-total">1</span>'
			) . '
		</span>';
	?>
	<div class="wrap">

		<?php secupress_admin_heading( __( 'Scanners', 'secupress' ) ); ?>

		<div class="secupress-wrapper">
			<div class="secupress-section-dark secupress-scanners-header<?php echo $reports ? '' : ' secupress-not-scanned-yet'; ?>">

				<div class="secupress-heading secupress-flex secupress-wrap">
					<div class="secupress-logo-block secupress-flex">
						<div class="secupress-lb-logo">
							<?php echo secupress_get_logo( array( 'width' => 59 ) ); ?>
						</div>
						<div class="secupress-lb-name">
							<p class="secupress-lb-title">
							<?php echo secupress_get_logo_word( array( 'with' => 98, 'height' => 23 ) ); ?>
							</p>
						</div>
					</div>
					<?php if ( ! $reports ) { ?>
					<div class="secupress-col-text">
						<p class="secupress-text-medium"><?php esc_html_e( 'First scan', 'secupress' ); ?></p>
						<p><?php  esc_html_e( 'Here’s how it’s going to work', 'secupress' ); ?></p>
					</div>
					<?php } ?>
					<p class="secupress-label-with-icon secupress-last-scan-result">
						<i class="icon-secupress" aria-hidden="true"></i>
						<span class="secupress-upper"><?php _e( 'Result of the scan', 'secupress' ); ?></span>
						<span class="secupress-primary"><?php echo $last_report; ?></span>
					</p>
					<p class="secupress-text-end hide-if-no-js">
						<a href="#secupress-more-info" class="secupress-link-icon secupress-open-moreinfo<?php echo $reports ? '' : ' secupress-activated dont-trigger-hide'; ?>" data-trigger="slidedown" data-target="secupress-more-info">
							<span class="icon" aria-hidden="true">
								<i class="icon-info"></i>
							</span>
							<span class="text">
								<?php esc_html_e( 'How does it work?', 'secupress' ); ?>
							</span>
						</a>
					</p>
				</div><!-- .secupress-heading -->

				<?php
				if ( secupress_get_scanner_pagination() === 1 || secupress_get_scanner_pagination() === 4 ) {
					?>
					<div class="secupress-scan-header-main secupress-flex">
						<div id="sp-tab-scans" class="secupress-tabs-contents secupress-flex">
							<div id="secupress-scan" class="secupress-tab-content" role="tabpanel" aria-labelledby="secupress-l-scan">
								<div class="secupress-flex secupress-chart">

									<div class="secupress-chart-container">
										<canvas class="secupress-chartjs" id="status_chart" width="180" height="180"></canvas>
										<div class="secupress-score"><?php echo $counts['letter']; ?></div>
									</div>

									<div class="secupress-chart-legends-n-note">

										<div class="secupress-scan-infos">
											<p class="secupress-score-text secupress-text-big secupress-m0">
												<?php echo $counts['text']; ?>
											</p>
											<p class="secupress-score secupress-score-subtext secupress-m0"><?php echo $counts['subtext']; ?></p>
										</div>

										<ul class="secupress-chart-legend hide-if-no-js">
											<li class="status-good" data-status="good">
												<span class="secupress-carret"></span>
												<?php esc_html_e( 'Good', 'secupress' ); ?>
												<span class="secupress-count-good"></span>
											</li>
											<li class="status-bad" data-status="bad">
												<span class="secupress-carret"></span>
												<?php esc_html_e( 'Bad', 'secupress' ); ?>
												<span class="secupress-count-bad"></span>
											</li>
											<li class="status-warning" data-status="warning">
												<span class="secupress-carret"></span>
												<?php esc_html_e( 'Warning', 'secupress' ); ?>
												<span class="secupress-count-warning"></span>
											</li>
											<?php if ( $counts['notscannedyet'] ) : ?>
											<li class="status-notscannedyet" data-status="notscannedyet">
												<span class="secupress-carret"></span>
												<?php esc_html_e( 'New Scan', 'secupress' ); ?>
												<span class="secupress-count-notscannedyet"></span>
											</li>
											<?php endif; ?>
										</ul><!-- .secupress-chart-legend -->

										<div id="tweeterA" class="hidden">
											<p>
												<q>
												<?php
												/** Translators: %s is the plugin name */
												printf( esc_html__( 'Wow! My website just got an A security grade using %s, what about yours?', 'secupress' ), SECUPRESS_PLUGIN_NAME );
												?>
												</q>
											</p>

											<a class="secupress-button secupress-button-mini" href="https://twitter.com/intent/tweet?via=secupress&amp;url=<?php
												/** Translators: %s is the plugin name */
												echo urlencode( esc_url_raw( 'http://secupress.me&text=' . sprintf( __( 'Wow! My website just got an A security grade using %s, what about yours?', 'secupress' ), SECUPRESS_PLUGIN_NAME ) ) );
											?>">
												<span class="icon" aria-hidden="true"><span class="dashicons dashicons-twitter"></span></span>
												<span class="text"><?php esc_html_e( 'Tweet that', 'secupress' ); ?></span>
											</a>
										</div><!-- #tweeterA -->
									</div><!-- .secupress-chart-legends-n-note -->

								</div><!-- .secupress-chart.secupress-flex -->
							</div><!-- .secupress-tab-content -->

							<div id="secupress-latest" class="secupress-tab-content hide-if-js" role="tabpanel" aria-labelledby="secupress-l-latest">

								<h3 class="secupress-text-medium hide-if-js"><?php esc_html_e( 'Your last scans', 'secupress' ); ?></h3>

								<div class="secupress-latest-list">
									<ul class="secupress-reports-list">
										<?php
										if ( (bool) $reports ) {
											echo implode( "\n", $reports );
										} else {
											echo '<li class="secupress-empty"><em>' . __( 'You have no other reports for now.', 'secupress' ) . "</em></li>\n";
										}
										?>
									</ul>
								</div><!-- .secupress-latest-list -->

							</div><!-- .secupress-tab-content -->


							<div id="secupress-schedule" class="secupress-tab-content hide-if-js" role="tabpanel" aria-labelledby="secupress-l-schedule">
								<p class="secupress-text-medium">
									<?php esc_html_e( 'Schedule your security analysis', 'secupress' ); ?>
								</p>
								<p><?php _e( 'The analysis of security points is keeping updated. No need to connect to your back office with our automatic scan.', 'secupress' ); ?></p>

								<?php if ( secupress_is_pro() ) :
									// /////.
									$last_schedule = '1463654935';
									$next_schedule = '1464654935';
									?>
									<div class="secupress-schedules-infos is-pro">
										<p class="secupress-schedule-last-one">
											<i class="icon-clock-o" aria-hidden="true"></i>
											<span><?php printf( __( 'Last automatic scan: %s', 'secupress' ), date_i18n( _x( 'Y-m-d \a\t h:ia', 'Schedule date', 'secupress' ), $last_schedule ) ); ?></span>
										</p>
										<p class="secupress-schedule-next-one">
											<i class="icon-clock-o" aria-hidden="true"></i>
											<span><?php printf( __( 'Next automatic scan: %s', 'secupress' ), date_i18n( _x( 'Y-m-d \a\t h:ia', 'Schedule date', 'secupress' ), $next_schedule ) ); ?></span>
										</p>

										<p class="secupress-cta">
											<a href="<?php echo esc_url( secupress_admin_url( 'modules', 'schedules' ) ); ?>#module-scanners" class="secupress-button secupress-button-primary" target="_blank"><?php esc_html_e( 'Schedule your next analysis', 'secupress' ); ?></a>
										</p>
									</div><!-- .secupress-schedules-infos -->
								<?php else : ?>
									<div class="secupress-schedules-infos">
										<p class="secupress-schedule-last-one">
											<i class="icon-clock-o" aria-hidden="true"></i>
											<span><?php printf( __( 'Last automatic scan: %s', 'secupress' ), '&mdash;' ); ?></span>
										</p>
										<p class="secupress-schedule-next-one">
											<i class="icon-clock-o" aria-hidden="true"></i>
											<span><?php printf( __( 'Next automatic scan: %s', 'secupress' ), '&mdash;' ); ?></span>
										</p>

										<p class="secupress-cta">
											<a href="<?php echo esc_url( secupress_admin_url( 'modules', 'schedules' ) ); ?>#module-scanners" class="secupress-button secupress-button-tertiary" target="_blank"><?php esc_html_e( 'Schedule your next analysis', 'secupress' ); ?></a>
										</p>
										<p class="secupress-cta-detail"><?php _e( 'Available with pro version', 'secupress' ); ?></p>
									</div><!-- .secupress-schedules-infos -->
								<?php endif; ?>

							</div><!-- .secupress-tab-content -->
						</div><!-- .secupress-tabs-contents -->
						<div class="secupress-tabs-controls hide-if-no-js">
							<ul class="secupress-tabs secupress-tabs-controls-list" role="tablist" data-content="#sp-tab-scans">
								<li role="presentation">
									<a id="secupress-l-latest" href="#secupress-latest" role="tab" aria-selected="false" aria-controls="secupress-latest">
										<span class="secupress-label-with-icon">
											<i class="icon-back rounded" aria-hidden="true"></i>
											<span class="secupress-upper"><?php esc_html_e( 'Latest scans', 'secupress' ); ?></span>
											<span class="secupress-description"><?php esc_html_e( 'View your previous scans', 'secupress' ); ?></span>
										</span>
									</a>
								</li>
								<li role="presentation">
									<a id="secupress-l-schedule" href="#secupress-schedule" role="tab" aria-selected="false" aria-controls="secupress-schedule">
										<span class="secupress-label-with-icon">
											<i class="icon-calendar rounded" aria-hidden="true"></i>
											<span class="secupress-upper"><?php esc_html_e( 'Schedule Scans', 'secupress' ); ?></span>
											<span class="secupress-description"><?php esc_html_e( 'Manage your recurring scans', 'secupress' ); ?></span>
										</span>
									</a>
								</li>
								<li role="presentation" class="hidden">
									<a id="secupress-l-scan" href="#secupress-scan" role="tab" aria-selected="false" aria-controls="secupress-scan" class="secupress-current">
										<span class="secupress-label-with-icon">
											<i class="icon-secupress" aria-hidden="true"></i>
											<span class="secupress-upper"><?php esc_html_e( 'Result of the scan', 'secupress' ); ?></span>
											<span class="secupress-primary"><?php echo $last_report; ?></span>
										</span>
									</a>
								</li>
							</ul>
							<div class="secupress-rescan-progress-infos">
								<h3>
									<i class="icon-secupress" aria-hidden="true"></i><br>

									<?php echo $currently_scanning_text; ?>
								</h3>
							</div>
							<p class="secupress-rescan-actions">
								<span class="screen-reader-text"><?php esc_html_e( 'Doubts? Try a new scan.', 'secupress' ); ?></span>
								<button class="secupress-button secupress-button-primary secupress-button-scan" type="button" data-nonce="<?php echo esc_attr( wp_create_nonce( 'secupress-update-oneclick-scan-date' ) ); ?>">
									<span class="icon" aria-hidden="true">
										<i class="icon-radar"></i>
									</span>
									<span class="text">
										<?php _e( 'Scan website', 'secupress' ); ?>
									</span>

									<span class="secupress-progressbar-val" style="width:2%;">
										<span class="secupress-progress-val-txt" aria-hidden="true">2 %</span>
									</span>
								</button>
							</p>
						</div>
					</div><!-- .secupress-scan-header-main -->
					<?php
				}

				if ( ! $reports ) {
					?>
					<div class="secupress-introduce-first-scan secupress-text-center">
						<h3>
							<i class="icon-secupress" aria-hidden="true"></i><br>
							<span class="secupress-init-title"><?php esc_html_e( 'Click to launch first scan', 'secupress' ); ?></span>

							<?php echo $currently_scanning_text; ?>
						</h3>

						<p class="secupress-start-one-click-scan">
							<button class="secupress-button secupress-button-primary secupress-button-scan" type="button" data-nonce="<?php echo esc_attr( wp_create_nonce( 'secupress-update-oneclick-scan-date' ) ); ?>">
								<span class="icon" aria-hidden="true">
									<i class="icon-radar"></i>
								</span>
								<span class="text">
									<?php esc_html_e( 'Scan my website', 'secupress' ); ?>
								</span>

								<span class="secupress-progressbar-val" style="width:2%;">
									<span class="secupress-progress-val-txt">2 %</span>
								</span>

							</button>
						</p>
					</div><!-- .secupress-introduce-first-scan -->
					<?php
				}
				?>

				<div class="secupress-scanner-steps">
					<?php
					/**
					 * SecuPress Steps work this way:
					 * - current step with li.secupress-current
					 * - passed step(s) with li.secupress-past
					 * - that's all
					 */
					$steps = array(
						'1' => array( 'title' => esc_html__( 'Security Report', 'secupress' ) ),
						'2' => array( 'title' => esc_html__( 'Auto-Fix', 'secupress' ) ),
						'3' => array( 'title' => esc_html__( 'Manual Operations', 'secupress' ) ),
						'4' => array( 'title' => esc_html__( 'Resolutions Report', 'secupress' ) ),
					);
					$step  = secupress_get_scanner_pagination();
					switch ( $step ) {
						case 1:
							$steps[1]['state'] = ' secupress-current';
							$steps[2]['state'] = '';
							$steps[3]['state'] = '';
							$steps[4]['state'] = '';
						break;
						case 2:
							$steps[1]['state'] = ' secupress-past';
							$steps[2]['state'] = ' secupress-current';
							$steps[3]['state'] = '';
							$steps[4]['state'] = '';
						break;
						case 3:
							$steps[1]['state'] = ' secupress-past';
							$steps[2]['state'] = ' secupress-past';
							$steps[3]['state'] = ' secupress-current';
							$steps[4]['state'] = '';
						break;
						case 4:
							$steps[1]['state'] = ' secupress-past';
							$steps[2]['state'] = ' secupress-past';
							$steps[3]['state'] = ' secupress-past';
							$steps[4]['state'] = ' secupress-current';
						break;
					}
					$current_step_class = 'secupress-is-step-' . $step;
					unset( $step );
					?>
					<ol class="secupress-flex secupress-counter <?php echo esc_attr( $current_step_class ); ?>">
						<?php
						foreach ( $steps as $i => $step ) {
							?>
							<li class="secupress-col-1-3 secupress-counter-put secupress-flex<?php echo $step['state']; ?>" aria-labelledby="sp-step-<?php echo $i; ?>-l" aria-describedby="sp-step-<?php echo $i; ?>-d">
								<span class="secupress-step-name" id="sp-step-<?php echo $i; ?>-l"><?php echo $step['title']; ?></span>
								<?php if ( 3 === $i ) { ?>
									<span class="secupress-step-name alt" aria-hidden="true"><?php echo $steps[4]['title']; ?></span>
								<?php } ?>
							</li>
							<?php
						}
						?>
					</ol>

					<div id="secupress-more-info" class="<?php echo $reports ? ' hide-if-js' : ' secupress-open'; ?>">
						<div class="secupress-flex secupress-flex-top">
							<div class="secupress-col-1-4">
								<div class="secupress-blob">
									<div class="secupress-blob-icon" aria-hidden="true">
										<i class="icon-radar"></i>
									</div>
									<p class="secupress-blob-title"><?php esc_html_e( 'Security Report', 'secupress' ); ?></p>
									<div class="secupress-blob-content" id="sp-step-1-d">
										<p><?php esc_html_e( 'Start to check all security items with the Scan your website button.', 'secupress' ); ?></p>
									</div>
								</div>
							</div><!-- .secupress-col-1-4 -->
							<div class="secupress-col-1-4">
								<div class="secupress-blob">
									<div class="secupress-blob-icon" aria-hidden="true">
										<i class="icon-autofix"></i>
									</div>
									<p class="secupress-blob-title"><?php esc_html_e( 'Auto-Fix', 'secupress' ) ?></p>
									<div class="secupress-blob-content" id="sp-step-2-d">
										<p><?php esc_html_e( 'Launch the auto-fix on selected issues.', 'secupress' ); ?></p>
									</div>
								</div>
							</div><!-- .secupress-col-1-4 -->
							<div class="secupress-col-1-4">
								<div class="secupress-blob">
									<div class="secupress-blob-icon" aria-hidden="true">
										<i class="icon-manuals"></i>
									</div>
									<p class="secupress-blob-title"><?php esc_html_e( 'Manual Operations', 'secupress' ) ?></p>
									<div class="secupress-blob-content" id="sp-step-3-d">
										<p><?php esc_html_e( 'Go further and take a look at points you have to fix thanks to specific operation.', 'secupress' ); ?></p>
									</div>
								</div>
							</div><!-- .secupress-col-1-4 -->
							<div class="secupress-col-1-4">
								<div class="secupress-blob">
									<div class="secupress-blob-icon" aria-hidden="true">
										<i class="icon-pad-check"></i>
									</div>
									<p class="secupress-blob-title"><?php esc_html_e( 'Resolutions Report', 'secupress' ); ?></p>
									<div class="secupress-blob-content" id="sp-step-4-d">
										<p><?php esc_html_e( 'Get the new security report about your website.', 'secupress' ); ?></p>
									</div>
								</div><!-- .secupress-blob -->
							</div><!-- .secupress-col-1-4 -->
						</div><!-- .secupress-flex -->

						<p class="secupress-text-end secupress-m0">
							<a href="#secupress-more-info" class="secupress-link-icon secupress-icon-right secupress-close-moreinfo<?php echo $reports ? '' : ' dont-trigger-hide'; ?>" data-trigger="slideup" data-target="secupress-more-info">
								<span class="icon" aria-hidden="true">
									<i class="icon-cross"></i>
								</span>
								<span class="text">
									<?php esc_html_e( 'I\'ve got it!', 'secupress' ); ?>
								</span>
							</a>
						</p>
					</div><!-- #secupress-more-info -->
				</div><!-- .secupress-scanner-steps -->

			</div><!-- .secupress-section-dark -->

			<?php
			// ////.
			secupress_require_class( 'settings' );
			secupress_require_class( 'settings', 'modules' );
			$modules         = SecuPress_Settings_Modules::get_modules();
			?>
			<div class="secupress-scanner-main-content secupress-section-gray secupress-bordered">

				<div class="secupress-step-content-container">
				<?php
					secupress_scanners_template();
				?>
				</div><!-- .secupress-step-content-container-->

			</div>

			<?php wp_nonce_field( 'secupress_score', 'secupress_score', false ); ?>
		</div>
	</div><!-- .wrap -->
	<?php
}


/*------------------------------------------------------------------------------------------------*/
/* TEMPLATE TAGS ================================================================================ */
/*------------------------------------------------------------------------------------------------*/

/**
 * Print the settings page title.
 *
 * @since 1.0
 *
 * @param (string) $title The title.
 */
function secupress_admin_heading( $title = '' ) {
	$heading_tag = secupress_wp_version_is( '4.3-alpha' ) ? 'h1' : 'h2';
	printf( '<%1$s class="secupress-page-title screen-reader-text">%2$s <sup>%3$s</sup> %4$s</%1$s>', $heading_tag, SECUPRESS_PLUGIN_NAME, SECUPRESS_VERSION, $title );
}

/**
 * Print the dark header of settings pages
 *
 * @since 1.0
 * @author Geoffrey
 *
 * @param (array) $titles The title and subtitle.
 */
function secupress_settings_heading( $titles = array() ) {
	extract( $titles );
	?>
	<div class="secupress-section-dark secupress-settings-header secupress-header-mini secupress-flex">
		<div class="secupress-col-1-3 secupress-col-logo secupress-text-center">
			<div class="secupress-logo-block secupress-flex">
				<div class="secupress-lb-logo">
					<?php echo secupress_get_logo( array( 'width' => 131 ) ); ?>
				</div>
				<div class="secupress-lb-name">
					<p class="secupress-lb-title">
					<?php echo secupress_get_logo_word( array( 'width' => 100, 'height' => 24 ) ); ?>
					</p>
				</div>
			</div>
		</div>
		<div class="secupress-col-1-3 secupress-col-text">
			<p class="secupress-text-medium"><?php echo $title; ?></p>
			<?php if ( isset( $subtitle ) ) { ?>
			<p><?php echo $subtitle; ?></p>
			<?php } ?>
		</div>
		<div class="secupress-col-1-3 secupress-col-rateus secupress-text-end">
			<p class="secupress-rateus hidden">
				<strong><?php _e( 'You like this plugin?' ) ?></strong>
				<br>
				<?php printf( __( 'Please take a few seconds to rate us on %sWordPress.org%s', 'secupress' ), '<a href="' . SECUPRESS_RATE_URL . '">', '</a>' ); ?>
			</p>
			<p class="secupress-rateus-link hidden">
				<a href="<?php echo SECUPRESS_RATE_URL; ?>">
					<i class="icon-star" aria-hidden="true"></i>
					<i class="icon-star" aria-hidden="true"></i>
					<i class="icon-star" aria-hidden="true"></i>
					<i class="icon-star" aria-hidden="true"></i>
					<i class="icon-star" aria-hidden="true"></i>
					<span class="screen-reader-text"><?php echo _x( 'Give us a five stars', 'hidden text', 'secupress' ); ?></span>
				</a>
			</p>
		</div>
	</div>
	<?php
}


/**
 * Print the scanners page content.
 *
 * @since 1.0
 */
function secupress_scanners_template() {
	secupress_require_class( 'scan' );

	$is_subsite   = is_multisite() && ! is_network_admin();
	$heading_tag  = secupress_wp_version_is( '4.4-alpha' ) ? 'h2' : 'h3';
	// Allowed tags in "Learn more" contents.
	$allowed_tags = array(
		'a'      => array( 'href' => array(), 'title' => array(), 'target' => array() ),
		'abbr'   => array( 'title' => array() ),
		'code'   => array(),
		'em'     => array(),
		'strong' => array(),
		'ul'     => array(),
		'ol'     => array(),
		'li'     => array(),
		'p'      => array(),
		'pre'    => array( 'class' => array() ),
		'br'     => array(),
	);
	// Auto-scans: scans that will be executed on page load.
	$autoscans   = SecuPress_Scan::get_and_delete_autoscans();

	if ( ! $is_subsite ) {
		$secupress_tests = secupress_get_scanners();
		$scanners        = secupress_get_scan_results();
		$fixes           = secupress_get_fix_results();

		// Store the scans in 3 variables. They will be used to order the scans by status: 'bad', 'warning', 'notscannedyet', 'good'.
		$bad_scans     = array();
		$warning_scans = array();
		$good_scans    = array();

		if ( ! empty( $scanners ) ) {
			foreach ( $scanners as $class_name_part => $details ) {
				if ( 'bad' === $details['status'] ) {
					$bad_scans[ $class_name_part ] = $details['status'];
				} elseif ( 'warning' === $details['status'] ) {
					$warning_scans[ $class_name_part ] = $details['status'];
				} elseif ( 'good' === $details['status'] ) {
					$good_scans[ $class_name_part ] = $details['status'];
				}
			}
		}
	} else {
		$secupress_tests = array( secupress_get_tests_for_ms_scanner_fixes() );
		$sites           = secupress_get_results_for_ms_scanner_fixes();
		$site_id         = get_current_blog_id();
		$scanners        = array();
		$fixes           = array();

		foreach ( $sites as $test => $site_data ) {
			if ( ! empty( $site_data[ $site_id ] ) ) {
				$scanners[ $test ] = ! empty( $site_data[ $site_id ]['scan'] ) ? $site_data[ $site_id ]['scan'] : array();
				$fixes[ $test ]    = ! empty( $site_data[ $site_id ]['fix'] )  ? $site_data[ $site_id ]['fix']  : array();
			}
		}
	}

	$step = secupress_get_scanner_pagination();

	switch ( $step ) {
		case 4 :
			require( SECUPRESS_INC_PATH . 'admin/scanner-step-4.php' );
			break;
		case 3 :
			require( SECUPRESS_INC_PATH . 'admin/scanner-step-3.php' );
			break;
		case 2 :
			require( SECUPRESS_INC_PATH . 'admin/scanner-step-2.php' );
			break;
		case 1 :
		default:
			require( SECUPRESS_INC_PATH . 'admin/scanner-step-1.php' );
	}
}


/**
 * Get a scan or fix status, formatted with icon and human readable text.
 *
 * @since 1.0
 *
 * @param (string) $status The status code.
 *
 * @return (string) Formatted status.
 */
function secupress_status( $status ) {
	switch ( $status ) :
		case 'bad':
			return __( 'Bad', 'secupress' );
		case 'good':
			return __( 'Good', 'secupress' );
		case 'warning':
			return __( 'Warning', 'secupress' );
		case 'cantfix':
			return __( 'Error', 'secupress' );
		default:
			return __( 'New', 'secupress' );
	endswitch;
}


/**
 * Print a box with title.
 *
 * @since 1.0
 *
 * @param (array) $args An array containing the box title, content and id.
 */
function secupress_sidebox( $args ) {
	$args = wp_parse_args( $args, array(
		'id'      => '',
		'title'   => 'Missing',
		'content' => 'Missing',
	) );

	echo '<div class="secupress-postbox postbox" id="' . $args['id'] . '">';
		echo '<h3 class="hndle"><span><b>' . $args['title'] . '</b></span></h3>';
		echo'<div class="inside">' . $args['content'] . '</div>';
	echo "</div>\n";
}