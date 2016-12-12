<?php

namespace AppBundle\Service;

class Twitter {

    /**
     * Twitter API url
     *
     * @var string
     */
	protected $apiUrl;

    /**
     * Consumer Key (API Key) from Twitter application
     *
     * @var string
     */
	protected $consumerKey;

    /**
     * Consumer Secret (API Secret) from Twitter application
     *
     * @var string
     */
	protected $consumerSecret;

    /**
     * Access Token from Twitter application
     *
     * @var string
     */
	protected $accessToken;

    /**
     * Access Token Secret from Twitter application
     *
     * @var string
     */
	protected $accessTokenSecret;

	public function __construct($apiUrl, $consumerKey, $consumerSecret, $accessToken, $accessTokenSecret)
	{
		$this->apiUrl = $apiUrl;
		$this->consumerKey = $consumerKey;
		$this->consumerSecret = $consumerSecret;
		$this->accessToken = $accessToken;
		$this->accessTokenSecret = $accessTokenSecret;
	}

    /**
     * Get Twitter user by screen name
     *
     * @param string $name
     *
     * @return stdClass
     */
	public function getUserByName($name = '')
	{
        $baseUrl = $this->apiUrl.'users/show.json';
        $url = $baseUrl.'?screen_name='.$name;

        $OAuthHeader = $this->getOAuthHeader($baseUrl, 'GET', array('screen_name' => $name));

        if(is_object($OAuthHeader)) 
        {
            return $OAuthHeader;
        }

        $header = array('Authorization: ' . $OAuthHeader, 'Expect:');

        $twitterUser = $this->curl($header, $url);

        return $twitterUser;
	}

    /**
     * Get user tweets by screen name
     *
     * @param string $name
     * @param int $count
     *
     * @return array
     */
    public function getUserTweets($name, $count = 20)
    {
        $baseUrl = $this->apiUrl.'statuses/user_timeline.json';
        $url = $baseUrl.'?screen_name='.$name.'&count='.$count;

        $OAuthHeader = $this->getOAuthHeader($baseUrl, 'GET', array('screen_name' => $name, 'count' => $count));

        if(is_object($OAuthHeader)) 
        {
            return $OAuthHeader;
        }

        $header = array('Authorization: ' . $OAuthHeader, 'Expect:');

        $userTweets = $this->curl($header, $url);

        return $userTweets;
    }

    /**
     * Returns the header authorization OAuth value.
     *
     * @param string $baseUrl
     * @param string $method
     * @param array  $parameters
     *
     * @return string
     */
    protected function getOAuthHeader($baseUrl, $method = 'GET', $parameters = array())
    {
        if (empty($this->accessToken) || empty($this->accessTokenSecret) || empty($this->consumerKey) || empty($this->consumerSecret)) 
        {
            $message = "Nisu postavljeni svi parametri potrebni za spajanje na Twitter API";
            $error = $this->error('', $message);

            return $error;
        }

        $oAuthParameters = array(
            'oauth_consumer_key' => $this->consumerKey,
            'oauth_nonce' => time(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => time(),
            'oauth_token' => $this->accessToken,
            'oauth_version' => '1.0',
        );
        $oAuthParameters = array_merge($parameters, $oAuthParameters);
        // Sort parameters, parameters order is important when building auth header
        ksort($oAuthParameters);

        $queryParameters = $this->getQueryParameters($oAuthParameters);
        $parameterQueryParts = explode('&', $queryParameters);

        // Build signature string
        $signatureString = strtoupper($method).'&'.rawurlencode($baseUrl).'&'.rawurlencode($queryParameters);
        $signatureKey = rawurlencode($this->consumerSecret).'&'.rawurlencode($this->accessTokenSecret);
        $signature = base64_encode(hash_hmac('sha1', $signatureString, $signatureKey, true));

        // Create headers containing oauth
        $parameterQueryParts[] = 'oauth_signature='.rawurlencode($signature);
        return 'OAuth '.implode(', ', $parameterQueryParts);
    }

    /**
     * Returns the query parameters.
     *
     * @param array $parameters
     *
     * @return string
     */
    protected function getQueryParameters($parameters = array())
    {
        $query = '';
        if (count($parameters) > 0) 
        {
            $queryParts = array();
            foreach ($parameters as $key => $value) {
                $queryParts[] = $key.'='.rawurlencode($value);
            }
            $query = implode('&', $queryParts);
        }
        return $query;
    }

    /**
     * Make curl request and return data
     *
     * @param string $header
     * @param string $url
     *
     * @return mixed
     */
    protected function curl($header, $url)
    {
        $options = array( 
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_HEADER => false,
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false
           );

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $result = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

        $data = json_decode($result);

        return $data;
    }

    /**
     * Format error object
     *
     * @param int $code
     * @param string $message
     *
     * @return stdClass
     */
    protected function error($code, $message)
    {
        $errorObj = new \stdClass;
        $errorObj->code = $code;
        $errorObj->message = $message;
        $errorArr = array($errorObj);

        $result = new \stdClass;
        $result->errors = $errorArr;

        return $result;
    }

}