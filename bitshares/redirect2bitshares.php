<?php

require 'bts_lib.php';
require 'config.php';
require 'functions.php';
if ($_POST['x_login'] != $login) {
	debuglog('ecwid login does not match that found in config.php');
	die('invalid ecwid login');
}


$total = 	$_POST['x_amount'];
$order_id = $_POST['x_invoice_num'];

$asset = btsCurrencyToAsset($_POST['x_currency_code']);
$hash = btsCreateEHASH($accountName, $order_id, $total,$asset, $hashSalt);
$memo = btsCreateMemo($hash);
$openOrder = array(
    'order_id'     => $order_id,
    'total'     => $total,
    'memo'     => $memo,
    'asset'     => $asset
);
saveToOpenOrders($openOrder, '.'.DIRECTORY_SEPARATOR);
$post = array(
    'accountName'     => $accountName ,
    'orderId'     => $order_id ,
    'memo'     => $memo
);

$rbimg = 'checkout/img/robohash.png';
if(!file_exists($img))
{
  $rbUrl = 'http://robohash.org/'.$accountName.'?size=100x100';
  file_put_contents($rbimg, file_get_contents($rbUrl));
}    


$urlParams = '?';
$index = 0;
foreach ($post as $key => $value) {
	$index++;
	if($index > 1)
	{
		$urlParams .= '&';
	}
	$urlParams .= $key.'='.$value;
}
header('refresh:3;url=checkout/index.html'.$urlParams );
echo 'Redirecting to Bitshares payment gateway...';
$post = array(
    'responseCode'     => '1',
    'reasonCode'     => '1',
    'order_id'     => $order_id,
    'amount'     => 0,
    'total'     => $total,
    'trx_id'     => $memo,
    'url'     => $relayUrl
);
postToEcwid($post);
?>