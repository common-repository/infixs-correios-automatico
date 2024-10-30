<?php

namespace Infixs\CorreiosAutomatico\Core;

use Infixs\CorreiosAutomatico\Container;
use Infixs\CorreiosAutomatico\Core\Front\WooCommerce\AutofillAddress;
use Infixs\CorreiosAutomatico\Core\Support\Config;
use Infixs\CorreiosAutomatico\Database\Migration;
use Infixs\CorreiosAutomatico\Core\Admin\Hooks as AdminHooks;
use Infixs\CorreiosAutomatico\Core\Front\Hooks as FrontHooks;

defined( 'ABSPATH' ) || exit;
/**
 * Correios Automático Core Functions
 *
 * @package Infixs\CorreiosAutomatico
 * @since   1.0.0
 */
class Core {
	/**
	 * Initialize the plugin.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		add_action( 'init', [ $this, 'check_update' ] );

		new Install();
		new AdminHooks();
		new FrontHooks();

		$this->load_modules();
	}

	public function load_modules() {
		if ( Config::boolean( 'general.autofill_address' ) ) {
			new AutofillAddress( Container::shippingService() );
		}
	}

	/**
	 * Check plugin update.
	 *
	 * @since 1.0.0
	 */
	public function check_update() {
		$version = get_option( '_infixs_correios_automatico_version' );
		if ( $version !== \INFIXS_CORREIOS_AUTOMATICO_PLUGIN_VERSION ) {
			update_option( '_infixs_correios_automatico_version', \INFIXS_CORREIOS_AUTOMATICO_PLUGIN_VERSION );
			Migration::run();
		}
	}
}