<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that handle Monei Bizum Payment method.
 *
 * Class WC_Gateway_Monei_Bizum
 */
class WC_Gateway_Monei_Bizum extends WC_Monei_Payment_Gateway_Hosted {

	const PAYMENT_METHOD = 'bizum';

	/**
	 * Constructor for the gateway.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		$this->id = MONEI_GATEWAY_ID . '_bizum';
		$this->method_title  = __( 'MONEI - Bizum', 'monei' );
		$this->method_description = __( 'Accept Bizum payments.', 'monei' );
		$this->enabled = ( ! empty( $this->get_option( 'enabled' ) && 'yes' === $this->get_option( 'enabled' ) ) && $this->is_valid_for_use() ) ? 'yes' : false;

		// Load the form fields.
		$this->init_form_fields();
		// Load the settings.
		$this->init_settings();

		// Bizum Hosted payment with redirect.
		$this->has_fields = false;
		$iconUrl = apply_filters( 'woocommerce_monei_bizum_icon', WC_Monei()->image_url( 'bizum-logo.svg' ));
		$iconMarkup = '<img src="' . $iconUrl . '" alt="MONEI" class="monei-icons" />';
		// Settings variable
		$this->hide_logo            = ( ! empty( $this->get_option( 'hide_logo' ) && 'yes' === $this->get_option( 'hide_logo' ) ) ) ? true : false;
		$this->icon                 = ( $this->hide_logo ) ? '' : $iconMarkup;
		$this->title                = ( ! empty( $this->get_option( 'title' ) ) ) ? $this->get_option( 'title' ) : '';
		$this->description          = ( ! empty( $this->get_option( 'description' ) ) ) ? $this->get_option( 'description' ) : '';
		$this->status_after_payment = ( ! empty( $this->get_option( 'orderdo' ) ) ) ? $this->get_option( 'orderdo' ) : '';
		$this->api_key              = $this->getApiKey();
        $this->account_id           = $this->getAccountId();
        $this->shop_name            = get_bloginfo( 'name' );
		$this->logging              = ( ! empty( get_option( 'monei_debug' ) ) && 'yes' === get_option( 'monei_debug' ) ) ? true : false;

		// IPN callbacks
		$this->notify_url           = WC_Monei()->get_ipn_url();
		new WC_Monei_IPN($this->logging);

		$this->supports             = array(
			'products',
			'refunds',
		);

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_filter(
            'woocommerce_save_settings_checkout_' . $this->id,
            function ($is_post) {
                return $this->checks_before_save($is_post, 'woocommerce_monei_bizum_enabled');
            }
        );
    }

	/**
	 * Initialise Gateway Settings Form Fields
	 *
	 * @access public
	 * @since 5.0
	 * @return void
	 */
	public function init_form_fields() {
        $this->form_fields = require WC_Monei()->plugin_path() . '/includes/admin/monei-bizum-settings.php';
	}

	/**
	 * Process the payment and return the result
	 *
	 * @access public
	 * @param int $order_id
     * @param null|string $allowed_payment_method
	 * @return array
	 */
    public function process_payment( $order_id, $allowed_payment_method = null ) {
		return parent::process_payment( $order_id, self::PAYMENT_METHOD );
	}
}

