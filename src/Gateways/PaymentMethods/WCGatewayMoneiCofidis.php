<?php

namespace Monei\Gateways\PaymentMethods;

use Monei\Gateways\Abstracts\WCMoneiPaymentGatewayHosted;
use Monei\Services\ApiKeyService;
use Monei\Services\payment\MoneiPaymentServices;
use Monei\Services\PaymentMethodsService;
use Monei\Templates\TemplateManager;
use WC_Monei_IPN;
use WC_Monei_Payment_Gateway_Hosted;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that handle Monei Cofidis Payment method.
 *
 * Class WC_Gateway_Monei_Cofidis
 */
class WCGatewayMoneiCofidis extends WCMoneiPaymentGatewayHosted {


	const PAYMENT_METHOD = 'cofidis';

	/**
	 * Constructor for the gateway.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct(
		PaymentMethodsService $paymentMethodsService,
		TemplateManager $templateManager,
		ApiKeyService $apiKeyService,
		MoneiPaymentServices $moneiPaymentServices
	) {
		parent::__construct( $paymentMethodsService, $templateManager, $apiKeyService, $moneiPaymentServices );
		$this->id                 = MONEI_GATEWAY_ID . '_cofidis';
		$this->method_title       = __( 'MONEI - Cofidis', 'monei' );
		$this->method_description = __( 'Accept Cofidis payments.', 'monei' );
		$this->enabled            = ( ! empty( $this->get_option( 'enabled' ) && 'yes' === $this->get_option( 'enabled' ) ) && $this->is_valid_for_use() ) ? 'yes' : false;

		// Load the form fields.
		$this->init_form_fields();
		// Load the settings.
		$this->init_settings();

		// Cofidis Hosted payment with redirect.
		$this->has_fields = false;

		// Settings variable
		$this->hide_logo            = true;
		$this->icon                 = ''; // No logo
		$this->title                = __( 'Financia con Cofidis en 4 cuotas/90 días by MONEI', 'monei' ); // Hardcoded Title, asked by provider.
		$this->description          = ' '; // If description empty, payment_fields() does not render.
		$this->status_after_payment = ( ! empty( $this->get_option( 'orderdo' ) ) ) ? $this->get_option( 'orderdo' ) : '';
		$this->api_key              = ( ! empty( $this->get_option( 'apikey' ) ) ) ? $this->get_option( 'apikey' ) : '';
		$this->shop_name            = get_bloginfo( 'name' );
		$this->pre_auth             = ( ! empty( $this->get_option( 'pre-authorize' ) && 'yes' === $this->get_option( 'pre-authorize' ) ) ) ? true : false;
		$this->logging              = ( ! empty( $this->get_option( 'debug' ) ) && 'yes' === $this->get_option( 'debug' ) ) ? true : false;

		// IPN callbacks
		$this->notify_url = WC_Monei()->get_ipn_url();
		new WC_Monei_IPN( $this->logging );

		$this->supports = array(
			'products',
			'refunds',
		);

		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			array(
				$this,
				'process_admin_options',
			)
		);
		add_filter(
			'woocommerce_save_settings_checkout_' . $this->id,
			function ( $is_post ) {
				return $this->checks_before_save( $is_post, 'woocommerce_monei_cofidis_enabled' );
			}
		);
		add_action( 'wp_enqueue_scripts', array( $this, 'cofidis_scripts' ) );

		// Add new total on checkout updates (ex, selecting different shipping methods)
		add_filter(
			'woocommerce_update_order_review_fragments',
			function ( $fragments ) {
				return self::add_cart_total_fragments( $fragments );
			}
		);
	}

	/**
	 * Return whether or not this gateway still requires setup to function.
	 *
	 * When this gateway is toggled on via AJAX, if this returns true a
	 * redirect will occur to the settings page instead.
	 *
	 * @return bool
	 * @since 3.4.0
	 */
	public function needs_setup() {

		if ( ! $this->account_id || ! $this->api_key ) {
			return true;
		}

		return false;
	}

	/**
	 * Initialise Gateway Settings Form Fields
	 *
	 * @access public
	 * @return void
	 * @since 5.0
	 */
	public function init_form_fields() {
		$this->form_fields = require WC_Monei()->plugin_path() . '/includes/admin/monei-cofidis-settings.php';
	}

	/**
	 * Process the payment and return the result
	 *
	 * @access public
	 *
	 * @param int $order_id
	 * @param null|string $allowed_payment_method
	 *
	 * @return array
	 */
	public function process_payment( $order_id, $allowed_payment_method = null ) {
		return parent::process_payment( $order_id, self::PAYMENT_METHOD );
	}

	/**
	 * Payments fields, shown on checkout or payment method page (add payment method).
	 */
	public function payment_fields() {
		ob_start();
		if ( is_checkout() ) {
			echo esc_html( $this->description );
			$this->render_cofidis_widget();
		}
		ob_end_flush();
	}

	/**
	 * To add the widget to your cart or product page create a container element in the HTML file where the widget will be displayed.
	 * https://docs.monei.com/docs/guides/setup-cofidis-widget/
	 */
	protected function render_cofidis_widget() {
		?>
		<div id="cofidis_widget"></div>
		<?php
	}

	/**
	 * Registering MONEI JS library and plugin js.
	 * https://docs.monei.com/docs/guides/setup-cofidis-widget/
	 */
	public function cofidis_scripts() {

		if ( ! is_checkout() ) {
			return;
		}

		if ( 'no' === $this->enabled ) {
			return;
		}

		// If already enqueued (by other payment method) we do nothing.
		if ( ! wp_script_is( 'monei', 'enqueued' ) ) {
			wp_register_script( 'monei', 'https://js.monei.com/v2/monei.js', '', '1.0', true );
			wp_enqueue_script( 'monei' );
		}

		wp_register_script( 'woocommerce_monei_cofidis', plugins_url( 'public/js/monei-cofidis.min.js', MONEI_MAIN_FILE ), array( 'jquery', 'monei' ), MONEI_VERSION, true );
		wp_localize_script(
			'woocommerce_monei_cofidis',
			'wc_monei_cofidis_params',
			array(
				'account_id' => monei_get_settings( false, 'monei_accountid' ),
				'lang'       => ( 0 === strpos( get_locale(), 'en' ) ) ? 'en' : 'es',
				'total'      => monei_price_format( WC()->cart->get_total( false ) ),
			)
		);
		wp_enqueue_script( 'woocommerce_monei_cofidis' );
	}
}

