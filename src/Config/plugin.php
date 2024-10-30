<?php

defined( 'ABSPATH' ) || exit;

return apply_filters( 'infixs_correios_automatico_settings',
	[ 
		'general' => [ 
			'autofill_address' => 'yes',
			'calculate_shipping_product_page' => 'yes',
			'calculate_shipping_product_page_position' => 'meta_start',
			'show_order_tracking_form' => 'yes',
			'show_order_label_form' => 'yes',
			'show_order_prepost_form' => 'yes',
		],
		'auth' => [ 
			'active' => 'no',
			'environment' => 'production',
			'user_name' => '',
			'access_code' => '',
			'postcard' => '',
			'token' => '',
			'contract_type' => '',
			'contract_document' => '',
		],
		'sender' => [ 
			'name' => '',
			'email' => '',
			'phone' => '',
			'celphone' => '',
			'document' => '',
			'address_postalcode' => '',
			'address_street' => '',
			'address_complement' => '',
			'address_number' => '',
			'address_neighborhood' => '',
			'address_city' => '',
			'address_state' => '',
		],
		'label' => [ 
			'style' => '',
			'show_border' => 'no',
			'font_size' => 11,
			'width' => 400,
			'line_height' => 3,
			'show_logo' => 'yes',
			'logo_url' => '',
			'show_recipient_form' => 'yes',
			'show_sender_info' => 'yes',
			'show_recipient_barcode' => 'yes',
			'recipient_barcode_height' => 50,
			'logo_width' => 150,
			'page_margin' => 3,
			'items_gap' => 3,
			'columns_length' => 3,
		],
		'debug' => [ 
			'active' => 'yes',
			'debug_log' => 'no',
			'info_log' => 'no',
			'notice_log' => 'no',
			'warning_log' => 'no',
			'error_log' => 'yes',
			'critical_log' => 'yes',
			'alert_log' => 'yes',
			'emergency_log' => 'yes',
		]
	] );