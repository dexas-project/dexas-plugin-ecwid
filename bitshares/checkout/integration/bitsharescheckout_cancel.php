<?php

//1) Recreate hash from post data to ensure it matches with hash passed in, if not redirect back to base url
//2) Verify order exists on blockchain and is fully paid
//3) Remove order from open order list
//4) Go to base url

require '../../config.php';
require '../../bts_lib.php';
require '../../functions.php';

$response = array();
$trx_id = $_POST['trx_id'];
$memo = $_POST['memo'];
$order_id = $_POST['order_id'];
$total = $_POST['total'];
$asset = $_POST['asset'];
$memoHashSanity = btsCreateEHASH($accountName,$order_id, $total, $asset, $hashSalt);
$memoSanity = btsCreateMemo($memoHashSanity);
if ($memoSanity !== $memo) {
  $response['url'] = $baseURL;
  echo json_encode($response);
  die;
}

removeInvFile($orderArray[0]['order_id'] .'inv', '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
//postToEcwid($post);
$response['url'] = $baseURL;
echo json_encode($response);
die;

	


?>
