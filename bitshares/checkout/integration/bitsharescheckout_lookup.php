<?php
//1) Make sure order hasn't been paid already
//2) make sure its still an open order
//3) return open order data
require '../../functions.php';

$memo = $_POST['memo'];
$order_id = $_POST['order_id'];
$orderCompleteArray = getOrderFromCompletedOrders($memo,'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
if(count($orderCompleteArray) > 0)
{
  $return = array();
  $return['error'] = 'This order has already been paid';
  die(json_encode($return));
}

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
die(json_encode($orderArray[0]));  
?>