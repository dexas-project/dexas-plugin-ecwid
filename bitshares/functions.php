<?php

function debuglog($contents)
{
	error_log($contents);
}
function getOpenOrders($relativeDir)
{
  $openOrderList = array();
  if ($handle = opendir($relativeDir)) {
	  while (false !== ($file = readdir($handle))) { 
		  $ext = substr($file, -3);
		  if ($ext != 'inv')
			  continue;
      $fileHandle =  fopen($relativeDir.$file, "r");
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

function getOrderFromCompletedOrders($memo, $relativeDir)
{
  $orders = array();
  $myorder = isOrderComplete($memo, $relativeDir);
  if($myorder !== FALSE)
  {
    array_push($orders, $myorder);
  }
  return $orders;
}
function getOrderFromOpenOrders($memo, $relativeDir)
{
  $orders = array();
  $myorder = doesOrderExist($memo, $relativeDir);
  if($myorder !== FALSE)
  {
    array_push($orders, $myorder);
  }
  return $orders;
}
function saveToOpenCompleteLog($dataArray, $relativeDir)
{
  $data =  date('Y-m-d H:i:s').' '.$dataArray['memo']. PHP_EOL;

  // save bitshares invoice data in a file named after the ecwid invoice id
  file_put_contents($relativeDir.'ordercomplete.inv', $data, FILE_APPEND | LOCK_EX);
}
function saveToOpenOrders($dataArray, $relativeDir)
{
  $data =  $dataArray['order_id']. PHP_EOL;
  $data .= $dataArray['total']. PHP_EOL;
  $data .= $dataArray['asset']. PHP_EOL;
  $data .= $dataArray['memo']. PHP_EOL;

  // save bitshares invoice data in a file named after the ecwid invoice id
  file_put_contents($relativeDir.$dataArray['order_id'].'.inv', $data);
}
function removeInvFile($invFileName, $relativeDir)
{
  if ($handle = opendir($relativeDir)) {
	  while (false !== ($file = readdir($handle))) { 
		  $ext = substr($file, -3);
		  if ($ext != 'inv')
			  continue;
      if($file != $invFileName)
        continue;
      unlink($relativeDir.$file);  
	  }
	  closedir($handle); 
  }      
}
function isOrderComplete($memoToFind, $relativeDir)
{

  if ($handle = opendir($relativeDir)) {
	  while (false !== ($file = readdir($handle))) { 
		  if ($file !== 'ordercomplete.inv')
			  continue;
      $fileHandle =  fopen($relativeDir.$file, 'r');
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
function doesOrderExist($memoToFind, $relativeDir)
{

  if ($handle = opendir($relativeDir)) {
	  while (false !== ($file = readdir($handle))) { 
		  $ext = substr($file, -3);
		  if ($ext != 'inv')
			  continue;
      $fileHandle =  fopen($relativeDir.$file, 'r');
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
function postToEcwid($notice)
{
	require 'config.php';
	
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
		debuglog('request to ecwid.com failed');
		debuglog(curl_error($ch));
	}
			
	curl_close($ch);
	return $response;
}
?>