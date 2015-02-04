<?php
// delete .inv files that are older than 30 days
function deleteOldOpenOrdersHelper() {
  $ret = TRUE;
	if ($handle = opendir(ROOT)) {
		while (false !== ($file = readdir($handle))) { 
			$ext = substr($file, -3);
			if ($ext != 'inv')
				continue;
			if((time() - filemtime($file)) > 2592000)
				$ret = unlink($file);
		}
		closedir($handle); 
	}
  return $ret;
}
function sendToCartHelper($notice)
{
	$x_response_code = $notice['responseCode']; // 1=approved, 2=declined
	$x_response_reason_code = $notice['reasonCode']; // 1=approved, 2= declined
	$x_trans_id = $notice['trx_id'];
	$x_invoice_num = $notice['order_id']; 
	$x_amount = $notice['amount'];
	$x_url = $notice['url'];
	$x_total = $notice['total'];
	$string = hashSalt.login.$x_trans_id.$x_total;
  
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
	curl_setopt($ch, CURLOPT_TIMEOUT, '15');
	
	
	$response = curl_exec($ch);
	if ($response === false){
		debuglog('request to opencart failed');
		debuglog(curl_error($ch));

	}
			
	curl_close($ch);
	return $response;
}
function fileSaveToOpenOrdersHelper($dataArray)
{

  $data =  $dataArray['order_id']. PHP_EOL;
  $data .= $dataArray['total']. PHP_EOL;
  $data .= $dataArray['asset']. PHP_EOL;
  $data .= $dataArray['memo']. PHP_EOL;

  // save bitshares invoice data in a file named after the ecwid invoice id
  $bytes = file_put_contents(ROOT.$dataArray['order_id'].'.inv', $data);
  if($bytes === FALSE)
  {
    return FALSE;
  }
  return TRUE;
}
function fileSaveToOpenCompleteHelper($dataArray)
{
  $data =  date('Y-m-d H:i:s').' '.$dataArray['memo']. PHP_EOL;

  // save bitshares invoice data in a file named after the ecwid invoice id
  $bytes = file_put_contents(ROOT.'ordercomplete.inv', $data, FILE_APPEND | LOCK_EX);
  if($bytes === FALSE)
  {
    return FALSE;
  }
  return TRUE;
}
function fileRemoveHelper($invFileName)
{
  $ret = TRUE;
  if ($handle = opendir(ROOT)) {
	  while (false !== ($file = readdir($handle))) { 
		  $ext = substr($file, -3);
		  if ($ext != 'inv')
			  continue;
		  if($file != $invFileName)
			  continue;
		  $ret = unlink(ROOT.$file);  
	  }
	  closedir($handle); 
  }
  return $ret;
}
function sendToCart($order, $responseCode)
{
    $response = array();
    $post = array(
					  'responseCode'     => $responseCode,
					  'reasonCode'     => $responseCode,
					  'order_id'     => $order['order_id'],
					  'amount'     => $order['amount'],
					  'total'     => $order['total'],
					  'trx_id'     => $order['memo'],
					  'url'     => relayURL
				  );

	  $linkHTML = sendToCartHelper($post);
	  if(preg_match_all('/<a\s+href=["\']([^"\']+)["\']/i', $linkHTML, $links, PREG_PATTERN_ORDER))
		  $all_hrefs = array_unique($links[1]);
	  $response['url'] = $all_hrefs[0];
	  return $response;
	  
}
function getOpenOrdersUser()
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

function isOrderCompleteUser($memoToFind, $order_id)
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
function doesOrderExistUser($memoToFind, $order_id)
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
function completeOrderUser($order)
{
  $order['amount'] = $order['total'];	
  $response = sendToCart($order, '1');
  if(!array_key_exists('error', $response))
  {
    if(!fileRemoveHelper($order['order_id'] .'.inv'))
    {
      $response['error'] = 'There was a problem removing open order from internal database';
      $response['url'] = NULL;
    }
    
    if(!fileSaveToOpenCompleteHelper($order))
    {
      $response['error'] = 'There was a problem saving completed order to internal database';
      $response['url'] = NULL;
    }
  }
  else
  {
	$response['url'] = NULL;
  }
  return $response;
}
function cancelOrderUser($order)
{
  $order['amount'] = $order['total'];	
  $response = sendToCart($order, '2');
  if(!array_key_exists('error', $response))
  {
    if(!fileRemoveHelper($order['order_id'] .'.inv'))
    {
      $response['error'] = 'There was a problem removing open order from internal database';
      $response['url'] = NULL;
    }
  }
  else
  {
	$response['url'] = NULL;
  }  
	return $response;
}
function createOrderUser()
{
	if ($_POST['x_login'] != login) {
    $ret = array();
		debuglog('ecwid login does not match that found in config.php');
    $ret['error'] = 'invalid ecwid login';
		return $ret;
	}
	$total = 	$_POST['x_amount'];
	$order_id = $_POST['x_invoice_num'];

	$asset= btsCurrencyToAsset($_POST['x_currency_code']);
	$hash = btsCreateEHASH(accountName, $order_id, $total,$asset, hashSalt);
	$memo = btsCreateMemo($hash);
	$openOrder = array(
		'order_id'     => $order_id,
		'total'     => $total,
		'memo'     => $memo,
		'asset'     => $asset
	);
  
	fileSaveToOpenOrdersHelper($openOrder);
  $openOrder['amount'] = 0;
  $response = sendToCart($openOrder, '1');
  if(array_key_exists('error', $response))
  {
    $ret = array();
		debuglog('ecwid order creation error: '.$response['error']);
    $ret['error'] = 'ecwid order creation error: '.$response['error'] ;
		return $ret;    
  }
	$ret = array(
		'accountName'     => accountName,
		'order_id'     => $order_id,
		'memo'     => $memo
	);
	return $ret;	
}
function cronJobUser()
{
	return deleteOldOpenOrdersHelper();
}
?>