<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class DIBS_Requests_Checkout {

	public static function get_checkout( $checkout_flow = 'embedded', $order_id = null ) {
		$checkout = array(
			'termsUrl' => wc_get_page_permalink( 'terms' ),
		);
		if ( 'embedded' === $checkout_flow ) {
			$checkout['url']                                     = wc_get_checkout_url();
			$checkout['shipping']['countries']                   = array();
			$checkout['shipping']['merchantHandlesShippingCost'] = true;
		} else {
			$order                                   = wc_get_order( $order_id );
			$checkout['returnUrl']                   = $order->get_checkout_order_received_url();
			$checkout['integrationType']             = 'HostedPaymentPage';
			$checkout['merchantHandlesConsumerData'] = true;
			$checkout['shipping']['countries']       = array();
			$checkout['shipping']['merchantHandlesShippingCost'] = false;
			$checkout['consumer']                                = self::get_consumer_address( $order );
		}

		if ( 'all' !== get_option( 'woocommerce_allowed_countries' ) ) {
			$checkout['shipping']['countries'] = self::get_shipping_countries();
		}
		$dibs_settings          = get_option( 'woocommerce_dibs_easy_settings' );
		$allowed_customer_types = ( isset( $dibs_settings['allowed_customer_types'] ) ) ? $dibs_settings['allowed_customer_types'] : 'B2C';
		switch ( $allowed_customer_types ) {
			case 'B2C':
				$checkout['consumerType']['supportedTypes'] = array( 'B2C' );
				break;
			case 'B2B':
				$checkout['consumerType']['supportedTypes'] = array( 'B2B' );
				break;
			case 'B2CB':
				$checkout['consumerType']['supportedTypes'] = array( 'B2C', 'B2B' );
				$checkout['consumerType']['default']        = 'B2C';
				break;
			case 'B2BC':
				$checkout['consumerType']['supportedTypes'] = array( 'B2B', 'B2C' );
				$checkout['consumerType']['default']        = 'B2B';
				break;
			default:
				$checkout['consumerType']['supportedTypes'] = array( 'B2B' );
		} // End switch().

		return $checkout;
	}

	public static function get_shipping_countries() {
		$converted_countries      = array();
		$supported_dibs_countries = dibs_get_supported_countries();
		// Add shipping countries.
		$wc_countries = new WC_Countries();
		$countries    = array_keys( $wc_countries->get_allowed_countries() );

		foreach ( $countries as $country ) {
			$converted_country = dibs_get_iso_3_country( $country );
			if ( in_array( $converted_country, $supported_dibs_countries ) ) {
				$converted_countries[] = array( 'countryCode' => $converted_country );
			}
		}
		return $converted_countries;
	}

	public static function get_consumer_address( $order ) {
		$consumer                                    = array();
		$consumer['email']                           = $order->get_billing_email();
		$consumer['shippingAddress']['addressLine1'] = $order->get_shipping_address_1();
		$consumer['shippingAddress']['addressLine2'] = $order->get_shipping_address_2();
		$consumer['shippingAddress']['postalCode']   = $order->get_shipping_postcode();
		$consumer['shippingAddress']['city']         = $order->get_shipping_city();
		$consumer['shippingAddress']['country']      = dibs_get_iso_3_country( $order->get_shipping_country() );
		$consumer['phoneNumber']['prefix']           = '+47';
		$consumer['phoneNumber']['number']           = $order->get_billing_phone();
		$consumer['privatePerson']['firstName']      = $order->get_shipping_first_name();
		$consumer['privatePerson']['lastName']       = $order->get_shipping_last_name();
		return $consumer;
	}
}
