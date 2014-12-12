<?php
require 'config.php';
require 'bts_lib.php';
require 'functions.php';
$openOrderList = array();
$ecwidAPIUrl = '';
if ($handle = opendir('./')) {
	while (false !== ($file = readdir($handle))) { 
		$ext = substr($file, -3);
		if ($ext != 'inv')
			continue;
    $fileHandle =  fopen($file, "r");
    $newOrder = array();
    $id = fgets($fileHandle);
    $total = fgets($fileHandle);
    $currency = fgets($fileHandle);
    $ecwidAPIUrl = fgets($fileHandle);
    $newOrder['total'] = $total;
    $newOrder['currency_code'] = $currency;
    $newOrder['order_id'] = $id;
    $newOrder['date_added'] = 0;
    array_push($openOrderList,$newOrder);
	}
	closedir($handle); 
}
$demo = FALSE;
if($demoMode == "1" || $demoMode == 1 || $demoMode == TRUE || $demoMode == "true")
{
	$demo = TRUE;
}
$response   = btsVerifyOpenOrders($openOrderList, $accountName, $rpcUser, $rpcPass, $rpcPort, $demo);
echo json_encode($response);
if(array_key_exists('error', $response))
{
	die;
}
foreach ($response as $responseOrder) {
  $responseOrder['url'] = $ecwidAPIUrl;
	// update the order based on response status (processing for partial funds and complete for full funds)	
	switch($responseOrder['status'])
	{
		case 'complete':
      removeInvFile($responseOrder['order_id'].'.inv');
			postToEcwid($responseOrder); 
			break;		
		case 'overpayment':		
			removeInvFile($responseOrder['order_id'].'.inv');
			postToEcwid($responseOrder); 
			break;
		case 'processing':
      postToEcwid($responseOrder); 
			break;		
	}
	 
}
function removeInvFile($invFileName)
{
  if ($handle = opendir('./')) {
	  while (false !== ($file = readdir($handle))) { 
		  $ext = substr($file, -3);
		  if ($ext != 'inv')
			  continue;
      if($file != $invFileName)
        continue;
      unlink($file);  
	  }
	  closedir($handle); 
  }      
}
?>