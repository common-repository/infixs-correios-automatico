<?php

namespace Infixs\CorreiosAutomatico\Core\Front;

defined( 'ABSPATH' ) || exit;

/**
 * Correios Automático Front-End Functions
 * 
 * Settup all functions for public front end area, actions and filters.
 *
 * @package Infixs\CorreiosAutomatico
 * @since   1.0.0
 */
class Front {
	public function enqueue_scripts() {
		global $post;

		wp_enqueue_style(
			'infixs-correios-automatico-front',
			\INFIXS_CORREIOS_AUTOMATICO_PLUGIN_URL . 'assets/front/css/style.css',
			[],
			filemtime( \INFIXS_CORREIOS_AUTOMATICO_PLUGIN_PATH . 'assets/front/css/style.css' ),
		);

		wp_enqueue_script(
			'infixs-correios-automatico-orders',
			\INFIXS_CORREIOS_AUTOMATICO_PLUGIN_URL . 'assets/front/js/main.js',
			[ 'jquery', 'jquery-blockui', 'wp-util' ],
			filemtime( \INFIXS_CORREIOS_AUTOMATICO_PLUGIN_PATH . 'assets/front/js/main.js' ),
			true
		);

		$script_data = [ 
			'nonce' => wp_create_nonce( 'infixs_correios_automatico_nonce' ),
		];

		if ( function_exists( 'is_product' ) && is_product() ) {
			$script_data['productId'] = $post->ID;
		}

		wp_localize_script(
			'infixs-correios-automatico-orders',
			'infxsCorreiosAutomatico',
			$script_data
		);
	}
}