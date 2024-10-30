<?php
/**
 * Order Tracking Column
 *
 * @since   1.0.0
 * 
 * @var \WC_Order $order
 * @var string $print_url
 * 
 * @package Infixs\CorreiosAutomatico
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="infixs-correios-automatico-tracking-column-wrapper"
	data-order-id="<?php echo esc_attr( $order->get_id() ); ?>">
	<a class="button wc-action-button" title="Imprimir Etiqueta" href="<?php echo esc_attr( $print_url ); ?>"
		target="_blank" style="display: inline-flex;align-items: center;justify-content: center;">
		<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img"
			width="1.3em" height="1.3em" viewBox="0 0 32 32">
			<path fill="currentColor"
				d="M24 6.5V8h1a5 5 0 0 1 5 5v7.5a3.5 3.5 0 0 1-3.5 3.5H25v1.5a3.5 3.5 0 0 1-3.5 3.5h-11A3.5 3.5 0 0 1 7 25.5V24H5.5A3.5 3.5 0 0 1 2 20.5V13a5 5 0 0 1 5-5h1V6.5A3.5 3.5 0 0 1 11.5 3h9A3.5 3.5 0 0 1 24 6.5m-14 0V8h12V6.5A1.5 1.5 0 0 0 20.5 5h-9A1.5 1.5 0 0 0 10 6.5m-1 19a1.5 1.5 0 0 0 1.5 1.5h11a1.5 1.5 0 0 0 1.5-1.5v-6a1.5 1.5 0 0 0-1.5-1.5h-11A1.5 1.5 0 0 0 9 19.5zM25 22h1.5a1.5 1.5 0 0 0 1.5-1.5V13a3 3 0 0 0-3-3H7a3 3 0 0 0-3 3v7.5A1.5 1.5 0 0 0 5.5 22H7v-2.5a3.5 3.5 0 0 1 3.5-3.5h11a3.5 3.5 0 0 1 3.5 3.5z">
			</path>
		</svg>
	</a>
	<a class="button wc-action-button infixs-correios-automatico-tracking-update-button"
		title="Atualizar Código de Rastreamento"
		style="display: inline-flex;align-items: center;justify-content: center;">
		<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img"
			width="1.3em" height="1.3em" viewBox="0 0 14 14">
			<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
				<path d="M7 11.5a4.5 4.5 0 1 0 0-9a4.5 4.5 0 0 0 0 9"></path>
				<path d="M7 7.5a.5.5 0 1 0 0-1a.5.5 0 0 0 0 1m0-5v-2m0 13v-2M11.5 7h2M.5 7h2"></path>
			</g>
		</svg>
	</a>
	<div class="infixs-correios-automatico-tracking-edit-form" style="display:none;">
		<div style="flex: 1; position:relative;">
			<input type="text" value="" class="infixs-correios-automatico-tracking-update-input" style="width: 100%;">
			<div class="infixs-correios-automatico-spin-animation">
				<svg xmlns="http://www.w3.org/2000/svg" class="infixs-correios-automatico-spin-icon" width="1.5em"
					height="1.5em" viewBox="0 0 24 24">
					<g fill="none" fill-rule="evenodd">
						<path
							d="m12.593 23.258l-.011.002l-.071.035l-.02.004l-.014-.004l-.071-.035q-.016-.005-.024.005l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427q-.004-.016-.017-.018m.265-.113l-.013.002l-.185.093l-.01.01l-.003.011l.018.43l.005.012l.008.007l.201.093q.019.005.029-.008l.004-.014l-.034-.614q-.005-.018-.02-.022m-.715.002a.02.02 0 0 0-.027.006l-.006.014l-.034.614q.001.018.017.024l.015-.002l.201-.093l.01-.008l.004-.011l.017-.43l-.003-.012l-.01-.01z" />
						<path fill="currentColor"
							d="M12 4.5a7.5 7.5 0 1 0 0 15a7.5 7.5 0 0 0 0-15M1.5 12C1.5 6.201 6.201 1.5 12 1.5S22.5 6.201 22.5 12S17.799 22.5 12 22.5S1.5 17.799 1.5 12"
							opacity="0.1" />
						<path fill="currentColor"
							d="M12 4.5a7.46 7.46 0 0 0-5.187 2.083a1.5 1.5 0 0 1-2.075-2.166A10.46 10.46 0 0 1 12 1.5a1.5 1.5 0 0 1 0 3" />
					</g>
				</svg>
			</div>
		</div>

		<a class="button wc-action-button infixs-correios-automatico-tracking-confirm-button"
			style="display: inline-flex;align-items: center;justify-content: center;">
			<span class="dashicons dashicons-yes" style="font-size: 18px;"></span>
		</a>
		<a class="button wc-action-button infixs-correios-automatico-tracking-cancel-button"
			style="display: inline-flex;align-items: center;justify-content: center;">
			<span class="dashicons dashicons-no-alt" style="font-size: 18px;"></span>
		</a>
	</div>

</div>