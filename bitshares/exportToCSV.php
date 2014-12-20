<?php
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=exportTransactions.csv');
require 'config.php';
require 'bts_lib.php';
require 'functions.php';
$memo = $_REQUEST['memo'];
$orderArray = getOrderFromOpenOrders($memo,'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);

$demo = FALSE;
if($demoMode === "1" || $demoMode === 1 || $demoMode === TRUE || $demoMode === "true")
{
	$demo = TRUE;
}
$response   = btsVerifyOpenOrders($orderArray, $accountName, $rpcUser, $rpcPass, $rpcPort, $hashSalt, $demo);

// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// output the column headings
fputcsv($output, array('#', 'Transaction ID', 'Order ID', 'Amount'));
$count = 0;
foreach ($response as $responseOrder) {
	$count++;
	fputcsv($output, array($count, $responseOrder['trx_id'], $responseOrder['order_id'], $responseOrder['amount']));
}
if(count($response) <= 0)
{
	fputcsv($output, array('No transactions found!'));
}
?>