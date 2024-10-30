<?php
/**
 * Order Tracking Column
 *
 * @since   1.0.0
 * 
 * @var \Infixs\CorreiosAutomatico\Models\TrackingCode $last_code
 * @var \WC_Order $order
 * 
 * @package Infixs\CorreiosAutomatico
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="infixs-correios-automatico-tracking-column-wrapper"
	data-order-id="<?php echo esc_attr( $order->get_id() ); ?>">
	<div class="infixs-correios-automatico-tracking-code-link">
		<?php if ( $last_code ) : ?>
			<a href="https://www.linkcorreios.com.br/?id=<?php echo esc_attr( $last_code->code ); ?>"
				aria-label="Código de rastreamento" target="_blank"><?php echo esc_html( $last_code->code ); ?></a>
		<?php endif; ?>
	</div>
</div>