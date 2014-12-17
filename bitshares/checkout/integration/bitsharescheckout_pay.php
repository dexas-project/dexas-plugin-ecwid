<?php
//1) Recreate hash from post data to ensure it matches with hash passed in, if not send error message
//2) Create BTS URI, if it can't send error
//3) Send BTS URI
require '../../bts_lib.php';
require '../../config.php';
require '../../functions.php';
$return = array();

$memo = $_POST['memo'];
$order_id = $_POST['order_id'];
$total = $_POST['total'];
$asset = $_POST['asset'];
$trx_id = $_POST['trx_id'];
$amount = $_POST['amount'];

$memoHashSanity = btsCreateEHASH($accountName,$order_id, $total, $asset, $hashSalt);
$memoSanity = btsCreateMemo($memoHashSanity);
if ($memoSanity !== $memo) {
	$return['error'] = 'Invalid memo';
  echo json_encode($ret);
	die;
}
$response = btsCreateInvoice($accountName, $order_id, $total,$total, $asset, $memo);
if(array_key_exists('error', $response))
{
    error_log($response['error']);
    $return['error'] = $response['error'];
    echo json_encode($return);
    die("Error creating invoice");
}
else {	
    saveToOpenOrders($_POST, '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
    $return['url'] = $response['url'];
    echo json_encode($return);
    die;
}

?>
