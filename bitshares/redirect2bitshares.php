<?php

require 'bts_lib.php';
require 'config.php';
require 'functions.php';

if ($_POST['x_login'] != $login) {
	debuglog('ecwid login does not match that found in config.php');
	print 'invalid ecwid login';
	die;
}

$data =  $_POST['x_invoice_num']. PHP_EOL;
$data .= $_POST['x_amount']. PHP_EOL;
$data .= $_POST['x_currency_code']. PHP_EOL;
$data .= $_POST['x_relay_url']. PHP_EOL;
// save bitshares invoice data in a file named after the ecwid invoice id
file_put_contents($_POST['x_invoice_num'].'.inv', $data, LOCK_EX);


$response = btsCreateInvoice($accountName, $_POST['x_invoice_num'], $_POST['x_amount'], $_POST['x_amount'], $_POST['x_currency_code']);
if(array_key_exists('error', $response))
{
    error_log($response['error']);
    die("Error creating invoice");
}
else {	
    $post = array(
        'order_id'     => $_POST['x_invoice_num'],
        'amountReceived'     => 0,
        'total'     => $_POST['x_amount'],
        'trxId'     => $response['orderEHASH'],
        'url'     => $_POST['x_relay_url']
        
    );
	  $form = '<form action="'.$response['url'].'"><input type="submit" value="Open your Bitshares wallet"></form>';
    $form .= '<form action="callback.php" method="POST">';

    foreach ($post as $key => $value) {
        $form.= '<input type="hidden" name="'.$key.'" value = "'.$value.'" />';
    }

    $form.='<input type="submit" value="Return to checkout" />';
    $form.='</form>';
    echo $form;
	
}

?>
