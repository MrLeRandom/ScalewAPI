<?php
class ScalewAPI{
	/*
	Organization_ID => https://console.scaleway.com/account/credentials = String
	*/
	private $organization_id; 
	/*
		Dernière réponse de l'API = arrat
	*/
	private $last_response;

	/*
		Url de l'API scaleway = String
	*/
	private $api_url;
	
	/*
		Dernière URL contacter
	*/
	private $last_url;
	/*
		Dernier parametre utiliser
	*/
	private $last_param;
	/*
		Headers HTTP = array
	*/
	private $header;

	function __construct(string $key, string $url = "https://api.scaleway.com"){
		$this->organization_id = $key;
		$this->api_url = $url;
	}
	public function get(string $path, array $param = [], array $header = [], $json = true){
		return $this->http($this->api_url, $path, 'GET', $param, $header);
	}
	public function post(string $path, array $param = [], array $header = [], $json = true){
		return $this->http($this->api_url, $path, 'POST', $param, $header);
	}
	public function delete(string $path, array $param = [], array $header = [], $json = true){
		return $this->http($this->api_url, $path, 'DELETE', $param, $header);
	}
	public function put(string $path, array $param = [], array $header = [], $json = true){
		return $this->http($this->api_url, $path, 'PUT', $param, $header);
	}
	public function patch(string $path, array $param = [], array $header = [], $json = true){
		return $this->http($this->api_url, $path, 'PATCH', $param, $header);
	}
	public function head(string $path, array $param = [], array $header = [], $json = true){
		return $this->http($this->api_url, $path, 'HEAD', $param, $header);
	}
	private function http(string $url, string $path, string $method, array $param = [], array $header = [], $json = true){
		$headers = ['X-Auth-Token: '.$this->organization_id];
		$ch = curl_init();
		if($method == 'GET' || $method == "HEAD" || $method == "PURGE"){ //Les methods qui passes en query
			curl_setopt($ch, CURLOPT_URL, $url.(($path[1] != "/") ? "/".$path : $path).'?'.$this->array_to_get($param));
			$this->last_url = ['date' => date(DateTime::ISO8601), 'result' => $url.(($path[1] != "/") ? "/".$path : $path).'?'.$this->array_to_get($param)];
			$this->last_param = ['date' => date(DateTime::ISO8601), 'result' => $this->array_to_get($param)];
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		}elseif($method == "POST"){
			curl_setopt($ch, CURLOPT_URL, $url.'/'.$path);
			$this->last_url = ['date' => date(DateTime::ISO8601), 'result' => $url.'/'.$path];
			$this->last_param = ['date' => date(DateTime::ISO8601), 'result' => json_encode($param)];
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($param));
			curl_setopt($ch, CURLOPT_POST, 1);
		}else{
			curl_setopt($ch, CURLOPT_URL, $url.'/'.$path);
			$this->last_url = ['date' => date(DateTime::ISO8601), 'result' => $url.'/'.$path];
			$this->last_param = ['date' => date(DateTime::ISO8601), 'result' => json_encode($param)];
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($param));
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($header, $headers));  // On passe la clé en deuxième pour écrasé au cas ou

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
		    $err_ch = curl_error($ch);
		}
		curl_close($ch);
		
		if($json){
			$this->last_response = ['date' => date(DateTime::ISO8601), 'result' => json_decode($result)];
			return json_decode($result);
		}

		$this->last_response = ['date' => date(DateTime::ISO8601), 'result' => $result];
		return $result;
	}
	public function array_to_get(array $param){
		if(empty($param)) return false;
		$return = "";
		foreach ($param as $k => $v) {
			$return .= $k."=".$v.'&';
		}
	return substr($return, 0, -1); //On enleve le dernier &
	}
	public function last_url(){
		return $this->last_url;
	}
	public function last_response(){
		return $this->last_response;
	}
	public function last_param(){
		return $this->last_param;
	}
	public function debug($var){
		$debug = debug_backtrace();
		echo '<p>&nbsp;</p><p><a href="#" onclick="$(this).parent().next(\'ol\').slideToggle(); return false;"><strong>' . $debug[0]['file'] . ' </strong> l.' . $debug[0]['line'] . '</a></p>';
		echo '<ol style="display:none;">';
		foreach ($debug as $k => $v) {
			if ($k > 0) {
				echo '<li><strong>' . $v['file'] . '</strong> l.' . $v['line'] . '</li>';
			}
		}
		echo '</ol>';
		echo '<pre>';
		print_r($var);
		echo '</pre>';		
	}
}

$scaleway = new ScalewAPI("XXXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXX");
$scaleway->debug($scaleway->patch("domain/v2alpha2/dns-zones/DOMAINE.COM/records", 
	[
		'changes' => [
			['add' => 
				['records' => 
					[
						[
							"data" => "1.1.1.1", "name" => "@", "priority" => 1, "ttl" => 60, "type" => "A"
						]
					]
				]
			]
		]
	]
));	

$scaleway->debug($scaleway->last_response());
$scaleway->debug($scaleway->last_param());
