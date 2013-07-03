<?php

/**
 * EHttpRequest Yii Framework extension.
 * 
 * Provides an extension of the CHttpRequest class within the Yii framework. This
 * extension provides the ability to use reverse proxy headers to determine the user
 * host address and to determine whether the original request was made secure.
 * 
 * Provides the possibility to get the user location info, using hostip.info's database.
 *
 * @author Emile Bons <emile@emilebons.nl>
 * @date July 3, 2013
 * 
 * @property-read string $userCity Returns the user city.
 * @property-read string $userCountryCode Returns the user country code.
 * @property-read string $userCountryName Returns the user country name.
 */
class EHttpRequest extends CHttpRequest {
	
	protected $_userLocation;
	public $useReverseProxyHeaders = false;
	
	const IP_ADDRESS_DATABASE = 'http://api.hostip.info/get_json.php';
	
	public function getIsSecureConnection() {
		$headers = apache_request_headers();
		if(!$this->useReverseProxyHeaders || !isset($headers['X-Forwarded-Proto'])) 
			return parent::getIsSecureConnection();
        return $headers['X-Forwarded-Proto'] == 'https';
	}
	
	public function getUserCity() { return $this->userLocation['city']; }
	
	protected function getUserLocation() {
		if(!empty($this->_userLocation)) return $this->_userLocation;
		$client = new EHttpClient(self::IP_ADDRESS_DATABASE);
		$client->setParameterGet('ip', $this->getUserHostAddress());
		$response = $client->request();
		$this->_userLocation = CJSON::decode($response->getBody());
		return $this->_userLocation;
	}
	
	public function getUserCountryCode() { return $this->userLocation['country_code']; }
	
	public function getUserCountryName() { return $this->userLocation['country_name']; }
	
	public function getUserHostAddress() {
		$headers = apache_request_headers();
		if(!$this->useReverseProxyHeaders || !isset($headers['X-Forwarded-For']))
			return parent::getUserHostAddress();
		return $headers['X-Forwarded-For'];
	}
	
}

?>
