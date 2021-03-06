<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class DIBS_Requests_Get_DIBS_Order extends DIBS_Requests2 {

	public $payment_id;
	public $order_id;

	public function __construct( $payment_id, $order_id = null ) {
		$this->payment_id = $payment_id;
		$this->order_id   = $order_id;
		parent::__construct();
	}

	public function request() {
		$request_url = $this->endpoint . 'payments/' . $this->payment_id;

		$response = wp_remote_request( $request_url, $this->get_request_args() );

		DIBS_Easy::log( 'DIBS GET Order response (' . $request_url . '): ' . stripslashes_deep( json_encode( $response ) ) );

		if ( is_wp_error( $response ) ) {
			return $this->get_error_message( $response );
			// return 'ERROR';
		}

		if ( $response['response']['code'] >= 200 && $response['response']['code'] <= 299 ) {
			return json_decode( wp_remote_retrieve_body( $response ) );
		} else {
			return $this->get_error_message( $response );
			// return 'ERROR';
		}
	}

	public function get_request_args() {
		$request_args = array(
			'headers'    => $this->request_headers( $this->order_id ),
			'user-agent' => $this->request_user_agent(),
			'method'     => 'GET',
			'timeout'    => apply_filters( 'nets_easy_set_timeout', 10 ),
		);
		DIBS_Easy::log( 'DIBS Get Order request args: ' . stripslashes_deep( json_encode( $request_args ) ) );

		return $request_args;
	}
}
