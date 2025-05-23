<?php

namespace Monei\Gateways\Abstracts;

use Exception;
use WC_Geolocation;
use MoneiPaymentServices;
use WC_Order;
use WC_Payment_Tokens;
use WC_Monei_Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract class that will be inherited by all integrated components payment methods.
 * Class WC_Monei_Payment_Gateway_Component
 *
 * @extends WCMoneiPaymentGateway
 * @since 5.0
 */
abstract class WCMoneiPaymentGatewayComponent extends WCMoneiPaymentGateway {
	const APPLE_GOOGLE_ID = 'monei_apple_google';

	/**
	 * Process the payment and return the result
	 *
	 * @access public
	 * @param int    $order_id
	 * @param string $allowed_payment_method
	 * @return array
	 */
	public function process_payment( $order_id, $allowed_payment_method = null ) {
		$order   = new WC_Order( $order_id );
		$payload = $this->create_payload( $order, $allowed_payment_method );
		$payload = $this->handler === null || ! ( $this->handler->is_subscription_order( $order_id ) ) ? $payload : $this->handler->create_subscription_payload( $order, $allowed_payment_method, $payload );

		/**
		 * If payment is tokenized ( saved cc ) we just need to create_payment with token and everything will work fine.
		 * If payment is normal cc, we will do 2 steps.
		 * First Step: Create Payment without token.
		 * Second Step: Confirm Payment with Token and cardholderName.
		 * Strong CustomerAuthentication and PSD2 normative requires cardholder name to be sent for each transaction.
		 * See: https://docs.monei.com/docs/guides/send-cardholder-name/
		 */
		try {
			$create_payment = $this->moneiPaymentServices->create_payment( $payload );
			do_action( 'wc_gateway_monei_create_payment_success', $payload, $create_payment, $order );

			$this->log( 'WC_Monei_API::create_payment ' . $allowed_payment_method, 'debug' );
			$this->log( $payload, 'debug' );
			$this->log( $create_payment, 'debug' );

			$confirm_payment = false;
			// We need to return the payment ID to the frontend and confirm payment there if we arrive from block checkout
			// and when we are not in redirect flow (component cc), but user didn't choose any tokenized saved method
			if ( $this->isBlockCheckout() && ! $this->redirect_flow && ! isset( $payload['paymentToken'] ) ) {
				return array(
					'result'      => 'success',
					'redirect'    => false,
					'paymentId'   => $create_payment->getId(), // Send the paymentId back to the client
					'token'       => $this->get_frontend_generated_monei_token(), // Send the token back to the client
					'completeUrl' => $payload['completeUrl'],
					'failUrl'     => $payload['failUrl'],
					'orderId'     => $order_id,
				);
			}

			// We need to confirm payment, when we are not in redirect flow (component cc), but user didn't choose any tokenized saved method.
			if ( ! $this->redirect_flow && ! isset( $payload['paymentToken'] ) ) {
				// We do 2 steps, in order to confirm card holder Name in the second step.
				$confirm_payload = array(
					'paymentToken'  => $this->get_frontend_generated_monei_token(),
					'paymentMethod' => array(
						'card' => array(
							'cardholderName' => $this->get_frontend_generated_monei_cardholder( $order ),
						),
					),
				);

				$confirm_payment = $this->moneiPaymentServices->confirm_payment( $create_payment->getId(), $confirm_payload );
				do_action( 'wc_gateway_monei_confirm_payment_success', $confirm_payload, $confirm_payment, $order );

				$this->log( 'WC_Monei_API::confirm_payment ' . $allowed_payment_method, 'debug' );
				$this->log( $create_payment->getId(), 'debug' );
				$this->log( $confirm_payload, 'debug' );
				$this->log( $confirm_payment, 'debug' );
			}

			/**
			 * Depends if we came in 1 step or 2.
			 */
			$next_action_redirect = ( $confirm_payment ) ? $confirm_payment->getNextAction()->getRedirectUrl() : $create_payment->getNextAction()->getRedirectUrl();
			return array(
				'result'   => 'success',
				'redirect' => $next_action_redirect,
			);

		} catch ( Exception $e ) {
			do_action( 'wc_gateway_monei_process_payment_error', $e, $order );
			// Extract and log the responseBody message
			$response_body = json_decode( $e->getResponseBody(), true );
			if ( isset( $response_body['message'] ) ) {
				WC_Monei_Logger::log( $response_body['message'], 'error' );
				wc_add_notice( $response_body['message'], 'error' );
				return array(
					'result' => 'failure',
				);
			}
			WC_Monei_Logger::log( $e->getMessage(), 'error' );
			wc_add_notice( $e->getMessage(), 'error' );
			return array(
				'result' => 'failure',
			);
		}
	}

	/**
	 * Payload creation.
	 *
	 * @param $order
	 * @param null|string $allowed_payment_method
	 *
	 * @return array
	 */
	public function create_payload( $order, $allowed_payment_method = null ) {
		$order_id    = $order->get_id();
		$amount      = monei_price_format( $order->get_total() );
		$currency    = get_woocommerce_currency();
		$user_email  = $order->get_billing_email();
		$description = $this->shop_name . ' - #' . $order_id;

		/**
		 * The URL to which a payment result should be sent asynchronously.
		 */
		$callback_url = wp_sanitize_redirect( esc_url_raw( $this->notify_url ) );
		/**
		 * The URL the customer will be directed to if the payment failed.
		 */
		$fail_url = esc_url_raw( $order->get_checkout_payment_url( false ) );
		/**
		 * The URL the customer will be directed to after transaction completed (successful or failed).
		 */
		$complete_url = wp_sanitize_redirect( esc_url_raw( add_query_arg( 'utm_nooverride', '1', $this->get_return_url( $order ) ) ) );

		/**
		 * Create Payment Payload
		 */
		$payload = array(
			'amount'                => $amount,
			'currency'              => $currency,
			'orderId'               => (string) $order_id,
			'description'           => $description,
			'customer'              => array(
				'email' => $user_email,
				'name'  => $order->get_formatted_billing_full_name(),
				'phone' => ( $order->get_billing_phone() ) ?: null,
			),
			'callbackUrl'           => $callback_url,
			'completeUrl'           => $complete_url,
			'cancelUrl'             => wc_get_checkout_url(),
			'failUrl'               => $fail_url,
			'transactionType'       => ( $this->pre_auth ) ? self::PRE_AUTH_TRANSACTION_TYPE : self::SALE_TRANSACTION_TYPE,
			'sessionDetails'        => array(
				'ip'        => WC_Geolocation::get_ip_address(),
				'userAgent' => wc_get_user_agent(),
			),
			'billingDetails'        => array(
				'name'    => ( $order->get_formatted_billing_full_name() ) ?: null,
				'email'   => ( $order->get_billing_email() ) ?: null,
				'phone'   => ( $order->get_billing_phone() ) ?: null,
				'company' => ( $order->get_billing_company() ) ?: null,
				'address' => array(
					'country' => ( $order->get_billing_country() ) ?: null,
					'city'    => ( $order->get_billing_city() ) ?: null,
					'line1'   => ( $order->get_billing_address_1() ) ?: null,
					'line2'   => ( $order->get_billing_address_2() ) ?: null,
					'zip'     => ( $order->get_billing_postcode() ) ?? null,
					'state'   => ( $order->get_billing_state() ) ?: null,
				),
			),
			'shippingDetails'       => array(
				'name'    => ( $order->get_formatted_shipping_full_name() ) ?: null,
				'email'   => $user_email,
				'phone'   => ( $order->get_shipping_phone() ) ?: null,
				'company' => ( $order->get_shipping_company() ) ?: null,
				'address' => array(
					'country' => ( $order->get_shipping_country() ) ?: null,
					'city'    => ( $order->get_shipping_city() ) ?: null,
					'line1'   => ( $order->get_shipping_address_1() ) ?: null,
					'line2'   => ( $order->get_shipping_address_2() ) ?: null,
					'zip'     => ( $order->get_shipping_postcode() ) ?: null,
					'state'   => ( $order->get_shipping_state() ) ?: null,
				),
			),
			'allowedPaymentMethods' => array( $allowed_payment_method ),
		);

		// If customer has selected a saved payment method, we get the token from $_POST and we add it to the payload.
		$token_id = $this->get_payment_token_id_if_selected();
		if ( $token_id ) {
			$wc_token                = WC_Payment_Tokens::get( $token_id );
			$payload['paymentToken'] = $wc_token->get_token();
		}

		// If user has paid using Apple or Google pay, we add paymentToken.
		// This will overwrite previous token, in case one preselected token was checked in checkout, but we should ignore it.
		$token_id = $this->get_frontend_generated_monei_apple_google_token();
		if ( $token_id ) {
			$payload['paymentToken'] = $token_id;
		}

		// If customer has checkboxed "Save payment information to my account for future purchases."
		if ( $this->tokenization && $this->get_save_payment_card_checkbox() ) {
			$payload['generatePaymentToken'] = true;
		}
		$componentGateways = array( MONEI_GATEWAY_ID, self::APPLE_GOOGLE_ID );
		// If merchant is not using redirect flow (means component CC or apple/google pay), there is a generated frontend token paymentToken and we need to add session ID to the request.
		if ( in_array( $this->id, $componentGateways, true ) && ! $this->redirect_flow && ( $this->get_frontend_generated_monei_token() || $this->get_frontend_generated_monei_apple_google_token() ) ) {
			$payload['sessionId'] = (string) WC()->session->get_customer_id();
		}

		$payload = apply_filters( 'wc_gateway_monei_create_payload', $payload );
		return $payload;
	}

	/**
	 * Frontend MONEI generated token.
	 *
	 * @return false|string
	 */
	public function get_frontend_generated_monei_token() {
        //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		return ( isset( $_POST['monei_payment_token'] ) ) ? wc_clean( wp_unslash( $_POST['monei_payment_token'] ) ) : false; // WPCS: CSRF ok.
	}

	/**
	 * Frontend MONEI generated flag for block checkout processing.
	 *
	 * @return boolean
	 */
	public function isBlockCheckout() {
        //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		return ( isset( $_POST['monei_is_block_checkout'] ) ) ? wc_clean( wp_unslash( $_POST['monei_is_block_checkout'] ) ) === 'yes' : false; // WPCS: CSRF ok.
	}

	/**
	 * Frontend MONEI cardholderName.
	 *
	 * @return false|string
	 */
	public function get_frontend_generated_monei_cardholder( $order ) {
		$defaultName = $order->get_formatted_billing_full_name();
        //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		return ( isset( $_POST['monei_cardholder_name'] ) ) ? wc_clean( wp_unslash( $_POST['monei_cardholder_name'] ) ) : $defaultName; // WPCS: CSRF ok.
	}

	/**
	 * Frontend MONEI payment-request token generated when Apple or Google pay.
	 * https://docs.monei.com/docs/monei-js/payment-request/
	 *
	 * @return false|string
	 */
	protected function get_frontend_generated_monei_apple_google_token() {
        //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		return ( isset( $_POST['monei_payment_request_token'] ) ) ? wc_clean( wp_unslash( $_POST['monei_payment_request_token'] ) ) : false; // WPCS: CSRF ok.
	}
}
