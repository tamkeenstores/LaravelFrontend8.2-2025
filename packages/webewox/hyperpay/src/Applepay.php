<?php

namespace Webewox\Hyperpay;
use App;

class Applepay
{
    public function __construct($redirect = false, $lang = false)
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

        $this->testmode = config('applepay.testmode');
        $this->currency = config('applepay.currency');
        $this->title = config('applepay.title');
        $this->trans_type = config('applepay.trans_type');
        $this->trans_mode = config('applepay.trans_mode');
        $this->accesstoken = config('applepay.accesstoken');
        $this->entityid = config('applepay.entityId');
        $this->brands = config('applepay.applepaybrands');
        $this->connector_type = config('applepay.connector_type');
        $this->payment_style = config('applepay.payment_style');
        $this->mailerrors = 'no';
        $this->lang = $lang ? $lang : App::getLocale();
        $this->supportedNetworks = config('applepay.applepaysupportedNetworks');

        // $lang = explode('-', get_bloginfo('language'));
        // $lang = $lang[0];
        // $this->lang  = $lang;

        $this->tokenization = config('applepay.tokenization');

        $this->redirect_page_id = $redirect ? $redirect : '';


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
        return config('applepay.enabled');
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

        $orderAmount = number_format($data['amount'], 2, '.', '');

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


        


        // print_r($data);
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
            // print_r($resultJson); die();
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
            $supportedNetworks = json_encode($this->supportedNetworks);
            $postbackURL = $this->redirect_page_id;


            echo '<script>
                            var wpwlOptions = {


                                style:"' . $this->payment_style . '",
                                locale:"' . $this->lang . '",
                                paymentTarget: "_top",
                                applePay: {
                                    supportedNetworks: ' . $supportedNetworks . ',
                                    displayName: "Tamkeen",
                                    total: { label: "Tamkeen, INC." }
                                }
                            }
        
                    </script>';
                //if the lang is Arabic change the direction to ltr
                if ($this->lang == 'ar') {
                    echo '<style>
                            .wpwl-group{
                            local: "ar",
                            direction:ltr !important;
                            }
                          </style>';
                };
                echo '<style>
                .wpwl-form {
	margin: 0px;
	max-width: 100em;
}

.wpwl-apple-pay-button-white-with-line {
margin: auto;
border: 5px solid;
padding: 60px 0;
position: absolute;
top: 50%;
width: 100%;
left: 0;
right: 0;
}
	.wpwl-apple-pay-button{-webkit-appearance: -apple-pay-button !important;}
	.wpwl-group.wpwl-group-button.wpwl-clearfix {
text-align: center;
height: 100%;
position: relative;
width: 100%;
}

                </style>';
                // payment form
                echo '<script  src="' . $scriptURL . '"></script>';
                echo '<form action="' . $postbackURL . '" class="paymentWidgets" data-brands="'. $payment_brands .'">
                        </form>';
        }
    }

}