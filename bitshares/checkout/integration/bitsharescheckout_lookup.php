<?php
//1) Make sure order hasn't been paid already
//2) make sure its still an open order
//3) return open order data
require '../../functions.php';

$memo = $_POST['memo'];

$orderCompleteArray = getOrderFromCompletedOrders($memo,'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
if(count($orderArray) > 0)
{
  $return = array();
  $return['error'] = 'This order has already been paid';
  echo json_encode($return);
  die;
}

$orderArray = getOrderFromOpenOrders($memo,'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
if(count($orderArray) <= 0)
{
  $return = array();
  $return['error'] = 'Could not find this order in the system, please review the Order ID and Order Hash';
  echo json_encode($return);
  die;  
}
echo json_encode($orderArray[0]);
die;
?>