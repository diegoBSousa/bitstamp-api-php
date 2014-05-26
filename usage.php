<?php
include "class/bitstamp.php";

$apiKey ="yourApiKey";
$apiSecret = "yourApiSecret";
$userId = "yourIdNumber";
$bitstamp = new bitstamp($apiKey, $apiSecret, $userId);
/*PUBLIC APIs*/
$ticker = $bitstamp->ticker();

//group - group orders with the same price (0 - false; 1 - true). Default: 1.
$grouped = $bitstamp->order_book();
$ungrouped = $bitstamp->order_book(0);

//time - time frame for transaction export ("minute" - 1 minute, "hour" - 1 hour). Default: hour.
$hour = $bitstamp->transactions();
$hour2 = $bitstamp->transactions("hour");
$minute = $bitstamp->transactions("minute");

$eurUsd = $bitstamp->eur_usd();


/*PRIVATE APIs*/
$userBalance = $bitstamp->balance();

//offset - skip that many transactions before beginning to return results. Default: 0.
//limit - limit result to that many transactions. Default: 100.
//sort - sorting by date and time (asc - ascending; desc - descending). Default: desc.
$user_transactions1 = $bitstamp->user_transactions($offset = 0, $limit = 10, $sort = "asc");
$user_transactions2 = $bitstamp->user_transactions($offset = 0, $limit = 100, $sort = "desc");
$user_transactions3 = $bitstamp->user_transactions(0, 20, "asc");
$user_transactions4 = $bitstamp->user_transactions();

$user_open_orders = $bitstamp->open_orders();

$cancel_user_order = $bitstamp->cancel_order($idOrder = 0);

//mininum order size is 5USD
$buy = $bitstamp->buy($amount= 2, $price=2.5);
$buy2 = $bitstamp->buy( 1.05, 622.5);

$sell = $bitstamp->sell($amount=0.001, $price = 602.5);
$sell2 = $bitstamp->sell(0.0001, 2211.5);

$list_of_withdrawal_requests = $bitstamp->withdrawal_requests();

$bitcoin_withdrawal_ID = $bitstamp->bitcoin_withdrawal($amount=1.456456, $address="yourBitcoinAddressWith18Characters");

$your_bitcoin_deposit_address = $bitstamp->bitcoin_deposit_address();

$unconfirmed_btc_list = $bitstamp->unconfirmed_btc();

$ripple_withdrawal_true_or_false = $bitstamp->ripple_withdrawal($amount=0.1, $address="yourRippleAddressWith33Characters", $currency="USD");

$your_ripple_address = $bitstamp->ripple_address();
var_dump($your_ripple_address);
?>
