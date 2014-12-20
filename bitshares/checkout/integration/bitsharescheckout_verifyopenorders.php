<?php
//1) Get all open orders in system
//2) Verify open orders using btsVerifyOpenOrders
//3) Remove paid orders from open order list
//4) Send success notification to ECWID checkout

require '../../config.php';
require '../../bts_lib.php';
require '../../functions.php';

$openOrderList = getOpenOrders('..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
if(count($openOrderList) <= 0)
{
  die('done');
}

$demo = FALSE;
if($demoMode === "1" || $demoMode === 1 || $demoMode === TRUE || $demoMode === "true")
{
	$demo = TRUE;
}
$response   = btsVerifyOpenOrders($openOrderList, $accountName, $rpcUser, $rpcPass, $rpcPort, $hashSalt, $demo);
if(array_key_exists('error', $response))
{
	die(json_encode($response));
}
foreach ($response as $responseOrder) {
   $post = array(
        'responseCode'     => '1',
        'reasonCode'     => '1',
        'order_id'     => $responseOrder['order_id'],
        'amount'     => $responseOrder['amount'],
        'total'     => $responseOrder['total'],
        'trx_id'     => $responseOrder['memo'],
        'url'     => $relayUrl
  );     
	
	// update the order based on response status (processing for partial funds and complete for full funds)	
	switch($responseOrder['status'])
	{
		case 'complete':
      $completeOrder = array(
          'memo'     => $responseOrder['memo']
      );
      saveToOpenCompleteLog($completeOrder, '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);    
      removeInvFile($responseOrder['order_id'].'.inv','..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
			break;		
		case 'overpayment':
      $completeOrder = array(
          'memo'     => $responseOrder['memo']
      );
      saveToOpenCompleteLog($completeOrder, '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);    
			removeInvFile($responseOrder['order_id'].'.inv','..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
			break;	
	}
  
  postToEcwid($post);  
}
?>