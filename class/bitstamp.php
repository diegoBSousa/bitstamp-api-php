<?php
class bitstamp
  {
    private static $nonce;
    private static $apiKey;
    private static $apiSecret;
    private static $clientId;
    private static $certificate;
    private static $publicApiList;
    private static $privateApiList;
    private static $baseUrl;

    public function __construct($apiKey, $apiSecret, $clientId, $haveYouServerCertificate = false)
      {
        $this->apiKey = $apiKey;
        $this->clientId = $clientId;
        $this->apiSecret = $apiSecret;
        $this->certificate = (bool)$haveYouServerCertificate;
        $this->baseUrl = "https://www.bitstamp.net/api/";
        $this->version = "v. beta 0.1";
        $this->publicApiList = Array("ticker", "order_book", "transactions", "eur_usd");
        $this->privateApiList = Array("balance", "user_transactions", "open_orders", "cancel_order", "buy", "sell", "withdrawal_requests", "bitcoin_withdrawal", "bitcoin_deposit_address", "unconfirmed_btc", "ripple_withdrawal", "ripple_address");
      }

    private function nonce()
      {
        return  round(microtime(true) * 100);
      }
      
    private function signMessage($message)
      {
        $signedMessage = hash_hmac('sha256', $message, $this->apiSecret);
        $signature = strtoupper($signedMessage);
        return $signature;
      }
     
    private function callPrivateApi($api, $params = Array())
      {
        foreach($this->privateApiList as $value)
          {
            $this->nonce = $this->nonce();
            if($api == $value)
              {
                $message = $this->nonce . $this->clientId . $this->apiKey;
                $params["signature"] = $this->signMessage($message);
                $params["key"] = $this->apiKey;
                $params["nonce"] = $this->nonce;
                return $this->doRequest("POST", $api, $params);
              }
          }
        return false;
      }
     
    private function callPublicApi($api, $params = Array())
      { 
        foreach($this->publicApiList as $value)
          {
            if($api == $value)
              { 
                return $this->doRequest("GET", $api, $params);
              }
          }
        return false;
      }
     
    private function doRequest($type, $api, $params)
      {
        foreach(array_keys($params) as $key)
          {
            urlencode($params[$key]);
          }
        $postFields = http_build_query($params);
        $ch = curl_init();
        $options = Array(
                          CURLOPT_HEADER         => false,
                          CURLOPT_USERAGENT      => urlencode('PHP Bitstamp API Module ' . $this->version),
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_SSL_VERIFYPEER => $this->certificate, 
                          CURLOPT_SSL_VERIFYHOST => $this->certificate
                        );
        if($type == "POST")
          {
            $options[CURLOPT_URL]  = $this->baseUrl . $api . "/";
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $postFields;
          }
        else
          {
            $options[CURLOPT_URL] = $this->baseUrl . $api . '/?' . $postFields;
          }
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($status == 200)
          {
            return json_decode($response, true);
          }
        else
          {
            /* Debug */
            var_dump($response);
            var_dump($status);
          }
        return false;
      }
    
    /**
     * GET https://www.bitstamp.net/api/ticker/
     * @return array JSON results
     *   last - last BTC price
     *   high - last 24 hours price high
     *   low - last 24 hours price low
     *   vwap - last 24 hours volume weighted average price: vwap
     *   volume - last 24 hours volume
     *   bid - highest buy order
     *   ask - lowest sell order
     */
    public function ticker()
      { 
        return $this->callPublicApi(__FUNCTION__);
      }
      
    /**
     * GET https://www.bitstamp.net/api/order_book/
     * @param int $group
     *  group orders (0 - false; 1 - true). Default: 1
     * @return array JSON results
     *   Returns JSON dictionary with "bids" and "asks".
     *   Each is a list of open orders and each order
     *    is represented as a list of price and amount.
     */
    public function order_book($group = 1)
      {
        return $this->callPublicApi(__FUNCTION__, Array("group"=> (int)$group));
      }
      
    /**
     * GET https://www.bitstamp.net/api/transactions/
     * @param string $time
     *  Time frame for transaction export ("minute" - 1 minute, "hour" - 1 hour). Default: hour.
     * @return array JSON results
     *   Returns JSON dictionary with "bids" and "asks".
     *   Each is a list of open orders and each order
     *    is represented as a list of price and amount.
     */
    public function transactions($time = "hour")
      {
        return $this->callPublicApi(__FUNCTION__, Array("time"=> $time));
      }
    
    /**
     * GET https://www.bitstamp.net/api/eur_usd/
     *  EUR/USD conversion rate
     * @return array JSON results
     *   buy - buy conversion rate
     *   sell - sell conversion rate
     */
    public function eur_usd()
      {
        return $this->callPublicApi(__FUNCTION__);
      }
      
    /**
     * POST https://www.bitstamp.net/api/balance/
     * @return array JSON results
     *  usd_balance   - USD balance
     *  btc_balance   - BTC balance
     *  usd_reserved  - USD reserved in open orders
     *  btc_reserved  - BTC reserved in open orders
     *  usd_available - USD available for trading
     *  btc_available - BTC available for trading
     *  fee - customer trading fee
     */
    public function balance()
      {
        return $this->callPrivateApi(__FUNCTION__);
      }
      
    /**
     * POST https://www.bitstamp.net/api/user_transactions/
     * @param int $offset
     *   skip that many transactions before beginning to return results. Default: 0.
     * @param int $limit
     *   limit result to that many transactions. Default: 100.
     * @param string $sort
     *   sorting by date and time (asc - ascending; desc - descending). Default: desc.
     * @return array JSON results
     * Returns descending JSON list of transactions. Every transaction (dictionary) contains:
     *  datetime - date and time
     *  id - transaction id
     *  type - transaction type (0 - deposit; 1 - withdrawal; 2 - market trade)
     *  usd - USD amount
     *  btc - BTC amount
     *  fee - transaction fee
     *  order_id - executed order id
     */
    public function user_transactions($offset = 0, $limit = 100, $sort = "desc")
      {
        return $this->callPrivateApi(__FUNCTION__, Array( "offset" => (int)$offset, "limit" => (int)$limit, "sort" => $sort));
      }
    
    /**
     * POST https://www.bitstamp.net/api/open_orders/
     * @return array JSON results
     * Returns JSON list of open orders. Each order is represented as dictionary:
     *  id - order id
     *  datetime - date and time
     *  type - buy or sell (0 - buy; 1 - sell)
     *  price - price
     *  amount - amount
     */
    public function open_orders()
      {
        return $this->callPrivateApi(__FUNCTION__);
      }
      
    /**
     * POST https://www.bitstamp.net/api/cancel_order/
     * @param int $id
     *  order ID
     * @return bool
     * Returns 'true' if order has been found and canceled.
     */
    public function cancel_order($id)
      {
        return $this->callPrivateApi(__FUNCTION__, Array("id" => (int)$id));
      }

    /**
     * POST https://www.bitstamp.net/api/buy/
     *  Buy limit order
     * @param float $amount
     * @param float $price
     * @return array JSON results
     *  Returns JSON dictionary representing order:
     *   id - order id
     *   datetime - date and time
     *   type - buy or sell (0 - buy; 1 - sell)
     *   price - price
     *   amount - amount
     */
    public function buy($amount, $price)
      {
        return $this->callPrivateApi(__FUNCTION__, Array("amount" => number_format((float)$amount, 5), "price" => number_format((float)$price, 2) ));
      }

      /**
     * POST https://www.bitstamp.net/api/sell/
     *  Sell limit order
     * @param float $amount
     * @param float $price
     * @return array JSON results
     *  Returns JSON dictionary representing order:
     *   id - order id
     *   datetime - date and time
     *   type - buy or sell (0 - buy; 1 - sell)
     *   price - price
     *   amount - amount
     */
    public function sell($amount, $price)
      {
        return $this->callPrivateApi(__FUNCTION__, Array("amount" => number_format((float)$amount, 5), "price" => number_format((float)$price, 2) ));
      }
     
     /**
     * POST https://www.bitstamp.net/api/withdrawal_requests/
     *  Withdrawal requests
     * @return array JSON results
     *  Returns JSON dictionary representing order:
     *   id - order id
     *   datetime - date and time
     *   type - (0 - SEPA; 1 - bitcoin; 2 - WIRE transfer; 3 and 4 - bitstamp code; 5 - Mt.Gox code)
     *   amount - amount
     *   status - (0 - open; 1 - in process; 2 - finished; 3 - canceled; 4 - failed)
     *  data - additional withdrawal request data (Mt.Gox code, etc.)
     */
    public function withdrawal_requests()
      {
        return $this->callPrivateApi(__FUNCTION__);
      }
     
     /**
     * POST https://www.bitstamp.net/api/bitcoin_withdrawal/
     *  Bitcoin withdrawal
     * @param float $amount
     * @param string $address
     *     Bitcoins address
     * @return array JSON results
     *  Returns JSON dictionary if successful:
     *    id - withdrawal id
     */
    public function bitcoin_withdrawal($amount, $address)
      {
        return $this->callPrivateApi(__FUNCTION__, Array("amount" => (float)$amount, "address" => $address));
      }
     
     /**
     * POST https://www.bitstamp.net/api/bitcoin_deposit_address/
     *  Bitcoin deposit address
     * @return string
     *  Returns your bitcoin deposit address.
     */
    public function bitcoin_deposit_address()
      {
        return $this->callPrivateApi(__FUNCTION__);
      }
     
     /**
     * POST https://www.bitstamp.net/api/unconfirmed_btc/
     *  Unconfirmed bitcoin deposits
     * @return array JSON results
     *  Returns JSON list of unconfirmed bitcoin transactions.
     *  Each transaction is represented as dictionary:
     *   amount - bitcoin amount
     *   address - deposit address used
     *   confirmations - number of confirmations
     */
    public function unconfirmed_btc()
      {
        return $this->callPrivateApi(__FUNCTION__);
      }
     
     /**
     * POST https://www.bitstamp.net/api/ripple_withdrawal/
     *  Ripple withdrawal
     * @param float $amount
     * @param string $address
     * @param string $currency
     * @return boolean
     *  Returns true if successful.
     */
    public function ripple_withdrawal($amount, $address, $currency)
      {
        return $this->callPrivateApi(__FUNCTION__, Array("amount" => (float)$amount, "address" => $address, "currency" => $currency));
      }

     /**
     * POST https://www.bitstamp.net/api/ripple_address/
     *  Ripple deposit address
     * @return string
     *  Returns your ripple deposit address.
     */
    public function ripple_address()
      {
        return $this->callPrivateApi(__FUNCTION__);
      }
  }     
?>
