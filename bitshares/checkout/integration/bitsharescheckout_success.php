<?php
require '../../config.php';
require '../../bts_lib.php';
require '../../functions.php';


$order_id = $_POST['order_id'];
$memo = $_POST['memo'];
$orderArray = getOrderFromOpenOrders($memo,'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
if(count($orderArray) <= 0)
{
  $ret = array();
  $ret['error'] = 'Could not find this order in the system, please review the Order ID and Order Hash';
  die(json_encode($ret));
}

if ($orderArray[0]['order_id'] !== $order_id) {
	$ret = array();
	$ret['error'] = 'Invalid Order ID. Could not complete your order';
	die(json_encode($ret));
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
	die(json_encode($ret));
}
$orderpaid = FALSE;
$amount = 0;
foreach ($response as $responseOrder) {
	// remove open order if its paid
	switch($responseOrder['status'])
	{
		case 'complete':    
      $orderpaid = TRUE;
      $amount += $responseOrder['amount'];
      break;		
		case 'overpayment':
      $orderpaid = TRUE;
      $amount += $responseOrder['amount'];
			break; 
 	  case 'processing':
      $amount += $responseOrder['amount'];
			break;    
	} 
}
if($amount < ($orderArray[0]['total'] - 1))
  $amount = $orderArray[0]['total'];
else if($amount > ($orderArray[0]['total'] + 1))
  $amount = $orderArray[0]['total'];
if($orderpaid)
{
  $post = array(
      'responseCode'     => '1',
      'reasonCode'     => '1',
      'order_id'     => $orderArray[0]['order_id'],
      'amount'     => $amount,
      'total'     => $orderArray[0]['total'],
      'trx_id'     => $memo,
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
  die(json_encode($response));
}
else
{
  $ret = array();
  $ret['error'] = 'Order was not paid fully. Please try again';
  die(json_encode($ret));
}
?>