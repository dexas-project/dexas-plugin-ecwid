<?php
// This is a redirect page, expect the caller to redirec to URL passed back.

//1) Verify order exists in open order list based on memo or order_id, if not send error message
//2) Recreate hash from post data to ensure it matches with hash passed in, if not send error message
//3) Verify order exists on blockchain and is fully paid, if not send error message
//4) Send success notification to ECWID checkout

require '../../config.php';
require '../../bts_lib.php';
require '../../functions.php';



$memo = $_POST['memo'];
$orderArray = getOrderFromOpenOrders($memo,'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
if(count($orderArray) <= 0)
{
  $ret = array();
  $ret['error'] = 'Could not find this order in the system, please review the Order ID and Order Hash';';
  echo json_encode($ret);
  die;
}
$memoHashSanity = btsCreateEHASH($accountName,$orderArray[0]['order_id'], $orderArray[0]['total'], $orderArray[0]['asset'], $hashSalt);
$memoSanity = btsCreateMemo($memoHashSanity);
if ($memoSanity !== $memo) {
	$ret = array();
	$ret['error'] = 'Invalid memo. Could not complete your order';
  echo json_encode($ret);
	die;
}
$demo = FALSE;
if($demoMode === "1" || $demoMode === 1 || $demoMode === TRUE || $demoMode === "true")
{
	$demo = TRUE;
}
$response = btsVerifyOpenOrders($orderArray, $accountName, $rpcUser, $rpcPass, $rpcPort, $hashSalt, $demo);

if(array_key_exists('error', $response))
{
  $ret = array();
  $ret['error'] = 'Could not verify order. Please try again';
  echo json_encode($ret);
	die;
}

$orderpaid = false;
$trxid = null;
$amount = 0;
foreach ($response as $responseOrder) {
	// remove open order if its paid
	switch($responseOrder['status'])
	{
		case 'complete':    
      $orderpaid = true;
      $trxid  = $responseOrder['trx_id'];
      $amount += $responseOrder['amount'];
      break;		
		case 'overpayment':
      $orderpaid = true;
      $trxid  = $responseOrder['trx_id'];
      $amount += $responseOrder['amount'];
			break; 
    
	} 
}
if($orderpaid == true)
{
  $post = array(
      'responseCode'     => '1',
      'reasonCode'     => '1',
      'order_id'     => $orderArray[0]['order_id'],
      'amount'     => $amount,
      'total'     => $orderArray[0]['total'],
      'trx_id'     => $trxid,
      'url'     => $relayUrl
  );

  $linkHTML = postToEcwid($post);
  if(preg_match_all('/<a\s+href=["\']([^"\']+)["\']/i', $linkHTML, $links, PREG_PATTERN_ORDER))
      $all_hrefs = array_unique($links[1]);
  $response['url'] = $all_hrefs[0];
  removeInvFile($order_id .'inv', '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
  $completeOrder = array(
      'memo'     => $memo
  );
  saveToOpenCompleteLog($completeOrder, '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
  echo json_encode($response);
}
else
{
  $ret = array();
  $ret['error'] = 'Order was not paid fully. Please try again';
  echo json_encode($ret);
}
die;

	


?>
