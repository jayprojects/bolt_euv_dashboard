<?php
$log_mylog_enabled = true;
$echo_in_log =false;
require 'netutil.php';
$apiPath ="https://api.gm.com/api/v1/";
function decodeJWTResponse($encodedResponse){
	mylog ("decoding response: ".$encodedResponse);
	$decodedResponse = base64_decode($encodedResponse);
	$decodedResponse= substr($decodedResponse, strpos($decodedResponse, "onstar_account_info")-2);
	$decodedResponse= substr($decodedResponse, 0, strrpos($decodedResponse, "}")+1);
	return $decodedResponse;
}

function parseJWTToken($decodedResponse){
	//$decodedResponse=stripslashes(trim($decodedResponse));
	$decodedResponse=preg_replace('/[^a-z0-9_,.=+\/\":{}-]/i','',$decodedResponse);
	mylog ("parsing response: ".$decodedResponse);
	$decodedResponse=substr($decodedResponse, 0, strpos($decodedResponse, "expires_in")+19);
	$jsonResponse = json_decode($decodedResponse);
	$accesstoken= $jsonResponse->access_token;
	mylog("Access token in response: ".$accesstoken."");
	$expiresIn = $jsonResponse->expires_in;
	if(!empty($accesstoken) && (intval($expiresIn)>100)) {
		$acces_token=$accesstoken;
		$acces_token_expire_time=time()+intval($expiresIn);
		mylog ("saving token in file");
		$fileContent = '{"access_token":"'.$acces_token.'", "expires_time":'.$acces_token_expire_time.'}';
		file_put_contents("onstarstorage/token.txt", $fileContent);
		
		mylog ("returning new token ");
		return $accesstoken;
	}
	return "";
}

function getAccessTokenFromFile(){
	mylog ("getting access token from file");
	$tokenInFileContent=file_get_contents("onstarstorage/token.txt");
	if(!empty($tokenInFileContent)){
		$jsonResponse = json_decode($tokenInFileContent);
		$accesstoken= $jsonResponse->access_token;
		$expiresTime = $jsonResponse->expires_time;
		if((intval($expiresTime)-time())>60){
			mylog ("Token found in file");
			return $accesstoken;
		}else{
			mylog ("Token found in file but it expired.");
		}
	}
	return "";
}



function createJWT(){
	mylog ("creating new jwt request token");
	$header = json_encode(['alg' => 'HS256','typ' => 'JWT']);
	$payload = json_encode(["client_id" => "",
						  "device_id"=> "",
						  "grant_type"=> "password",
						  "nonce"=> str_shuffle("ea1c1de764faa8fef6956bde44"),
						  "password"=> "",
						  "scope"=> "onstar gmoc user_trailer user priv",
						  "timestamp"=> gmdate("Y-m-d\TH:i:s\Z"),
						  "username"=> "user@gmail.com"]);
	mylog( "payload: ". $payload);
	$base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
	$base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
	$signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, 'encrypted signature', true);
	$base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
	$jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
	
	mylog (" new jwt request token: ".$jwt);
	return $jwt;
}

function getOnStarToken(){
	$accesstoken= getAccessTokenFromFile();
	if(empty($accesstoken)){
		mylog ("Token not found in file. getting new");
		$jwt = createJWT();
		$header = array('Content-Type: text/plain','Accept-Language: en','User-Agent: myChevrolet/118 CFNetwork/1408.0.4 Darwin/22.5.0');
		$resp= httpPost("https://api.gm.com/api/v1/oauth/token", $jwt, $header);
		$decodedResponse = decodeJWTResponse($resp);
		$accesstoken=parseJWTToken($decodedResponse);
	}
	return $accesstoken;
}

function mylog($text){
	global $log_mylog_enabled;
	global $echo_in_log;
	if($log_mylog_enabled){
		if($echo_in_log){
			echo(date("Y-m-d h:i A")." ::  ".$text."\n<br/>");
		}
		file_put_contents("onstarstorage/log.txt", $text."\n<br/>", FILE_APPEND);
	}
}
?>