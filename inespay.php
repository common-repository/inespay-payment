<?php

require_once(dirname(__FILE__) . '/lib/InespayApiPublic.php');

add_action('init', 'init_wc_inespay_payment_gateway', 0);

function init_wc_inespay_payment_gateway()
{

    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }


    class Inespay extends WC_Payment_Gateway
    {

        const PREFIX_SUBJECT_TRANSFER = 'INE-';

        /**
         * Constructor for the gateway.
         *
         * @access public
         * @return void
         */
        public function __construct()
        {
            global $woocommerce;

            $this->id = 'inespay';
            $this->icon = apply_filters('inespay_icon', plugins_url('/images/inespay.png', __FILE__));

            $this->has_fields = false;

            // Set up localisation
            $this->load_plugin_textdomain();

            $this->method_title = __('Bank Transfer PSD2', 'inespay');
            $this->method_description = '<img src="' . plugins_url('/images/logo_extendido_new.png', __FILE__) . '" alt="Transferencia Online" style="float:none; margin-bottom:20px;">';
            $this->method_description .= '- ' . __('You will be redirected to your bank to authorize the payment in real time.', 'inespay') . '<br><br>';

            // Load the form fields.
            $this->init_form_fields();

            // Load the settings.
            $this->init_settings();

            // Define user set variables
            $this->title = $this->method_title;
            $this->description = $this->method_description;
            $this->environment = $this->settings['INESPAY_ENVIRONMENT'];
            $this->api_key = $this->settings['INESPAY_API_KEY'];
            $this->api_token = $this->settings['INESPAY_API_TOKEN'];
            add_action('plugins_loaded', 'check_woocommerce_version');
            // Actions
            if (defined('WC_VERSION') && version_compare(WC_VERSION, '2.0', '<')) {
                // Check for gateway messages using WC 1.X format
                add_action('woocommerce_update_options_payment_gateways', array(&$this, 'process_admin_options'));
            } else {
                // Payment listener/API hook (WC 2.X)
                add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            }

            add_action('woocommerce_receipt_inespay', array($this, 'receipt_page')); //Add receipt page to default page with resume operation
            add_action('woocommerce_api_wc_gateway_inespay', array($this, 'call_back_from_inespay'));

            if (!$this->is_valid_for_use()) {
                $this->enabled = false;
            }
        }

        function call_back_from_inespay()
        {
            global $woocommerce;


            $error = true;
            $error_message = '';
            $order = null;

            if (!empty($_REQUEST)) {
                if (!empty($_POST) && (array_key_exists('dataReturn', $_POST)) && (array_key_exists('signatureDataReturn', $_POST))) {
                    @ob_clean();
                    $apiInespay = new InespayApiPublic();
                    $apiInespay->setApiKeyInespay($this->api_key);
                    $dataReturn = sanitize_text_field($_POST['dataReturn']);
                    $apiInespay->setDataReturn($dataReturn);

                    $signatureDataReturn = sanitize_text_field($_POST['signatureDataReturn']);
                    $apiInespay->setSignatureDataReturn($signatureDataReturn);
                    if ($apiInespay->isDataReturnValid()) {

                        $status = $apiInespay->getStatusFromDataReturn();
                        $reference = $apiInespay->getReferenceFromDataReturn();

                        //Remove prefix to get idCart from reference
                        $idCart = preg_replace('/^' . self::PREFIX_SUBJECT_TRANSFER . '/', '', $reference);
                        $idCart = (int)$idCart;
                        $order = new WC_Order($idCart);

                        if ($status == InespayApiBase::STATUS_CODE_OK) {

                            $error = false;
                            $order->payment_complete();
                            if (wp_redirect($this->get_return_url($order))) {
                                exit;
                            }

                        } else {
                            $error_message = __('No completed transaction', 'inespay');
                        }
                    } else {
                        $error_message = __('Signature no matching', 'inespay');
                    }
                } else {
                    $error_message = __('Invalid params', 'inespay');
                }

            } else {
                $error_message = __('Invalid paramteres', 'inespay');
            }

            if ($error) {
                wc_add_notice(__('Payment error:', 'inespay') . $error_message, 'error'); //Saca aviso en rojo

                if ($order != null) {
                    if (wp_redirect($order->get_cancel_order_url())) {
                        exit;
                    }
                }
            }
        }

        /**
         * Localisation.
         *
         * @access public
         * @return void
         */
        function load_plugin_textdomain()
        {

            $locale = apply_filters('plugin_locale', get_locale(), 'woocommerce');
            $variable_lang = (get_option('woocommerce_informal_localisation_type') == 'yes') ? 'informal' : 'formal';

            load_textdomain('inespay', WP_LANG_DIR . '/inespay/inespay-' . $locale . '.mo');
            load_plugin_textdomain('inespay', false, dirname(plugin_basename(__FILE__)) . '/languages/' . $variable_lang);
            load_plugin_textdomain('inespay', false, dirname(plugin_basename(__FILE__)) . '/languages');
        }

        /**
         * Check if this gateway is enabled and available in the user's country
         *
         * @access public
         * @return bool
         */
        function is_valid_for_use()
        {
            $available_currencies = array('EUR');

            return in_array(get_woocommerce_currency(), apply_filters('woocommerce_inespay_supported_currencies', $available_currencies));
        }

        /**
         * Admin Panel Options
         * - Options for bits like 'title' and availability on a country-by-country basis
         *
         * @since 1.0.0
         */
        public function admin_options()
        {
            ?>˘

            <h3><?php _e('Inespay', 'inespay'); ?></h3>
            <p>
                <strong><?php _e('The new way to accept payments by bank transfer. Authorized and supervised by Bank of Spain.', 'inespay'); ?></strong>
            </p>
            <p><?php _e('Redirect your customer to their bank to authorize a transfer in real time.', 'inespay'); ?></p>
            <p><?php _e('Instant notification to confirm payment to your customer and release orders without delay.', 'inespay'); ?></p>
            <p><?php _e('Sign up and get your credentials at <a href="https://www.transferenciabancariapsd2.com/" target="_blank"> www.transferenciabancariapsd2.com </a>', 'inespay'); ?> <a href="https://clients.inespay.com/build/signup" target="_blank"> https://clients.inespay.com/build/signup</a></p>

            <?php if ($this->is_valid_for_use()) : ?>
            <table class="form-table">
                <?php
                // Generate the HTML For the settings form.
                $this->generate_settings_html();
                ?>
            </table>
            <!--/.form-table-->
        <?php else : ?>
            <div class="inline error">
                <p>
                    <strong><?php _e('Gateway Disabled', 'inespay'); ?></strong>: <?php _e('inespay does not support your store currency.', 'inespay'); ?>
                </p>
            </div>
        <?php
        endif;
        }

        /**
         * Initialise Gateway Settings Form Fields
         *
         * @access public
         * @return void
         */
        function init_form_fields()
        {

            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'inespay'),
                    'type' => 'checkbox',
                    'description' => __('Enable/Disable payment method.', 'inespay'),
                    'desc_tip' => true,
                    'label' => __('Enable INESPAY', 'inespay'),
                    'default' => 'yes'
                ),
                'INESPAY_ENVIRONMENT' => array(
                    'title' => __('Environment', 'inespay'),
                    'type' => 'select',
                    'label' => __('Mode', 'inespay'),
                    'options' => array(
                        'R' => __('Real', 'inespay'),
                        'T' => __('Test/Sandbox', 'inespay'),

                    ),
                    'description' => __('To use this payment method in your production online store, you must use the Real Environment credentials.', 'inespay'),
                    'desc_tip' => true,
                    'default' => 'T'
                ),

                'INESPAY_API_KEY' => array(
                    'title' => __('API Key', 'inespay'),
                    'type' => 'text',
                    'description' => __('Paste here the Key API provided by INESPAY', 'inespay'),
                    'desc_tip' => true,
                    'default' => __('', 'inespay')
                ),
                'INESPAY_API_TOKEN' => array(
                    'title' => __('API Token', 'inespay'),
                    'type' => 'textarea',
                    'description' => __('Paste here the Token API provided by INESPAY', 'inespay'),
                    'desc_tip' => true,
                    'default' => __('', 'inespay')
                )
            );
        }

        /**
         * Generate the inespay URL
         *
         * @access public
         * @param mixed $order_id
         * @return string
         */
        function generate_inespay_url($order_id)
        {
            global $woocommerce;

            $order = new WC_Order($order_id);
            $errorUrl = false;

            $environmentApiInespay = InespayApiPublic::ENV_PRO; //Production
            if ($this->environment == 'T') {
                $environmentApiInespay = InespayApiPublic::ENV_SAN; //Sandbox
            }
            $apiInespay = new InespayApiPublic();
            $apiInespay->setEnvironmentInespay($environmentApiInespay);
            $apiInespay->setApiKeyInespay($this->api_key);
            $apiInespay->setTokenInespay($this->api_token);

            if (defined('WC_VERSION') && version_compare(WC_VERSION, '2.7', '<')) {
                $internalReference = $order->id; // Usa $order->id si la versión es menor a 2.7
            } else {
                $internalReference = $order->get_id(); // Usa $order->get_id() si la versión es 2.7 o mayor
            }
            $reference = self::PREFIX_SUBJECT_TRANSFER . $internalReference;
            $subject = $reference;

            $totalAmount = $order->get_total();
            $protocolsAccepted = array("https", "http");
            $urlOkError = esc_url(add_query_arg('wc-api', 'wc_gateway_inespay', home_url('/')), $protocolsAccepted);


            $singleInitRequest = new SingleInitRequest();
            $singleInitRequest->setAmount($totalAmount); //Importe 2 decimales separados por .
            $singleInitRequest->setDescription($subject); //Concepto del pago
            $singleInitRequest->setReference($internalReference); //Identificador interno del pago

            $singleInitRequest->setSuccessLinkRedirect($urlOkError);
            $singleInitRequest->setSuccessLinkRedirectMethod('POST');
            $singleInitRequest->setNotifUrl($urlOkError);

            $singleInitRequest->setAbortLinkRedirect($urlOkError);
            $singleInitRequest->setAbortLinkRedirectMethod('POST');

            $response = $apiInespay->generateSimplePaymentUrl($singleInitRequest); //Llamada síncrona Api Inespay

            //Success url INESPAY
            if ($response->getStatus() == InespayApiBase::STATUS_CODE_SUCCESS) {

                $urlInespay = esc_url($response->getSinglePayinLink(), $protocolsAccepted);
                return '<button class="button" onclick="window.location.href=\'' . $urlInespay . '\'">' . __('Make payment', 'inespay') . '</button>';

            } else {
                //Error generación url
                $errorUrl = true;
                $typeError = $this->checkErrorDomain($response);
                $error_message = __('Error generating URL', 'inespay');
                if ('P406' === $typeError) {
                    $error_message = parse_url($_SERVER['HTTP_HOST'], PHP_URL_HOST) . ' ' . __('Error domain is not in the list', 'inespay');
                }
            }

            //Show error if exist
            if ($errorUrl) {
                wc_add_notice(__('Connection error', 'inespay') . ':' . $error_message, 'error');
                return '<h5>' . $error_message . '</h5>';
            }
        }

        private function checkErrorDomain(SingleInitResponse $response)
        {
            $message = 'does not belong to any authorized domain, please add the domain to the Inespay dashboard.';
            if ($response->getStatusDesc() && strpos($response->getStatusDesc(), $message) !== false) {
                return 'P406';
            } else {
                return null;
            }
        }

        /**
         * Process the payment and return the result
         *
         * @access public
         * @param int $order_id
         * @return array
         */
        function process_payment($order_id)
        {

            $order = new WC_Order($order_id);

            if (defined('WC_VERSION') && version_compare(WC_VERSION, '2.1', '<')) {
                $redirect_url = add_query_arg('order', $order->get_id(), add_query_arg('key', $order->get_order_key(), get_permalink(woocommerce_get_page_id('pay'))));
            } else {
                $redirect_url = $order->get_checkout_payment_url(true);
            }

            return array(
                'result' => 'success',
                'redirect' => $redirect_url
            );
        }

        /**
         * Output for the order received page.
         *
         * @access public
         * @return void
         */
        function receipt_page($order)
        {
            static $has_run = false; // Variable estática para controlar la ejecución

            if ($has_run) {
                return; // Si ya ha sido ejecutada, salir de la función
            }

            $has_run = true; // Marcar como ejecutada

            $receiptText = '<img src="' . plugins_url('/images/logo_extendido_new.png', __FILE__) . '" style=""> <br>';
            $receiptText .= '- ' . __('You will be redirected to your bank to authorize the payment in real time.', 'inespay') . '<br>';
            echo $receiptText;
            echo $this->generate_inespay_url($order);
        }

        /**
         * Get gateway icon.
         * @return string
         */
        public function get_icon()
        {
            $icon_html = '';

            return apply_filters('woocommerce_gateway_icon', $icon_html, $this->id);
        }

        public function get_return_url($order = null)
        {
            if ($order) {
                $return_url = $order->get_checkout_order_received_url();
            } else {
                $return_url = wc_get_endpoint_url('order-received', '', wc_get_page_permalink('checkout'));
            }

            if (is_ssl() || get_option('woocommerce_force_ssl_checkout') == 'yes') {
                $return_url = str_replace('http:', 'https:', $return_url);
            }

            return apply_filters('woocommerce_get_return_url', $return_url, $order);
        }
    }
}