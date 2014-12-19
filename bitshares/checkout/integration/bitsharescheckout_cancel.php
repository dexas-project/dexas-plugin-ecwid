<?php
// This is a redirect page, expect the caller to redirec to URL passed back.

//1) Recreate hash from post data to ensure it matches with hash passed in, if not redirect back to base url
//2) Remove order from open order list
//3) Go to base url

require '../../config.php';
require '../../bts_lib.php';
require '../../functions.php';

$response = array();

$memo = $_POST['memo'];
$orderArray = getOrderFromOpenOrders($memo,'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
if(count($orderArray) <= 0)
{
  $ret = array();
  $ret['url'] = $baseURL;
  echo json_encode($ret);
  die;
}
$post = array(
    'responseCode'     => '2',
    'reasonCode'     => '2',
    'order_id'     => $orderArray[0]['order_id'],
    'amount'     => $orderArray[0]['total'],
    'total'     => $orderArray[0]['total'],
    'trx_id'     => 1,
    'url'     => $relayUrl
);
$linkHTML = postToEcwid($post);
if(preg_match_all('/<a\s+href=["\']([^"\']+)["\']/i', $linkHTML, $links, PREG_PATTERN_ORDER))
    $all_hrefs = array_unique($links[1]);
$response['url'] = $all_hrefs[0];
removeInvFile($order_id .'inv', '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
echo json_encode($response);
die;
?>