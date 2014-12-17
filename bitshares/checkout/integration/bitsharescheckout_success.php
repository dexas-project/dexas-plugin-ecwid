<?php

//1) Verify order exists in open order list based on memo or order_id, if not send error message
//2) Recreate hash from post data to ensure it matches with hash passed in, if not send error message
//3) Verify order exists on blockchain and is fully paid, if not send error message
//4) Send success notification to ECWID checkout

require '../../config.php';
require '../../bts_lib.php';
require '../../functions.php';



$memo = $_POST['memo'];
$trx_id = $_POST['trx_id'];
$amount = $_POST['amount'];
$orderArray = getOrderFromOpenOrders($memo,'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
if(count($openOrderList) <= 0)
{
  $ret = array();
  $ret['error'] = 'Could not find this order in the system, please contact the system administrator';
  echo json_encode($ret);
  die;
}
$memoHashSanity = btsCreateEHASH($accountName,$orderArray[0]['order_id'], $orderArray[0]['total'], $orderArray[0]['asset'], $hashSalt);
$memoSanity = btsCreateMemo($memoHashSanity);
if ($memoSanity !== $memo) {
	$ret = array();
	$ret['error'] = 'Invalid memo';
  echo json_encode($ret);
	die;
}
$response   = btsVerifyOpenOrders($orderArray, $accountName, $rpcUser, $rpcPass, $rpcPort, $hashSalt, $demo);

if(array_key_exists('error', $response))
{
  echo json_encode($response);
	die;
}

foreach ($response as $responseOrder) {
	// remove open order if its paid
	switch($responseOrder['status'])
	{
		case 'complete':
      removeInvFile($responseOrder['order_id'].'.inv','..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);	
			break;		
		case 'overpayment':		
			removeInvFile($responseOrder['order_id'].'.inv','..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
			break;
		case 'processing':
      $ret = array();
      $ret['error'] = 'This order has not been paid in full, please try again';
      echo json_encode($ret);
      die;
			break;	      
	} 
}

$post = array(
    'responseCode'     => '1',
    'reasonCode'     => '1',
    'order_id'     => $orderArray[0]['order_id'],
    'amountReceived'     => $amount,
    'total'     => $orderArray[0]['total'],
    'trxId'     => $trx_id,
    'url'     => $orderArray[0]['url']
);
// does a redirect back to cart automatically
postToEcwid($post);
die;

	


?>
