<?php
/**
 * Shipping Cost Results Template
 * 
 * @package Infixs\CorreiosAutomatico
 * @since   1.0.1
 * 
 * @global \WC_Shipping_Rate[] $rates
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="infixs-correios-automatico-shipping-results">
	<div class="infixs-correios-automatico-shipping-results-grid">
		<div>Entrega</div>
		<div>Custo</div>
		<?php
		foreach ( $rates as $rate ) :
			$meta_data = $rate->get_meta_data();
			?>
			<div>
				<div class="infixs-correios-automatico-shipping-results-method">
					<?php echo esc_html( $rate->label ); ?>
				</div>
				<?php if ( isset( $meta_data['delivery_time'] ) ) : ?>
					<div class="infixs-correios-automatico-shipping-results-time">
						<?php echo sprintf( "Receba até %s %s", esc_html( $meta_data['delivery_time'] ), esc_html( $meta_data['delivery_time'] > 1 ? 'dias úteis' : 'dia útil' ) ); ?>
					</div>
				<?php endif; ?>
			</div>
			<div class="infixs-correios-automatico-shipping-results-cost">
				<?php echo esc_html( $rate->cost > 0 ? "R$ " . number_format( $rate->cost, 2, ',', '.' ) : 'Grátis' ); ?>
			</div>

		<?php endforeach; ?>
	</div>
</div>