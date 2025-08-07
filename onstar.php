<?php
if(isset($_GET['token']) && ($_GET['token'] == "token")&& isset($_GET['command'])){
	
	require 'jwtutil.php';
	$command = $_GET['command'];
	$resp="";
	
	if($command=="c_lock"){
		$resp=commonCommands_part1_url("lockDoor", '{"delay" : 0}');
	}else if($command=="c_unlock"){
		$resp=commonCommands_part1_url("unlockDoor", '{"delay" : 0}');
	}else if($command=="c_alarm"){
		$resp=commonCommands_part1_url("alert", '{"alertRequest":{"action":["Honk","Flash"],"delay":0,"duration":1,"override":["DoorOpen","IgnitionOn"]}}');
	}else if($command=="c_cancel_alarm"){
		$resp=commonCommands_part1_url("cancelAlert", '{}');
	}else if($command=="c_start"){
		$resp=commonCommands_part1_url("start", '');
	}else if($command=="c_stop"){
		$resp=commonCommands_part1_url("cancelStart", '');
	}else if($command=="c_refresh_cherging_profile"){
		$resp=commonCommands_part1_url("getChargingProfile", '');
	}else if($command=="c_refresh_data"){
		$body= '{"diagnosticsRequest":{"diagnosticItem":["ENERGY EFFICIENCY","HYBRID BATTERY MINIMUM TEMPERATURE","EV BATTERY LEVEL","ODOMETER","EV PLUG STATE","EV CHARGE STATE","AMBIENT AIR TEMPERATURE","INTERM VOLT BATT VOLT","VEHICLE RANGE"]}}';
		$resp=commonCommands_part1_url("diagnostics", $body);
	}else if($command=="c_charge_overried"){
		$resp=commonCommands_part1_url("chargeOverride",'{"chargeOverrideRequest":{"mode":"CHARGE_NOW"}}');
	}else if($command=="c_cancel_charge_overried"){
		$resp=commonCommands_part1_url("chargeOverride", '{"chargeOverrideRequest":{"mode":"CANCEL_OVERRIDE"}}');
	}else if($command=="c_charge_mode_immediate"){
		$resp=commonCommands_part1_url("setChargingProfile", '{"chargingProfile":{"chargeMode":"IMMEDIATE","rateType":"MIDPEAK"}}');
	}else if($command=="c_charge_mode_peak"){
		$resp=commonCommands_part1_url("setChargingProfile", '{"chargingProfile":{"chargeMode":"RATE_BASED","rateType":"PEAK"}}');
	}else if($command=="c_charge_mode_offpeak"){
		$resp=commonCommands_part1_url("setChargingProfile", '{"chargingProfile":{"chargeMode":"RATE_BASED","rateType":"OFFPEAK"}}');
	}else if($command=="c_stop_charge"){
		$resp=commonCommands_part1_url("stopCharge", '');		
	}else if($command=="get_report"){
		$reportUrl=base64_decode($_GET['url']);
		if(!empty($reportUrl)){
			$fileName=$_GET['filename'];
			$resultRequestSleep=intval($_GET['sleep']);
			mylog("reporturl: $reportUrl filename: $fileName resultRequestSleep: $resultRequestSleep");
			$resp=commonCommands_part2_report($reportUrl,$fileName,$resultRequestSleep);
		}else{
			$resp="empty report url";
		}
	}
	echo($resp);
	
}

function commonCommands_part1_url($command, $body){
	$accessToken = getOnStarToken();
	if(!empty($accessToken)){
		$header = array(
			'Accept-Language: en',
			'User-Agent: myChevrolet/118 CFNetwork/1408.0.4 Darwin/22.5.0',
			'Accept: application/json',
			'content-type: application/json; charset=UTF-8',
			'Authorization: Bearer '.$accessToken,
			'Host: api.gm.com',
			'Accept-Encoding: br, gzip, deflate'
				);
		//$body= '{"delay" : 0}';
		$resp = httpPost("https://api.gm.com/api/v1/account/vehicles/{car's VIN}/commands/".$command, $body, $header);
		mylog ("resp: ".$resp);
		
		$commandResponseObject = json_decode($resp);
		if(isset($commandResponseObject) &&  isset($commandResponseObject->commandResponse) && isset($commandResponseObject->commandResponse->url)){
			return $commandResponseObject->commandResponse->url;
		}else{
			return "No report url. Invalid response from the api";
		}
	
	}else{
		return "Access token not found";
	}
	
}

function commonCommands_part2_report($report_url, $filename, $resultRequestSleep){
	$accessToken = getOnStarToken();
	if(!empty($accessToken) && !empty($report_url)){
		$header = array(
			'Accept-Language: en',
			'User-Agent: myChevrolet/118 CFNetwork/1408.0.4 Darwin/22.5.0',
			'Accept: application/json',
			'content-type: application/json; charset=UTF-8',
			'Authorization: Bearer '.$accessToken,
			'Host: api.gm.com',
			);
		$status="unknown";
		for ($i = 0; $i < 3; $i++) { 
			
			$resp= httpGet($report_url, $header );
			mylog ($resp);
			
			$commandResponseObject = json_decode($resp);
			if ( isset($commandResponseObject) && isset($commandResponseObject->commandResponse->status)){
				$status=$commandResponseObject->commandResponse->status;
				if($status=="success"){
					if(!empty($filename) &&!empty($resp) ){
						file_put_contents("onstarstorage/".$filename, $resp);
					}
					return $resp;
				}
			}
			if($status!="inProgress"){
				$i=10;
			}
			sleep($resultRequestSleep);
		}
		return "Command timed out with status: ".$status."\n<br/>Response: ".$resp;
	}else{
		return "Access token not found. or Report url not valid";
	}
}

?>
