<?php
	return [
// 		'enabled' => 'yes',
// 		'lang' => 'en',
// 		'testmode' => '1', /*1 for test mode 0 for live mode*/
// 		'title' => 'Hyper Pay',
// 		'currency' => 'SAR',
// 		'trans_type' => 'DB',  /*DB for Debit mode PA for Pre-Authorization*/
// 		'connector_type' => 'VISA_ACP', /* VISA_ACP, MPGS */
// 		//'accesstoken' => 'OGFjOWE0Yzk3ODU5ZTQ4NTAxNzg1YTE0NGZiOTA1Yzh8NjdDdEdTeFRORQ==',
// 		//'entityId' => '8ac9a4c97859e48501785a14e87405d2',
// 		'accesstoken' => 'OGFjN2E0Y2E3ODAyZWQ4ZTAxNzgxODEzOWM5NjI2NDB8NlJYSkZxYmQ0Yg==',
// 		'entityId' => '8ac7a4ca7802ed8e01781813f004264a',
// 		'tokenization' => 'disable', /* disable, enable*/
// 		'brands' => 'VISA MASTER', /* VISA, MASTER, AMEX*/
// 		'stcpaybrands' => 'STC_PAY', /* VISA, MASTER, AMEX*/
// 		'hyperpaybrands' => 'HYPERPAY', /* VISA, MASTER, AMEX*/
// 		'hyperpaysupportedNetworks' => ['visa', 'masterCard'],
// 		'payment_style' => 'plain', /* plain, card*/



        'enabled' => 'yes',
		'lang' => 'en',
		'testmode' => '0', /*1 for test mode 0 for live mode*/
		'title' => 'Hyper Pay',
		'currency' => 'SAR',
		'trans_type' => 'DB',  /*DB for Debit mode PA for Pre-Authorization*/
		'connector_type' => 'VISA_ACP', /* VISA_ACP, MPGS */
		'accesstoken' => 'OGFjOWE0Yzk3ODU5ZTQ4NTAxNzg1YTE0NGZiOTA1Yzh8NjdDdEdTeFRORQ==',
		'entityId' => '8ac9a4c97859e48501785a14e87405d2',
		'tokenization' => 'disable', /* disable, enable*/
		'brands' => 'VISA MASTER', /* VISA, MASTER, AMEX*/
		'stcpaybrands' => 'STC_PAY', /* VISA, MASTER, AMEX*/
		'hyperpaybrands' => 'HYPERPAY', /* VISA, MASTER, AMEX*/
		'hyperpaysupportedNetworks' => ['visa', 'masterCard'],
		'payment_style' => 'plain', /* plain, card*/
	]

?>