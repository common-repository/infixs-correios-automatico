<?php

namespace Infixs\CorreiosAutomatico\Core\Admin\WooCommerce;

use Infixs\CorreiosAutomatico\Container;
use Infixs\CorreiosAutomatico\Core\Emails\TrackingCodeEmail;
use Infixs\CorreiosAutomatico\Core\Support\Config;
use Infixs\CorreiosAutomatico\Services\TrackingService;

defined( 'ABSPATH' ) || exit;

/**
 * Correios Automático Tracking Class
 *
 * @package Infixs\CorreiosAutomatico
 * @since   1.0.0
 */
class Tracking {

	/**
	 * Tracking service instance.
	 *
	 * @var TrackingService
	 */
	private $trackingService;

	/**
	 * Tracking constructor.
	 */
	public function __construct( TrackingService $trackingService ) {
		$this->trackingService = $trackingService;
	}



	public function register_order_meta_box() {

		if ( ! Config::boolean( "general.show_order_tracking_form" ) )
			return;

		add_meta_box(
			'infixs-correios-automatico-tracking-code',
			'Código de Rastreio',
			[ $this, 'render_order_meta_box' ],
			WCIntegration::get_shop_order_screen(),
			'side',
			'high'
		);
	}

	/**
	 * Render the tracking code meta box.
	 * 
	 * @param \WP_Post $post
	 * @return void
	 */
	public function render_order_meta_box( $post ) {
		$order = wc_get_order( $post->ID );
		$order_id = $order->get_id();

		$trackings = $this->trackingService->list( $order_id, [ 
			'order' => [ 
				'column' => 'created_at',
				'order' => 'desc',
			],
		] )->toArray();

		include_once INFIXS_CORREIOS_AUTOMATICO_PLUGIN_PATH . 'src/Presentation/admin/views/html-tracking-meta-box.php';
		wp_nonce_field( 'infixs-correios-automatico-trakking-code', 'trakking_code_nonce' );
	}


	public function save_tracking_code( $order_id, $order ) {
		if ( isset( $_POST['correios_automatico_tracking_code_nonce'], $_POST['correios_automatico_tracking_code'] )
			&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['correios_automatico_tracking_code_nonce'] ), ), 'correios-automatico-tracking-code' )
			&& $order
		) {
			$tracking_code = trim( sanitize_text_field( wp_unslash( $_POST['correios_automatico_tracking_code'] ) ) );
			$order->update_meta_data( '_infixs_correios_automatico_tracking_code', $tracking_code );


			$order->add_order_note(
				sprintf(
					/* translators: %1$s - Tracking code */
					__( 'Corrreios Automatic - New tracking code added to order: %1$s', 'infixs-correios-automatico' ),
					$tracking_code
				),
				false,
				true
			);

			$order->save();

			$this->send_tracking_notification( $order, $tracking_code );
			do_action( 'infixs_correios_automatico_tracking_updated', $order_id, $order, $tracking_code );
		}
	}

	public function send_tracking_notification( $order, $tracking_code ) {

	}

	/**
	 * Include emails.
	 *
	 * @param  array $emails Default emails.
	 *
	 * @return array
	 */
	public function include_emails( $emails ) {
		if ( ! isset( $emails['Correios_Automatico_Tracking_Code_Email'] ) ) {
			$emails['Correios_Automatico_Tracking_Code_Email'] = new TrackingCodeEmail();
		}
		return $emails;
	}

	/**
	 * Trigger tracking code email notification.
	 *
	 * @param \WC_Order $order         Order data.
	 * @param string   $tracking_code The Correios tracking code.
	 */
	public static function trigger_tracking_code_email( $order, $tracking_code ) {
		$mailer = WC()->mailer();
		$notification = $mailer->emails['Correios_Automatico_Tracking_Code_Email'];

		if ( 'yes' === $notification->enabled ) {
			$notification->trigger( $order->get_id(), $tracking_code );
		}
	}
}