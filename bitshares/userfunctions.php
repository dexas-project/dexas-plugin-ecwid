<?php
function fileSaveToOpenOrdersHelper($dataArray)
{

  $data =  $dataArray['order_id']. PHP_EOL;
  $data .= $dataArray['total']. PHP_EOL;
  $data .= $dataArray['asset']. PHP_EOL;
  $data .= $dataArray['memo']. PHP_EOL;

  // save bitshares invoice data in a file named after the ecwid invoice id
  file_put_contents(ROOT.$dataArray['order_id'].'.inv', $data);
}
function fileSaveToOpenCompleteHelper($dataArray)
{
  $data =  date('Y-m-d H:i:s').' '.$dataArray['memo']. PHP_EOL;

  // save bitshares invoice data in a file named after the ecwid invoice id
  file_put_contents(ROOT.'ordercomplete.inv', $data, FILE_APPEND | LOCK_EX);
}
function fileRemoveHelper($invFileName)
{
  if ($handle = opendir(ROOT)) {
	  while (false !== ($file = readdir($handle))) { 
		  $ext = substr($file, -3);
		  if ($ext != 'inv')
			  continue;
		  if($file != $invFileName)
			continue;
		  unlink(ROOT.$file);  
	  }
	  closedir($handle); 
  }      
}
function getOpenOrdersHelper()
{
  $openOrderList = array();
  if ($handle = opendir(ROOT)) {
	  while (false !== ($file = readdir($handle))) { 
		  $ext = substr($file, -3);
		  if ($ext != 'inv')
			  continue;
		  $fileHandle =  fopen(ROOT.$file, "r");
		  if($fileHandle)
		  {
			$newOrder = array();
			$id = fgets($fileHandle);
			$total = fgets($fileHandle);
			$asset = fgets($fileHandle);
			$memo = fgets($memo);
			$newOrder['total'] = trim($total);
			$newOrder['asset'] = trim($asset);
			$newOrder['order_id'] = trim($id);
			$newOrder['memo'] = trim($memo);
			$newOrder['date_added'] = 0;
			array_push($openOrderList,$newOrder);
			fclose($fileHandle);
		  }
		}
		closedir($handle); 
  }
  return $openOrderList;
}

function isOrderCompleteHelper($memoToFind)
{
  if ($handle = opendir(ROOT)) {
	  while (false !== ($file = readdir($handle))) { 
		  if ($file !== 'ordercomplete.inv')
			  continue;
      $fileHandle =  fopen(ROOT.$file, 'r');
      if($fileHandle)
      {
        $valid = FALSE;
        while (($buffer = fgets($fileHandle)) !== false) {
            if (strpos($buffer, $memoToFind) !== false) {
                $valid = TRUE;
                break; 
            }      
        }
        fclose($fileHandle);
        return $valid;
      }
      
	}
	closedir($handle); 
  }      
  return FALSE;
}
function doesOrderExistHelper($memoToFind)
{

  if ($handle = opendir(ROOT)) {
	  while (false !== ($file = readdir($handle))) { 
		  $ext = substr($file, -3);
		  if ($ext != 'inv')
			  continue;
      $fileHandle =  fopen(ROOT.$file, 'r');
      if($fileHandle)
      {
        $order = array();
        $order['order_id'] = trim(fgets($fileHandle));
        $order['total'] = trim(fgets($fileHandle));
        $order['asset'] = trim(fgets($fileHandle));
        $order['memo'] = trim(fgets($fileHandle));
        fclose($fileHandle);
        if($memoToFind === $order['memo'])
        {	
          return $order;  
        }  
      }
      
	}
	closedir($handle); 
  }      
  return FALSE;
}
function getOrderComplete($memo, $order_id)
{	
  $orders = array();
  $myorder = isOrderCompleteHelper($memo);
  if($myorder !== FALSE)
  {
    array_push($orders, $myorder);
  }
  return $orders;
}

function getOrder($memo, $order_id)
{
  $orders = array();
  $myorder = doesOrderExistHelper($memo);
  if($myorder !== FALSE)
  {
    array_push($orders, $myorder);
  }
  return $orders;
}

function completeOrderUser($memo, $order_id)
{
	global $relayUrl;
	global $baseURL;
	global $relayUrl;
	global $accountName;
	global $rpcUser;
	global $rpcPass;
	global $rpcPort;
	global $demoMode;
	global $hashSalt;
	$orderArray = getOrder($memo, $order_id);
	if(count($orderArray) <= 0)
	{
	  $ret = array();
	  $ret['error'] = 'Could not find this order in the system, please review the Order ID and Order Hash';
	  return $ret;
	}

	if ($orderArray[0]['order_id'] !== $order_id) {
		$ret = array();
		$ret['error'] = 'Invalid Order ID';
		return $ret;
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
	  return $ret;
	}	
	$orderpaid = FALSE;
	$order_id = 0;
	$amount = 0;
	foreach ($response as $responseOrder) {
		switch($responseOrder['status'])
		{
			case 'complete':    
				$orderpaid = TRUE;
				$order_id = $responseOrder['order_id'];
				$total = $responseOrder['total'];
				$memo = $responseOrder['memo'];
				$amount += $responseOrder['amount'];
				break;		
			case 'overpayment':
				$orderpaid = TRUE;
				$order_id = $responseOrder['order_id'];
				$total = $responseOrder['total'];
				$memo = $responseOrder['memo'];				
				$amount += $responseOrder['amount'];
				break; 
 			case 'processing':
				$amount += $responseOrder['amount'];
				break;    
		} 
	}
	if($orderpaid)
	{
	  $post = array(
		  'responseCode'     => '1',
		  'reasonCode'     => '1',
		  'order_id'     => $order_id,
		  'amount'     => $amount,
		  'total'     => $total,
		  'trx_id'     => $memo,
		  'url'     => $relayUrl
	  );

	  $linkHTML = sendToCart($post);
	  if(preg_match_all('/<a\s+href=["\']([^"\']+)["\']/i', $linkHTML, $links, PREG_PATTERN_ORDER))
		  $all_hrefs = array_unique($links[1]);
	  $response['url'] = $all_hrefs[0];
	  fileRemoveHelper($order_id .'.inv');
	  $completeOrder = array(
		  'memo'     => $memo
	  );
	  fileSaveToOpenCompleteHelper($completeOrder);
	}
	return $response;	  
}
function cancelOrderUser($memo, $order_id)
{
	global $relayUrl;
	global $baseURL;
	$orderArray = getOrder($memo, $order_id);
	if(count($orderArray) <= 0)
	{
	  $ret = array();
	  $ret['url'] = $baseURL;
	  return $ret;
	}
	$order = $orderArray[0];
	$response = array();
	$post = array(
		'responseCode'     => '2',
		'reasonCode'     => '2',
		'order_id'     => $order['order_id'],
		'amount'     => $order['total'],
		'total'     => $order['total'],
		'trx_id'     => $order['memo'],
		'url'     => $relayUrl
	);
	$linkHTML = sendToCart($post);
	if(preg_match_all('/<a\s+href=["\']([^"\']+)["\']/i', $linkHTML, $links, PREG_PATTERN_ORDER))
		$all_hrefs = array_unique($links[1]);
	$response['url'] = $all_hrefs[0];
	fileRemoveHelper($order['order_id'] .'.inv');
	return $response;
}
function createOrderUser()
{
	global $relayUrl;
	global $accountName;
	global $login;
	global $hashSalt;
	if ($_POST['x_login'] != $login) {
		debuglog('ecwid login does not match that found in config.php');
		return 'invalid ecwid login';
	}
	$total = 	$_POST['x_amount'];
	$order_id = $_POST['x_invoice_num'];

	$asset= btsCurrencyToAsset($_POST['x_currency_code']);
	$hash = btsCreateEHASH($accountName, $order_id, $total,$asset, $hashSalt);
	$memo = btsCreateMemo($hash);
	$openOrder = array(
		'order_id'     => $order_id,
		'total'     => $total,
		'memo'     => $memo,
		'asset'     => $asset
	);
	fileSaveToOpenOrdersHelper($openOrder);
	$post = array(
		'responseCode'     => '1',
		'reasonCode'     => '1',
		'order_id'     => $order_id,
		'amount'     => 0,
		'total'     => $total,
		'trx_id'     => $memo,
		'url'     => $relayUrl
	);
	sendToCart($post);
	$ret = array(
		'account'     => $accountName,
		'order_id'     => $order_id,
		'memo'     => $memo
	);
	return $ret;	
}
function cronJobUser()
{

	$openOrderList = getOpenOrdersHelper();
	if(count($openOrderList) <= 0)
	{
	  return 'No open orders found!';
	}

	$demo = FALSE;
	if($demoMode === "1" || $demoMode === 1 || $demoMode === TRUE || $demoMode === "true")
	{
		$demo = TRUE;
	}
	$response   = btsVerifyOpenOrders($openOrderList, $accountName, $rpcUser, $rpcPass, $rpcPort, $hashSalt, $demo);
	if(array_key_exists('error', $response))
	{
		return $response;
	}
	return completeOrderUser($response);
}
function sendToCart($notice)
{
	global $login;
	global $hashSalt;
	$x_response_code = $notice['responseCode']; // 1=approved, 2=declined
	$x_response_reason_code = $notice['reasonCode']; // 1=approved, 2= declined
	$x_trans_id = $notice['trx_id'];
	$x_invoice_num = $notice['order_id']; 
	$x_amount = $notice['amount'];
	$x_url = $notice['url'];
	$x_total = $notice['total'];
	$string = $hashSalt.$login.$x_trans_id.$x_total;
  
	$x_MD5_Hash = md5($string);
	$datatopost = array (
		"x_response_code" => $x_response_code,
		"x_response_reason_code" => $x_response_reason_code,
		"x_trans_id" => $x_trans_id,
		"x_invoice_num" => $x_invoice_num,
		"x_amount" => $x_amount,
		"x_MD5_Hash" => $x_MD5_Hash
		);
	$ch = curl_init($x_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $datatopost);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  
	
	
	$response = curl_exec($ch);
	if ($response === false){
		debuglog('request to opencart failed');
		debuglog(curl_error($ch));
	}
			
	curl_close($ch);
	return $response;
}
?>