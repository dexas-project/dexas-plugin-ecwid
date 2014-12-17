<?php
//1) Get all open orders in system
//2) Verify open orders using btsVerifyOpenOrders
//3) Remove paid orders from open order list
//4) Send success notification to ECWID checkout

require '../../config.php';
require '../../bts_lib.php';
require '../../functions.php';

$ecwidAPIUrl = '';
$openOrderList = getOpenOrders('..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
if(count($openOrderList) > 0)
{
  $ecwidAPIUrl = $openOrderList[0]['url'];
}
else
{
  die;
}
$demo = FALSE;
if($demoMode === "1" || $demoMode === 1 || $demoMode === TRUE || $demoMode === "true")
{
	$demo = TRUE;
}
$response   = btsVerifyOpenOrders($openOrderList, $accountName, $rpcUser, $rpcPass, $rpcPort, $hashSalt, $demo);
echo json_encode($response);
if(array_key_exists('error', $response))
{
	die;
}
foreach ($response as $responseOrder) {
  $responseOrder['url'] = $ecwidAPIUrl;
  $responseOrder['responseCode'] = '1';
  $responseOrder['responseReason'] = '1';
  
	// update the order based on response status (processing for partial funds and complete for full funds)	
	switch($responseOrder['status'])
	{
		case 'complete':
      removeInvFile($responseOrder['order_id'].'.inv','..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
			postToEcwid($responseOrder); 
			break;		
		case 'overpayment':		
			removeInvFile($responseOrder['order_id'].'.inv','..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
			postToEcwid($responseOrder); 
			break;
		case 'processing':
      postToEcwid($responseOrder); 
			break;		
	} 
}
die;

?>