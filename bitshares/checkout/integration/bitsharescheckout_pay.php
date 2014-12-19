<?php
//1) Recreate hash from post data to ensure it matches with hash passed in, if not send error message
//2) Create BTS URI, if it can't send error
//3) Send BTS URI
require '../../bts_lib.php';
require '../../config.php';
require '../../functions.php';
$return = array();

$memo = $_POST['memo'];
$balance = $_POST['balance'];
$orderArray = getOrderFromOpenOrders($memo,'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
if(count($orderArray) <= 0)
{
  $ret = array();
  $ret['error'] = 'Could not find this order in the system, please review the Order ID and Order Hash';
  echo json_encode($ret);
  die;
}
$response = btsCreateInvoice($accountName, $orderArray[0]['order_id'], $balance, $orderArray[0]['total'], $orderArray[0]['asset'], $memo);
if(array_key_exists('error', $response))
{
    error_log($response['error']);
    $return['error'] = $response['error'];
    echo json_encode($return);
    die("Error creating invoice");
}
else {	
    $return['url'] = $response['url'];
    echo json_encode($return);
    die;
}

?>
