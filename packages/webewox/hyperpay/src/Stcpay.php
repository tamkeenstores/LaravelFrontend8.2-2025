<?php

namespace Webewox\Hyperpay;

class Stcpay
{
    public function __construct()
    {
        $this->id = 'hyperpay';
        $this->has_fields = false;
        $this->method_title = 'Hyperpay Gateway';
        $this->method_description = 'Hyperpay Plugin for Woocommerce';

        // $this->init_form_fields();
        // $this->init_settings();

        $this->script_url = "https://eu-prod.oppwa.com/v1/paymentWidgets.js?checkoutId=";
        $this->token_url = "https://eu-prod.oppwa.com/v1/checkouts";
        $this->transaction_status_url = "https://eu-prod.oppwa.com/v1/checkouts/##TOKEN##/payment";
        $this->script_url_test = "https://test.oppwa.com/v1/paymentWidgets.js?checkoutId=";
        $this->token_url_test = "https://test.oppwa.com/v1/checkouts";
        $this->transaction_status_url_test = "https://test.oppwa.com/v1/checkouts/##TOKEN##/payment";

        $this->testmode = config('stcpay.testmode');
        $this->currency = config('stcpay.currency');
        $this->title = config('stcpay.title');
        $this->trans_type = config('stcpay.trans_type');
        $this->trans_mode = config('stcpay.trans_mode');
        $this->accesstoken = config('stcpay.accesstoken');
        $this->entityid = config('stcpay.entityId');
        $this->brands = config('stcpay.stcpaybrands');
        $this->connector_type = config('stcpay.connector_type');
        $this->payment_style = config('stcpay.payment_style');
        $this->mailerrors = 'no';
        $this->lang = config('stcpay.lang');

        // $lang = explode('-', get_bloginfo('language'));
        // $lang = $lang[0];
        // $this->lang  = $lang;

        $this->tokenization = config('stcpay.tokenization');

        $this->redirect_page_id = route('payment-response');


        if ($this->lang == 'ar') {
            $this->failed_message = 'تم رفض العملية ';

            $this->success_message = 'تم إجراء عملية الدفع بنجاح.';
        } else {
            $this->failed_message = 'Your transaction has been declined.';

            $this->success_message = 'Your payment has been processed successfully.';
        }
        $this->msg['message'] = "";
        $this->msg['class'] = "";
    }

    public function name()
    {
        return config('stcpay.enabled');
    }

    function isThisEnglishText($text)
    {
        return preg_match("/\p{Latin}+/u", $text);
    }

    public function token($data)
    {
        if ($this->testmode == 0) {
            $url = $this->token_url;
        } else {
            $url = $this->token_url_test;
        }

        $orderAmount = number_format(100, 2, '.', '');

        $orderid = $data['id'];

        $accesstoken = $this->accesstoken;
        $entityid = $this->entityid;
        $mode = $this->trans_mode;
        $type = $this->trans_type;
        $amount = number_format(round($orderAmount, 2), 2, '.', '');
        $currency = $this->currency;
        $transactionID = $orderid;
        $firstName = $data['firstName'];
        $family = $data['lastname'];
        $street = $data['address'];
        $zip = $data['zip'];
        $city = $data['city'];
        $state = $data['state'];
        $country = $data['country'];
        $email = $data['email'];

        $firstName = preg_replace('/\s/', '', str_replace("&", "", $firstName));
        $family = preg_replace('/\s/', '', str_replace("&", "", $family));
        $street = preg_replace('/\s/', '', str_replace("&", "", $street));
        $city = preg_replace('/\s/', '', str_replace("&", "", $city));
        $state = preg_replace('/\s/', '', str_replace("&", "", $state));          
        $country = preg_replace('/\s/', '', str_replace("&", "", $country));          

        if (empty($state)) {
            $state = $city;
        }

        $data = "entityId=$entityid" .
            "&amount=$amount" .
            "&currency=$currency" .
            "&paymentType=$type" .
            "&merchantTransactionId=$transactionID" .
            "&customer.email=$email";

        if ($mode == 'CONNECTOR_TEST') {
            $data .= "&testMode=EXTERNAL";
        }

        if (!($this->connector_type == 'MPGS' && $this->isThisEnglishText($firstName) == false)) {
            $data .= "&customer.givenName=" . $firstName;
        }

        if (!($this->connector_type == 'MPGS' && $this->isThisEnglishText($family) == false)) {
            $data .= "&customer.surname=" . $family;
        }

        if (!($this->connector_type == 'MPGS' && $this->isThisEnglishText($street) == false)) {
            $data .= "&billing.street1=" . $street;
        }

        if (!($this->connector_type == 'MPGS' && $this->isThisEnglishText($city) == false)) {
            $data .= "&billing.city=" . $city;
        }

        if (!($this->connector_type == 'MPGS' && $this->isThisEnglishText($state) == false)) {
            $data .= "&billing.state=" . $state;
        }

        if (!($this->connector_type == 'MPGS' && $this->isThisEnglishText($country) == false)) {
            $data .= "&billing.country=" . $country;
        }

        $data .= "&customParameters[branch_id]=1";
        $data .= "&customParameters[teller_id]=1";
        $data .= "&customParameters[device_id]=1";
        $data .= "&customParameters[bill_number]=$transactionID";


        



        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization:Bearer ' . $accesstoken
        ));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        // print_r($response);
        if (curl_errno($ch)) {
            echo "Problem with $url, $php_errormsg";
            // wc_add_notice(__('Hyperpay error:', 'woocommerce') . "Problem with $url, $php_errormsg", 'error');
        }
        curl_close($ch);
        if ($response === false) {
            echo "Problem reading data from $url, $php_errormsg";
            // wc_add_notice(__('Hyperpay error:', 'woocommerce') . "Problem reading data from $url, $php_errormsg", 'error');
        }

        $result = json_decode($response);


        $token = '';

        if (isset($result->id)) {
            $token = $result->id;
        }
        return $token;
        // echo $token;
    }

    public function checkStatus($id)
    {
        if (isset($id)) {
            $token = $id;

            if ($this->testmode == 0) {
                $url = $this->transaction_status_url;
            } else {
                $url = $this->transaction_status_url_test;
            }

            $url = str_replace('##TOKEN##', $token, $url);
            $url .= "?entityId=" . $this->entityid;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization:Bearer ' . $this->accesstoken
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $resultPayment = curl_exec($ch);
            curl_close($ch);
            $resultJson = json_decode($resultPayment, true);
            
            $sccuess = 0;
            $failed_msg = '';

            if (isset($resultJson['result']['code'])) {
                $successCodePattern = '/^(000\.000\.|000\.100\.1|000\.[36])/';
                $successManualReviewCodePattern = '/^(000\.400\.0|000\.400\.100)/';
                //success status
                if (preg_match($successCodePattern, $resultJson['result']['code']) || preg_match($successManualReviewCodePattern, $resultJson['result']['code'])) {
                    $sccuess = 1;
                } else {
                    //fail case
                    $failed_msg = $resultJson['result']['description'];

                }
            }

            return ['success' =>$sccuess, 'msg' => $failed_msg];
        }
    }


    public function renderPaymentForm($order, $token = '')
    {
        if ($token) {
            $token = $token;

            $order_id = $order;

            if ($this->testmode == 0) {
                $scriptURL = $this->script_url;
            } else {
                $scriptURL = $this->script_url_test;
            }

            $scriptURL .= $token;

            $payment_brands = $this->brands;

            $postbackURL = $this->redirect_page_id;


            echo '<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.4.1.min.js"></script>';
        ?>
            <script>
                var wpwlOptions = {

                    onReady: function() {
                        <?php
                        if ($this->tokenization == 'enable') {
                        ?>

                            var storeMsg = 'Store payment details?';
                            var style = 'style="direction: ltr"';
                            if (wpwlOptions.locale == "ar") {
                                storeMsg = ' هل تريد حفظ معلومات البطاقة ؟';
                                style = 'style="direction: rtl"';
                            }
                            var createRegistrationHtml = '<div class="customLabel style ="' + style + '">' + storeMsg +
                                '</div><div class="customInput style ="' + style + '""><input type="checkbox" name="createRegistration" value="true" /></div>';
                            $('form.wpwl-form-card').find('.wpwl-button').before(createRegistrationHtml);
                        <?php } ?>



                        $('.wpwl-form-virtualAccount-STC_PAY .wpwl-wrapper-radio-qrcode').hide();
                        $('.wpwl-form-virtualAccount-STC_PAY .wpwl-wrapper-radio-mobile').hide();
                        $('.wpwl-form-virtualAccount-STC_PAY .wpwl-group-paymentMode').hide();
                        $('.wpwl-form-virtualAccount-STC_PAY .wpwl-group-mobilePhone').show();
                        $('.wpwl-form-virtualAccount-STC_PAY .wpwl-wrapper-radio-mobile .wpwl-control-radio-mobile').attr('checked', true);
                        $('.wpwl-form-virtualAccount-STC_PAY .wpwl-wrapper-radio-mobile .wpwl-control-radio-mobile').trigger('click');

                    },
                    "style": "<?php echo $this->payment_style  ?>",
                    "locale": "<?php echo  $this->lang ?>",
                    "paymentTarget": "_top",
                    "registrations": {
                        "hideInitialPaymentForms": "true",
                        "requireCvv": "true"
                    }




                }
            </script>

        <?php
            if ($this->lang == 'ar') {
                echo '<style>
            .wpwl-group{
            direction:ltr !important;
            }

          </style>';
            };

            echo '<script  src="' . $scriptURL . '"></script>';
            echo '<form action="' . $postbackURL . '" class="paymentWidgets" data-brands="'. $payment_brands .'">
                    </form>';
        }
    }

}