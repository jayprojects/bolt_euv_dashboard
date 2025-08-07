<?php

if(!isset($_SESSION)) {
    session_start();
}
if(isset($_GET["pass"]) && ($_GET["token"]=='token')){ 
$_SESSION['auth']="ok";
}

if (!isset($_SESSION['auth']))
{
    header("Location: login.php", true, 301);
	exit();
}

$charge_percentage=0;
$range_miles=0;
$ambient_air_temp=0;
$hybrid_battery_temp=0;

$charging_status="unknown";
$plugged_status="unknown";
$odometer=0;
$internal_voltage=0;
$ev_efficiency=0;
$tokenInFileContent=file_get_contents("onstarstorage/diagnostics.txt");
$reloadData=false;
if(!empty($tokenInFileContent)){
	$diagnosticReportJson = json_decode($tokenInFileContent);
	if(isset($diagnosticReportJson)){
		
		$reportCompletionTime = $diagnosticReportJson->commandResponse->completionTime;
		$reportCompletionTimeInLocal = date("Y-m-d h:i A", strtotime($reportCompletionTime));
		$diffSeconds = strtotime('NOW')-strtotime($reportCompletionTime);
		if($diffSeconds>3600){
			//if more than an hour old, reload data;
			$reloadData=true;
		}
		foreach($diagnosticReportJson->commandResponse->body->diagnosticResponse as $report)
		{
			if($report->diagnosticElement[0]->name=="AMBIENT AIR TEMPERATURE"){
				$ambient_air_temp=intval(intval($report->diagnosticElement[0]->value)*1.8+32);
			}
			else if($report->diagnosticElement[0]->name=="EV BATTERY LEVEL"){
				$charge_percentage=$report->diagnosticElement[0]->value;
			}
			else if($report->diagnosticElement[0]->name=="EV CHARGE STATE"){
				$charging_status=$report->diagnosticElement[0]->value;
			}
			else if($report->diagnosticElement[0]->name=="EV PLUG STATE"){
				$plugged_status=$report->diagnosticElement[0]->value;
			}
			else if($report->name=="VEHICLE RANGE"){
				$range_miles=intval(intval($report->diagnosticElement[2]->value)*0.621371);
			}
			else if($report->diagnosticElement[0]->name=="INTERM VOLT BATT VOLT"){
				$internal_voltage=$report->diagnosticElement[0]->value;
			}
			else if($report->diagnosticElement[0]->name=="HYBRID BATTERY MINIMUM TEMPERATURE"){
				$hybrid_battery_temp=intval(intval($report->diagnosticElement[0]->value)*1.8+32);
			}
			else if($report->name=="ENERGY EFFICIENCY"){
				foreach($report->diagnosticElement as $dElement){
					if($dElement->name=="LIFETIME EFFICIENCY"){
					$ev_efficiency =$dElement->value;
					}
				}
			}
			else if($report->diagnosticElement[0]->name=="ODOMETER"){
				$odometer=$report->diagnosticElement[0]->value;
			}
		}
	}
}


$chargeMode="?";
$rateType="?";
$tokenInFileContent=file_get_contents("onstarstorage/getChargingProfile.txt");
if(!empty($tokenInFileContent)){
	$diagnosticReportJson = json_decode($tokenInFileContent);
	if(isset($diagnosticReportJson)){
		$chargingProfile=$diagnosticReportJson->commandResponse->body->chargingProfile;
		if(isset($chargingProfile)){
			$chargeMode = $chargingProfile->chargeMode;
			$rateType = $chargingProfile->rateType;
		}
	}
}
?>


<!DOCTYPE html>
<html>
   <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <meta name="description" content="">
      <meta name="author" content="">
      <title>My Bolt</title>
      <!-- Custom fonts for this template-->
      
	  
	  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
	  
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
		<link href="css/sb-admin-2.css" rel="stylesheet">
		<link rel="stylesheet" href="css/style.css">
   </head>
   <body id="page-top" class="sidebar-toggled">
		<div>
		
		
		</div>
      <!-- Page Wrapper -->
      <div id="wrapper">
      <div id="content-wrapper" class="d-flex flex-column">
      <!-- Main Content -->
      <div id="content">
      <div class="container-fluid">
      <div class="d-sm-flex align-items-center justify-content-between mb-4">
         <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
		 <div>Report time: <?php echo ($reportCompletionTimeInLocal);?><div id="top_spinner" class="spinner-border" style="display: none;"></div></div>
		 
         <button id="button_refresh_data"  class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">Refresh Data</button>
		 
      </div>
	  
      <div class="row">
         <div class="col-xl-3 col-md-3 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
               <div class="card-body">
                  <div class="row no-gutters align-items-center">
                     <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                           Charge Level &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						   <span ><i class="fa-solid fa-car-battery"></i></i></span>
                        </div>
                        <div >
						<div id="charge_gauge" class="progress">
						  <div class="barOverflow">
							<div class="bar"></div>
						  </div>
						  <div class="progress_lable"><span><?php echo ($charge_percentage);?></span>%</div>
						</div>
						</div>
                     </div>
                     
                  </div>
               </div>
            </div>
         </div>
      
	  <!-- Earnings (Monthly) Card Example -->
         <div class="col-xl-3 col-md-3 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
               <div class="card-body">
                  <div class="row no-gutters align-items-center">
                     <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                           Estimated range &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						   <span><i class="fa-solid fa-road"></i></span>
                        </div>
                        <div>
							<div id="range_gauge" class="progress">
						  <div class="barOverflow">
							<div class="bar"></div>
						  </div>
						  <div class="progress_lable"><span><?php echo ($range_miles);?></span>mi</div>
						</div>
						
						</div>
                     </div>
                     
                  </div>
               </div>
            </div>
         </div>
         <!-- Earnings (Monthly) Card Example -->
         <div class="col-xl-4 col-md-3 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
               <div class="card-body">
                  <div class="row no-gutters align-items-center">
                     <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Charge State &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span><i class="fa-solid fa-charging-station"></i></span>
                        </div>
                       
                           <div id="charge-states-container" class="row">
						   <?php
						   if($plugged_status=="plugged"){
							echo ('
									<div id="charge-plugged-in" class="col-xl-4 col-md-4 charge-state-card"><div class="col-xl-3 col-md-6 mb-4"></div>
									<i style ="color: #5EBF31" class="fa-solid fa-plug-circle-check"></i><br/>			  
										Plugged
									</div>');
						   }else{
							echo ('<div  id="charge-not-plugged-in"class="col-xl-4 col-md-4 charge-state-card"><div class="col-xl-3 col-md-6 mb-4"></div>
									<i style ="color: #E4795D" class="fa-solid fa-plug-circle-xmark"></i><br/>
									Unplugged
								   </div>');
						   }
						   
						   if($charging_status=='charging'){
							echo ('<div  id="charge-charging" class="col-xl-4 col-md-4 charge-state-card"><div class="col-xl-3 col-md-6 mb-4"></div>
									<i  style ="color: #65eF23" class="fa-solid fa-bolt"></i><br/>
									Charging
								   </div>');
						   }else if($charging_status=='not_charging'){
							echo ('<div  id="charge-not-charging" class="col-xl-4 col-md-4 charge-state-card"><div class="col-xl-3 col-md-6 mb-4"></div>
									<img src="img/no-charging.png" alt="Not Charging"/><br/>
									Not Charging
								   </div>');
						   }else if ($charging_status=='charging_complete'){
							   echo ('<div  id="charge-not-charging" class="col-xl-4 col-md-4 charge-state-card"><div class="col-xl-3 col-md-6 mb-4"></div>
								<i style ="color: #5EBF31" class="fa-solid fa-battery-full"></i><br/>
								Charging complete
							   </div>');
						   }
						   
						   ?>
						   <div class="col-xl-12 col-md-12"> Solar sensor:&nbsp;<a href="/solar"><b><span id="solar_charge_data"></span></b></a></div>
                           </div>
                        
                     </div>
                     
                  </div>
               </div>
            </div>
         </div>
         <!-- Pending Requests Card Example -->
         <div class="col-xl-2 col-md-2 mb-2">
            <div class="card border-left-warning shadow h-100 py-2">
               <div class="card-body">
                  <div class="row no-gutters align-items-center">
                     <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                           Temp &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span><i class="fa-solid fa-temperature-high"></i></span>
                        </div>
                        <div id="temp-card" >
						<div>Air: <span class="info-number-lg"><span id="air-temp"><?php echo ($charge_percentage);?></span>&deg;F</span></div>
						<div>Battery: <span class="info-number-lg"><span id="air-temp"><?php echo ($hybrid_battery_temp);?></span>&deg;F</span></div>
						
						</div>
                     </div>
                    
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- Content Row -->
      <div class="row">
         <!-- Area Chart -->
         <div class="col-xl-4 col-lg-4">
            <div class="card shadow mb-4">
               <!-- Card Header - Dropdown -->
               <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                 
               </div>
               <!-- Card Body -->
               <div class="card-body">
			   <div class="row" id="action_icon_container">
                  
				  </div>
				  
               </div>
            </div>
         </div>



		
		<div class="col-xl-3 col-lg-3">
            <div class="card shadow mb-4">
               <!-- Card Header - Dropdown -->
               <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Charging Info</h6>
                 
               </div>
               <!-- Card Body -->
               <div class="card-body">
					<div id="other-charge-info">
                   <ul>
					  <li>Override charging: <b>False</b></li>
					  <li>Charging Profile: 
						<ul>
					  <li>Charge Mode: <b><?php echo ($chargeMode);?></b></li>
					  <li>Rate Type: <b><?php echo ($rateType);?></b></li>
					  </ul></li>
					  
					  <li>Target charge level: <b>?%</b></li>
					  <li>Charge Completed in : <b>? Hours</b></li>
					  <li>Next charge Start: <b>?</b></li>
					  <li>Charge end: <b>?</b></li>
					  <li>Internal volt: <b><?php echo (round($internal_voltage,2));?> v</b></li>
					</ul> 
					</div>
               </div>
            </div>
         </div>
		 

		 
		 
		 <div class="col-xl-3 col-lg-3">
            <div class="card shadow mb-4">
               <!-- Card Header - Dropdown -->
               <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Other Information</h6>
                 
               </div>
               <!-- Card Body -->
               <div class="card-body">
					<div id="other-charge-info">
                   <ul>
					  <li>Odometer: <b><?php echo ($odometer);?> miles</b></li>
					  <li>Lifetime efficiency : <b><?php echo ($ev_efficiency);?> KWh</b></li>
					  <li>Equivalent MPEG: <b>? miles/Gallons</b></li>
					  <li>Last trip distance: <b>? miles</b></li>
					  <li>Tire pressure: <ul>
					  <li>Front-Right: <b>? kPa</b></li>
					  <li>Front-Left: <b>? kPa</b></li>
					  <li>Rear-Right: <b>? kPa</b></li>
					  <li>Rear-Left: <b>? kPa</b></li>
					  </ul></li>
					</ul> 
					</div>
               </div>
            </div>
         </div>
		 <div class="col-xl-12 col-lg-12">
            <div class="card shadow mb-4">
               <!-- Card Header - Dropdown -->
               <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Weather</h6>
                 
               </div>
               <!-- Card Body -->
               <div class="card-body">
					<div id="other-charge-info">
                   <div class="row">
				   
				   <?php

							
					$filePath="onstarstorage/accuweather.txt";
					
					$output="";
					$weather_report_cached_time= filemtime($filePath);
					if((time() - $weather_report_cached_time)>3000){
						//echo ("Getting from accuweaterh");
						$url= 'http://dataservice.accuweather.com/forecasts/v1/hourly/12hour/337608?apikey=apikey&details=true';
						$domain= 'http://dataservice.accuweather.com/';
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, $url);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0');
						curl_setopt($ch, CURLOPT_REFERER, $domain);
						$output = curl_exec($ch);
						file_put_contents($filePath, $output);
						curl_close($ch);     
					}else{
						//echo ("Getting from cached file");
						$output=file_get_contents($filePath);		
					}
					
					for($i=0; $i<6; $i++){
					$w = json_decode($output)[$i];
					//echo($w->EpochDateTime);;
					$timeAmPm =date('h A', $w->EpochDateTime);
					$timeH =intval(date('H', $w->EpochDateTime));
					$temp=$w->Temperature->Value;
					$cloudy=$w->IconPhrase;
					$uvi=0;
					$solar_i=0;
					if($timeH>5 && $timeH<20){
						$uvi= $w->UVIndex;
						$solar_i=$w->SolarIrradiance->Value;
					}
					echo ("<div class='col-xl-2'>");
					echo ("<div>$timeAmPm </div>");
					echo ("<div>$cloudy - $temp&deg;F</div>");
					echo ("<div>$uvi - $solar_i</div>");
					echo ("</div>");
					}
				?>

					</div> 
					</div>
               </div>
            </div>
         </div>
      </div>
	 <div class ="row">
	 <div class="col-xl-12">
	 
            <div class="card shadow mb-12">
               <!-- Card Header - Dropdown -->
               <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Logs</h6>
                 
               </div>
               <!-- Card Body -->
               <div class="card-body">
					<div id="logs"></div>
               </div>
            </div>
         
	</div>
		 <div class="col-xl-12">
	 
            <div class="card shadow mb-12">
               <!-- Card Header - Dropdown -->
               <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Reports</h6>
                 
               </div>
               <!-- Card Body -->
               <div class="card-body">
					<input type="text" id="inpt_report_url" value="" style="width:800px"/>
					<button onclick="getReport()">Get Report</button>
               </div>
            </div>
         
	</div>
	  </div>

      <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js'></script>
      <script src="js/mqttws31.min.js" type="text/javascript"></script>
      <script src="js/script.js?v=2"></script>
      <?php
	  //if($reloadData) echo ("<script>reloaData();</script>");
	  ?>
   </body>
</html>
