<?php
//1) Verify order exists in open order list based on memo or order_id
//2) Return result of blockchain query for this order

require '../../config.php';
require '../../bts_lib.php';
require '../../functions.php';

// should only have one entry

$memo = $_POST['memo'];
$orderArray = getOrderFromOpenOrders($memo,'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);

$demo = FALSE;
if($demoMode === "1" || $demoMode === 1 || $demoMode === TRUE || $demoMode === "true")
{
	$demo = TRUE;
}
$response   = btsVerifyOpenOrders($orderArray, $accountName, $rpcUser, $rpcPass, $rpcPort, $hashSalt, $demo);

if(array_key_exists('error', $response))
{
	die(json_encode($response));
}
die(json_encode($response));
?>