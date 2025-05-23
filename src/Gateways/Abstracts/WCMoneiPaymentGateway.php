<?php

namespace Monei\Gateways\Abstracts;

use Exception;
use Monei\Services\ApiKeyService;
use Monei\Services\payment\MoneiPaymentServices;
use Monei\Services\PaymentMethodsService;
use Monei\Templates\TemplateManager;
use WC_Admin_Settings;
use WC_Monei_Logger;
use WC_Payment_Gateway;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract class that will be inherited by all payment methods.
 *
 * @extends WC_Payment_Gateway
 *
 * @since 5.0
 */
abstract class WCMoneiPaymentGateway extends WC_Payment_Gateway {

	const SALE_TRANSACTION_TYPE     = 'SALE';
	const PRE_AUTH_TRANSACTION_TYPE = 'AUTH';
	const VERIFY_TRANSACTION_TYPE   = 'VERIF';

	/**
	 * Is sandbox?
	 *
	 * @var bool
	 */
	public $testmode;

	/**
	 * Is debug active?
	 *
	 * @var bool
	 */
	public $debug;

	/**
	 * What to do after payment?. processing or completed.
	 *
	 * @var string
	 */
	public $status_after_payment;

	/**
	 * Hide Logo in checkout.
	 *
	 * @var bool
	 */
	public $hide_logo;

	/**
	 * Account ID.
	 *
	 * @var string
	 */
	public $account_id;

	/**
	 * API Key.
	 *
	 * @var string
	 */
	public $api_key;

	/**
	 * Shop Name.
	 *
	 * @var string
	 */
	public $shop_name;

	/**
	 * Password.
	 *
	 * @var string
	 */
	public $password;

	/**
	 * Enable Tokenization.
	 *
	 * @var bool
	 */
	public $tokenization;

	/**
	 * Enable Pre-Auth.
	 *
	 * @var bool
	 */
	public $pre_auth;

	/**
	 * Enable Debugging.
	 *
	 * @var bool
	 */
	public $logging;

	/**
	 * @var string
	 */
	public $notify_url;

	/**
	 * Form option fields.
	 *
	 * @var array
	 */
	public $form_fields = array();

	public PaymentMethodsService $paymentMethodsService;
	private TemplateManager $templateManager;
	private ApiKeyService $apiKeyService;
	protected MoneiPaymentServices $moneiPaymentServices;

	public function __construct(
		PaymentMethodsService $paymentMethodsService,
		TemplateManager $templateManager,
		ApiKeyService $apiKeyService,
		MoneiPaymentServices $moneiPaymentServices
	) {
		$this->paymentMethodsService = $paymentMethodsService;
		$this->templateManager       = $templateManager;
		$this->apiKeyService         = $apiKeyService;
		$this->moneiPaymentServices  = $moneiPaymentServices;
	}

	/**
	 * Check if this gateway is enabled and available in the user's country
	 * todo: check if the gateway is enabled in the user account
	 *
	 * @access public
	 * @return bool
	 */
	protected function is_valid_for_use() {
		if ( empty( $this->getAccountId() ) || empty( $this->getApiKey() ) ) {
			return false;
		}
		$methodAvailability = $this->paymentMethodsService->getMethodAvailability( $this->id );

		if ( ! $methodAvailability ) {
			return false;
		}

		if ( ! in_array( get_woocommerce_currency(), array( 'EUR', 'USD', 'GBP' ), true ) ) {
			return false;
		} else {
			return true;
		}
	}

	public function is_available() {
		$isEnabled      = $this->enabled === 'yes' && $this->is_valid_for_use();
		$billingCountry = WC()->customer && ! empty( WC()->customer->get_billing_country() )
			? WC()->customer->get_billing_country()
			: wc_get_base_location()['country'];

		$methodAvailability = $this->paymentMethodsService->getMethodAvailability( $this->id );

		return $isEnabled &&
			( empty( $methodAvailability['countries'] ) || in_array( $billingCountry, $methodAvailability['countries'], true ) );
	}

	/**
	 * Override the get_icon method to add a custom class to the icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		$output = $this->icon ?: '';
		return apply_filters( 'woocommerce_gateway_icon', $output, $this->id );
	}


	/**
	 * Admin Panel Options
	 *
	 * @access public
	 * @since 5.0
	 * @return void
	 */
	public function admin_options() {
		if ( $this->is_valid_for_use() ) {
			parent::admin_options();
		} else {
			if ( ! $this->getAccountId() || ! $this->getApiKey() ) {
				$template = $this->templateManager->getTemplate( 'notice-admin-gateway-not-available-api' );
				if ( $template ) {
					$template->render( array() );
				}
				return;
			}
			$methodAvailability = $this->paymentMethodsService->getMethodAvailability( $this->id, $this->getAccountId() );
			if ( ! $methodAvailability ) {
				$template = $this->templateManager->getTemplate( 'notice-admin-gateway-not-enabled-monei' );
				if ( $template ) {
					$template->render( array() );
				}
				return;
			}
			$template = $this->templateManager->getTemplate( 'notice-admin-gateway-not-available' );
			if ( $template ) {
				$template->render( array() );
			}
		}
	}

	/**
	 * @param int    $order_id
	 * @param null   $amount
	 * @param string $reason
	 *
	 * @return bool
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return false;
		}

		if ( ! $amount ) {
			$amount = $order->get_total();
		}

		$payment_id = $order->get_meta( '_payment_order_number_monei', true );

		try {

			$result = $this->moneiPaymentServices->refund_payment( $payment_id, monei_price_format( $amount ) );

			if ( 'REFUNDED' === $result->getStatus() || 'PARTIALLY_REFUNDED' === $result->getStatus() ) {

				$this->log( $amount . ' Refund approved.', 'debug' );

				$order->add_order_note( __( 'MONEI Refund Approved:', 'monei' ) . wc_price( $amount ) . '<br/>Status: ' . $result->getStatus() . ' ' . $result->getStatusMessage() );

				return true;

			}
		} catch ( Exception $e ) {
			$this->log( 'Refund error: ' . $e->getMessage(), 'error' );
			$order->add_order_note( __( 'Refund error: ', 'monei' ) . $e->getMessage() );
		}
		return false;
	}

	/**
	 * Checbox to save CC on checkout.
	 */
	public function save_payment_method_checkbox() {
		printf(
			'<p class="form-row woocommerce-SavedPaymentMethods-saveNew">
				<input id="wc-%1$s-new-payment-method" name="wc-%1$s-new-payment-method" type="checkbox" value="true" style="width:auto;" />
				<label for="wc-%1$s-new-payment-method" style="display:inline;">%2$s</label>
			</p>',
			esc_attr( $this->id ),
			esc_html( apply_filters( 'wc_monei_save_to_account_text', __( 'Save payment information to my account for future purchases.', 'monei' ) ) )
		);
	}

	/**
	 * If user has selected a saved payment method, we will return it's id.
	 *
	 * @return int|false
	 */
	protected function get_payment_token_id_if_selected() {
        //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		return ( isset( $_POST[ 'wc-' . $this->id . '-payment-token' ] ) ) ? filter_var( wp_unslash( $_POST[ 'wc-' . $this->id . '-payment-token' ] ), FILTER_SANITIZE_NUMBER_INT ) : false; // WPCS: CSRF ok.
	}

	/**
	 * IF user has selected save payment method checkbox in checkout.
	 *
	 * @return bool
	 */
	protected function get_save_payment_card_checkbox() {
        //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		return ( isset( $_POST[ 'wc-' . $this->id . '-new-payment-method' ] ) );
	}

	/**
	 * On updated_checkout, we need thew new total cart in order to update cofidis plugin.
	 *
	 * @param array $fragments
	 *
	 * @return array
	 */
	protected function add_cart_total_fragments( $fragments ) {
		if ( ! WC()->cart ) {
			return $fragments;
		}

		$fragments['monei_new_total'] = monei_price_format( WC()->cart->get_total( false ) );
		return $fragments;
	}

	protected function log( $message, $level = 'debug' ) {
		if ( 'yes' === get_option( 'monei_debug' ) || 'error' === $level ) {
			WC_Monei_Logger::log( $message, $level );
		}
	}

	/**
	 * Setting checks when saving.
	 *
	 * @param $is_post
	 * @param $option string name of the option to enable/disable the method
	 * @return bool
	 */
	public function checks_before_save( $is_post, $option ) {
		if ( $is_post ) {
			// Check if API key is saved in general settings
			$api_key    = $this->getApiKey();
			$account_id = $this->getAccountId();
			if ( ! $api_key || ! $account_id ) {
				WC_Admin_Settings::add_error( __( 'MONEI needs an API Key in order to work. Disabling the gateway.', 'monei' ) );
				unset( $_POST[ $option ] );
			}
		}
		return $is_post;
	}

	public function getApiKey() {
		return $this->apiKeyService->get_api_key();
	}

	public function getAccountId() {
		return $this->apiKeyService->get_account_id();
	}

	public function getTestmode() {
		return $this->apiKeyService->is_test_mode();
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
	 * Frontend MONEI generated token.
	 *
	 * @return false|string
	 */
	public function get_frontend_generated_monei_token() {
        //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		return ( isset( $_POST['monei_payment_token'] ) ) ? wc_clean( wp_unslash( $_POST['monei_payment_token'] ) ) : false; // WPCS: CSRF ok.
	}

	/**
	 * @return float|int|string|null
	 */
	public function determineTheTotalAmountToBePassed() {
		$total = null;
        //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( is_wc_endpoint_url( 'order-pay' ) && isset( $_GET['key'] ) ) {
			// If on the pay for order page, get the order total
            //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$order_id = wc_get_order_id_by_order_key( wc_clean( wp_unslash( $_GET['key'] ) ) );
			if ( $order_id ) {
				$order = wc_get_order( $order_id );
				$total = $order ? $order->get_total() : 0;
			}
		} else {
			// Otherwise, use the cart total
			$total = WC()->cart->get_total( false );
		}
		return $total;
	}
}
