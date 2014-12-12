<?php

function debuglog($contents)
{
	error_log($contents);
}

function postToEcwid($notice)
{
	require 'config.php';
	
	$x_response_code = '1'; // 1=approved, 2=declined
	$x_response_reason_code = '1'; // 1=approved, 2= declined
	$x_trans_id = $notice['trxId'];
	$x_invoice_num = $notice['order_id']; 
	$x_amount = $notice['amountReceived'];
  $x_url = $notice['url'];
  $x_total = $notice['total'];
	$string = $hashValue.$login.$x_trans_id.$x_total;
  
	$x_MD5_Hash = md5($string);
	$datatopost = array (
		"x_response_code" => $x_response_code,
		"x_response_reason_code" => $x_response_reason_code,
		"x_trans_id" => $x_trans_id,
		"x_invoice_num" => $x_invoice_num,
		"x_amount" => $x_amount,
		"x_MD5_Hash" => $x_MD5_Hash,
		);

	$ch = curl_init($x_url);

	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $datatopost);
	//curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	$response = curl_exec($ch);
	if ($response === false){
		debuglog('request to ecwid.com failed');
		debuglog(curl_error($ch));
	}
			
	curl_close($ch);
	return $response;

}
?>
