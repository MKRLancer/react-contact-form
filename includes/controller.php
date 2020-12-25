<?php

add_action( 'parse_request', 'wpcf7_control_init', 20, 0 );

function wpcf7_control_init() {
	if ( WPCF7_Submission::is_restful() ) {
		return;
	}

	if ( isset( $_POST['_wpcf7'] ) ) {
		$contact_form = wpcf7_contact_form( (int) $_POST['_wpcf7'] );

		if ( $contact_form ) {
			$contact_form->submit();
		}
	}
}

add_action(
	'wp_enqueue_scripts',
	function () {
		$assets = array();
		$asset_file = wpcf7_plugin_path( 'includes/js/index.asset.php' );

		if ( file_exists( $asset_file ) ) {
			$assets = include( $asset_file );
		}

		$assets = wp_parse_args( $assets, array(
			'src' => wpcf7_plugin_url( 'includes/js/index.js' ),
			'dependencies' => array(
				'wp-api-fetch',
				'wp-polyfill',
			),
			'version' => WPCF7_VERSION,
			'in_footer' => ( 'header' !== wpcf7_load_js() ),
		) );

		wp_register_script(
			'contact-form-7',
			$assets['src'],
			$assets['dependencies'],
			$assets['version'],
			$assets['in_footer']
		);

		wp_register_script(
			'contact-form-7-html5-fallback',
			wpcf7_plugin_url( 'includes/js/html5-fallback.js' ),
			array( 'jquery-ui-datepicker' ),
			WPCF7_VERSION,
			true
		);

		if ( wpcf7_load_js() ) {
			wpcf7_enqueue_scripts();
		}

		wp_register_style(
			'contact-form-7',
			wpcf7_plugin_url( 'includes/css/styles.css' ),
			array(),
			WPCF7_VERSION,
			'all'
		);

		wp_register_style(
			'contact-form-7-rtl',
			wpcf7_plugin_url( 'includes/css/styles-rtl.css' ),
			array(),
			WPCF7_VERSION,
			'all'
		);

		wp_register_style(
			'jquery-ui-smoothness',
			wpcf7_plugin_url(
				'includes/js/jquery-ui/themes/smoothness/jquery-ui.min.css'
			),
			array(),
			'1.11.4',
			'screen'
		);

		if ( wpcf7_load_css() ) {
			wpcf7_enqueue_styles();
		}
	},
	10, 0
);

function wpcf7_enqueue_scripts() {
	wp_enqueue_script( 'contact-form-7' );

	$wpcf7 = array();

	if ( defined( 'WP_CACHE' ) and WP_CACHE ) {
		$wpcf7['cached'] = 1;
	}

	wp_localize_script( 'contact-form-7', 'wpcf7', $wpcf7 );

	do_action( 'wpcf7_enqueue_scripts' );
}

function wpcf7_script_is() {
	return wp_script_is( 'contact-form-7' );
}

function wpcf7_enqueue_styles() {
	wp_enqueue_style( 'contact-form-7' );

	if ( wpcf7_is_rtl() ) {
		wp_enqueue_style( 'contact-form-7-rtl' );
	}

	do_action( 'wpcf7_enqueue_styles' );
}

function wpcf7_style_is() {
	return wp_style_is( 'contact-form-7' );
}

/* HTML5 Fallback */

add_action( 'wp_enqueue_scripts', 'wpcf7_html5_fallback', 20, 0 );

function wpcf7_html5_fallback() {
	if ( ! wpcf7_support_html5_fallback() ) {
		return;
	}

	if ( wpcf7_script_is() ) {
		wp_enqueue_script( 'contact-form-7-html5-fallback' );
	}

	if ( wpcf7_style_is() ) {
		wp_enqueue_style( 'jquery-ui-smoothness' );
	}
}
