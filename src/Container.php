<?php

namespace Infixs\CorreiosAutomatico;

use Infixs\CorreiosAutomatico\Services\InfixsApi;
use Infixs\CorreiosAutomatico\Services\OrderService;
use Pimple\Container as PimpleContainer;
use Infixs\CorreiosAutomatico\Repositories\LogRepository;
use Infixs\CorreiosAutomatico\Repositories\PrepostRepository;
use Infixs\CorreiosAutomatico\Repositories\TrackingRepository;
use Infixs\CorreiosAutomatico\Routes\RestRoutes;
use Infixs\CorreiosAutomatico\Services\Correios\CorreiosApi;
use Infixs\CorreiosAutomatico\Services\Correios\CorreiosService;
use Infixs\CorreiosAutomatico\Services\LabelService;
use Infixs\CorreiosAutomatico\Services\PrepostService;
use Infixs\CorreiosAutomatico\Services\TrackingService;
use Infixs\CorreiosAutomatico\Repositories\ConfigRepository;
use Infixs\CorreiosAutomatico\Services\EmailService;
use Infixs\CorreiosAutomatico\Services\ShippingService;

defined( 'ABSPATH' ) || exit;

/**
 * Class Container
 *
 * @package Infixs\CorreiosAutomatico
 * @since   1.0.0
 */
class Container {
	private $container;
	private static $instance = null;

	/**
	 * Get the instance of the class.
	 *
	 * @since 1.0.0
	 * @return Container
	 */
	public static function getInstance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Container constructor.
	 */
	public function __construct() {
		$this->container = new PimpleContainer();
		$this->container['routes'] = fn() => new RestRoutes();

		$this->container['trackingRepository'] = fn() => new TrackingRepository();
		$this->container['configRepository'] = fn() => new ConfigRepository();
		$this->container['logRepository'] = fn( $c ) => new LogRepository( $c['configRepository'] );
		$this->container['prepostRepository'] = fn() => new PrepostRepository();

		$this->container['correiosApi'] = fn( $c ) => new CorreiosApi( $c['configRepository'] );
		$this->container['infixsApi'] = fn() => new InfixsApi();

		$this->container['correiosService'] = fn( $c ) => new CorreiosService( $c['correiosApi'], $c['configRepository'] );
		$this->container['trackingService'] = fn( $c ) => new TrackingService( $c['trackingRepository'] );
		$this->container['prepostService'] = fn( $c ) => new PrepostService( $c['prepostRepository'] );
		$this->container['orderService'] = fn() => new OrderService();
		$this->container['shippingService'] = fn( $c ) => new ShippingService( $c['correiosService'], $c['infixsApi'] );
		$this->container['emailService'] = fn() => new EmailService();
		$this->container['labelService'] = fn() => new LabelService();
	}

	/**
	 * Config Repository
	 * 
	 * @since 1.0.0
	 * @return ConfigRepository
	 */
	public static function configRepository() {
		return self::getInstance()->container['configRepository'];
	}

	/**
	 * Correios Service
	 * 
	 * @since 1.0.0
	 * @return CorreiosService
	 */
	public static function correiosService() {
		return self::getInstance()->container['correiosService'];
	}

	/**
	 * Tracking Service
	 * 
	 * @since 1.0.0
	 * @return TrackingService
	 */
	public static function trackingService() {
		return self::getInstance()->container['trackingService'];
	}

	/**
	 * Prepost Service
	 * 
	 * @since 1.0.0
	 * @return PrepostService
	 */
	public static function prepostService() {
		return self::getInstance()->container['prepostService'];
	}

	/**
	 * Label Service
	 * 
	 * @since 1.0.0
	 * @return LabelService
	 */
	public static function labelService() {
		return self::getInstance()->container['labelService'];
	}

	/**
	 * Order Service
	 * 
	 * @since 1.0.0
	 * @return OrderService
	 */
	public static function orderService() {
		return self::getInstance()->container['orderService'];
	}

	/**
	 * Shipping Service
	 * 
	 * @since 1.0.0
	 * @return ShippingService
	 */
	public static function shippingService() {
		return self::getInstance()->container['shippingService'];
	}

	public static function logRepository() {
		return self::getInstance()->container['logRepository'];
	}

	/**
	 * Email Service
	 * 
	 * @since 1.0.0
	 * @return EmailService
	 */
	public static function emailService() {
		return self::getInstance()->container['emailService'];
	}

	/**
	 * Infixs Api
	 * 
	 * @since 1.0.0
	 * 
	 * @return InfixsApi
	 */
	public static function infixsApi() {
		return self::getInstance()->container['infixsApi'];
	}

	/**
	 * Routes
	 * 
	 * @since 1.0.0
	 * @return RestRoutes
	 */
	public static function routes() {
		return self::getInstance()->container['routes'];
	}
}