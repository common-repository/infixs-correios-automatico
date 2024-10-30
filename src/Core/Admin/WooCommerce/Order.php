<?php

namespace Infixs\CorreiosAutomatico\Core\Admin\WooCommerce;
use Infixs\CorreiosAutomatico\Container;
use Infixs\CorreiosAutomatico\Core\Shipping\CorreiosShippingMethod;
use Infixs\CorreiosAutomatico\Core\Support\Config;
use Infixs\CorreiosAutomatico\Services\Correios\Includes\Address;
use Infixs\CorreiosAutomatico\Services\Correios\Includes\Package;
use Infixs\CorreiosAutomatico\Services\Correios\Includes\Person;
use Infixs\CorreiosAutomatico\Services\Correios\Includes\Prepost;
use Infixs\CorreiosAutomatico\Utils\Sanitizer;
use Infixs\CorreiosAutomatico\Core\Support\Log;

defined( 'ABSPATH' ) || exit;

/**
 * Correios Automático Order Class
 *
 * @package Infixs\CorreiosAutomatico
 * @since   1.0.0
 */
class Order {
	public function payment_complete( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order || ( $order && $order->is_paid() == false ) )
			return;

		if ( $order->has_shipping_method( 'infixs-correios-automatico' ) ) {
			$shipping_method = $this->get_infixs_correios_shipping_method( $order );
			if ( ! $shipping_method ) {
				return;
			}

			Log::debug( "Um pagamento foi confirmado para um pedido com o método de envio '{$shipping_method->get_title()}' dos Correios Automático." );

			if ( Config::boolean( 'auth.active' ) && ! empty( Config::string( 'sender.name' ) ) && $shipping_method->get_auto_prepost() ) {

				Log::debug( 'Iniciando a Pré-Postagem automática com declaração de conteúdo.' );

				$recipient_address = null;

				if ( $order->has_shipping_address() ) {
					$recipient_address = new Address(
						Sanitizer::numeric_text( $order->get_shipping_postcode() ),
						$order->get_shipping_address_1(),
						$order->get_meta( '_shipping_number' ),
						$order->get_shipping_address_2(),
						$order->get_meta( '_shipping_neighborhood' ),
						$order->get_shipping_city(),
						$order->get_shipping_state()
					);
				} else {
					$recipient_address = new Address(
						Sanitizer::numeric_text( $order->get_billing_postcode() ),
						$order->get_billing_address_1(),
						$order->get_meta( '_billing_number' ),
						$order->get_billing_address_2(),
						$order->get_meta( '_billing_neighborhood' ),
						$order->get_billing_city(),
						$order->get_billing_state()
					);
				}

				$cpfCnpj = '';
				$name = '';
				if ( $order->meta_exists( '_billing_persontype' ) && $order->get_meta( '_billing_persontype' ) == '2' ) {
					$cpfCnpj = Sanitizer::numeric_text( empty( $order->get_meta( '_billing_cnpj' ) ) ? $order->get_meta( '_billing_cpf' ) : $order->get_meta( '_billing_cnpj' ) );
					$name = empty( $order->get_shipping_company() ) ? $order->get_billing_company() : $order->get_shipping_company();

				} else {
					$cpf = $order->get_meta( '_billing_cpf' );
					$cpfCnpj = empty( $cpf ) ? '' : Sanitizer::numeric_text( $cpf );
					$first_name = empty( $order->get_shipping_first_name() ) ? $order->get_billing_first_name() : $order->get_shipping_first_name();
					$last_name = empty( $order->get_shipping_last_name() ) ? $order->get_billing_last_name() : $order->get_shipping_last_name();
					$name = trim( "$first_name $last_name" );
				}

				$recipient_phone = Sanitizer::phone( empty( $order->get_shipping_phone() ) ? $order->get_billing_phone() : $order->get_shipping_phone() );
				$recipient_celphone = Sanitizer::celphone( empty( $order->get_meta( '_billing_cellphone' ) ) ? $order->get_billing_phone() : $order->get_meta( '_billing_cellphone' ) );

				$recipient = new Person(
					$name,
					$recipient_address,
					$cpfCnpj,
					$recipient_phone,
					$recipient_celphone,
					$order->get_billing_email(),
				);


				Log::info( 'informativo aqui' );

				$sender = new Person(
					Config::string( 'sender.name' ),
					new Address(
						Sanitizer::numeric_text( Config::string( 'sender.address_postalcode' ) ),
						Config::string( 'sender.address_street' ),
						Config::string( 'sender.address_number' ),
						Config::string( 'sender.address_complement' ),
						Config::string( 'sender.address_neighborhood' ),
						Config::string( 'sender.address_city' ),
						Config::string( 'sender.address_state' )
					),
					Config::string( 'sender.document' ),
					Config::string( 'sender.phone' ),
					Config::string( 'sender.celphone' ),
					Config::string( 'sender.email' )
				);

				$package_data = [];
				$items = $order->get_items();
				foreach ( $items as $item ) {
					$package_data['contents'][ $item->get_id()] = [ 
						'quantity' => $item->get_quantity(),
						'data' => $item->get_product(),
						'line_total' => $item->get_total(),
					];
				}

				$package = $shipping_method->get_package( $package_data );

				$prepost = new Prepost(
					Config::string( 'auth.user_name' ),
					$sender,
					$recipient,
					$shipping_method->get_product_code(),
					$shipping_method->get_object_type_code()
				);

				$prepost->setPackage( $package );


				//Container::correiosService()->create_prepost( $prepost );
				//$order->update_meta_data( '_infixs_correios_automatico_order_status', 'pending' );
				//$order->save();
			} else {

				if ( ! Config::boolean( 'auth.active' ) && $shipping_method->get_auto_prepost() ) {
					Log::notice( "Você ativou a Pré-Postagem automática para o método '{$shipping_method->get_title()}', porém seu contrato está desativado nas configurações." );
				}

				if ( Config::boolean( 'auth.active' ) && $shipping_method->get_auto_prepost() && empty( Config::string( 'sender.name' ) ) ) {
					Log::notice( "É necessário preencher os dados do remetente para que funcione a Pré-Postagem automática." );
				}
			}
		}

	}

	/**
	 * Get the Correios shipping method from the order
	 *
	 * @param \WC_Order $order
	 * 
	 * @return CorreiosShippingMethod|false
	 */
	public function get_infixs_correios_shipping_method( $order ) {
		foreach ( $order->get_shipping_methods() as $shipping_method ) {
			if ( strpos( $shipping_method->get_method_id(), 'infixs-correios-automatico' ) === 0 ) {
				$instance_id = $shipping_method->get_instance_id();
				return new CorreiosShippingMethod( $instance_id );
			}
		}

		return false;
	}

	public function save_order_meta_data( $order_id ) {
		$order = wc_get_order( $order_id );

		$meta_data = [];

		foreach ( $order->get_items( 'shipping' ) as $item ) {
			$delivery_time = $item->get_meta( 'delivery_time' );
			$width = $item->get_meta( '_width' );
			$height = $item->get_meta( '_height' );
			$lenght = $item->get_meta( '_length' );
			$weight = $item->get_meta( '_weight' );

			if ( ! isset( $meta_data['width'] ) && ! empty( $width ) ) {
				$meta_data['width'] = $width;
			}

			if ( ! isset( $meta_data['height'] ) && ! empty( $height ) ) {
				$meta_data['height'] = $height;
			}

			if ( ! isset( $meta_data['lenght'] ) && ! empty( $lenght ) ) {
				$meta_data['lenght'] = $lenght;
			}

			if ( ! isset( $meta_data['weight'] ) && ! empty( $weight ) ) {
				$meta_data['weight'] = $weight;
			}

			if ( ! isset( $meta_data['delivery_time'] ) && ! empty( $delivery_time ) ) {
				$meta_data['delivery_time'] = $delivery_time;
			}
		}

		$order->update_meta_data( '_infixs_correios_automatico_data', $meta_data );

		$order->save();
	}
}