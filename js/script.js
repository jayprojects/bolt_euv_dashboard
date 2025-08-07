
let action_items = [
  {
    "id": "c_lock",
    "title": "Lock",
	"icon": "fa-lock",
	"color": "#5FC1F2",
    "sleep": 20,
    "filename": ""
  },
  {
    "id": "c_unlock",
    "title": "Unlock",
	"icon": "fa-lock-open",
	"color": "#E8CB38",
    "sleep": 10,
    "filename": ""
  },
{
    "id": "c_start",
    "title": "Start",
	"icon": "fa-power-off",
	"color": "#1261C8",
    "sleep": 30,
    "filename": ""
  },
{
    "id": "c_stop",
    "title": "Stop",
	"icon": "fa-power-off",
	"color": "#E8CB38",
    "sleep": 30,
    "filename": ""
  },
{
    "id": "c_alarm",
    "title": "Alarm",
	"icon": "fa-car-on",
	"color": "#F5904D",
    "sleep": 20,
    "filename": ""
  },
{
    "id": "c_cancel_alarm",
    "title": "Cancel Alarm",
	"icon": "fa-car",
	"color": "#33A04D",
    "sleep": 10,
    "filename": ""
  },
{
    "id": "c_charge_overried",
    "title": "Charge Override",
	"icon": "fa-bolt-lightning",
	"color": "#33A04D",
    "sleep": 30,
    "filename": ""
  },
{
    "id": "c_cancel_charge_overried",
    "title": "Cancel Charge Override",
	"icon": "fa-bolt-lightning",
	"color": "#33A04D",
    "sleep": 30,
    "filename": ""
  },
{
    "id": "c_charge_mode_peak",
    "title": "Set Peak rate charge",
	"icon": "fa-bolt-lightning",
	"color": "#33A04D",
    "sleep": 30,
    "filename": ""
  },
{
    "id": "c_charge_mode_offpeak",
    "title": "Set Offpeak rate charge",
	"icon": "fa-bolt-lightning",
	"color": "#33A04D",
    "sleep": 30,
    "filename": ""
  },
{
    "id": "c_charge_mode_immediate",
    "title": "Set charge immediate",
	"icon": "fa-bolt-lightning",
	"color": "#33A04D",
    "sleep": 30,
    "filename": ""
  }
  ,
{
    "id": "c_stop_charge",
    "title": "Stop Charge",
	"icon": "fa-bolt-lightning",
	"color": "#33A04D",
    "sleep": 30,
    "filename": ""
  }
  
];

$("#charge_gauge").each(function(){

  var $bar = $(this).find(".bar");
  var $val = $(this).find("span");
  var perc = parseInt( $val.text(), 10);

  $({p:0}).animate({p:perc}, {
    duration: 3000,
    easing: "swing",
    step: function(p) {
      $bar.css({
        transform: "rotate("+ (45+(p*1.8)) +"deg)", // 100%=180° so: ° = % * 1.8
        // 45 is to add the needed rotation to have the green borders at the bottom
      });
      $val.text(p|0);
    }
  });
});

$("#range_gauge").each(function(){

  var $bar = $(this).find(".bar");
  var $val = $(this).find("span");
  
  var perc = parseInt( $val.text(), 10)/3.3;

  $({p:0}).animate({p:perc}, {
    duration: 3000,
    easing: "swing",
    step: function(p) {
      $bar.css({
        transform: "rotate("+ (45+(p*1.8)) +"deg)", // 100%=180° so: ° = % * 1.8
        // 45 is to add the needed rotation to have the green borders at the bottom
      });
      //$val.text(p|0);
    }
  });
});
function addActionButtons(){
	$.each(action_items, function (index, value) {
		$("#action_icon_container").append('<div class="col-xl-2 col-md-2"><div id="'+value.id+'" data-sleep="'+value.sleep+'" data-filename="'+value.filename+'" class="action-icon"><i style ="color: '+value.color+'" class="fa-solid '+value.icon+'"></i><br/>'+value.title+'</div></div>');
	});
	$("#action_icon_container").append('<div class="col-xl-2 col-md-2"><div id="action_spinner" data-sleep="30" data-filename="" class="spinner-border" style="display: none;"></div></div>');
	
	$(".action-icon").click(function(){

	$("#action_spinner").show();
	let c = $(this).attr('id');
	let sleep = $(this).attr('data-sleep');
	let filename = $(this).attr('data-filename');
	$("#logs").append("<p>Executing command: "+ c+"</p>");
	let url="onstar.php?token=token&command="+c;
	$.get(url, function(data, status){
		$("#logs").append("<p>Request In progress. Report URL: "+data+"</p>");
		
		$("#action_spinner").hide();
		/*
		$("#logs").append("<p>Will wait "+sleep+" seconds then fetch the report </p>");
		setTimeout(() => {
			$("#action_spinner").show();
			let url="onstar.php?token=token&command=get_report&sleep="+sleep+"&url="+btoa(data)+"&filename="+filename;
		  $.get(url, function(data, status){
			$("#action_spinner").hide();
			$("#logs").append("<p>Result: "+data+"</p>");
		  });
		}, ""+sleep+"000");
		*/
  });
}); 
	
}
addActionButtons();
		  



function refresh_solar_charging_info(){
	$.get("http://mysite.com/solar/api.php?token=token&command=get_car_charging_info", function(data, status){
		$("#solar_charge_data").text(data);
	});
	setTimeout(() => {
	  refresh_solar_charging_info();
	}, "20000");

}
  

//refresh_solar_charging_info();



$("#button_refresh_data").click(function(){
reloaData();
});
function reloaData(){
	$("#top_spinner").show();
	$.get("onstar.php?token=token&command=c_refresh_data", function(data, status){
		$("#logs").append("<p>Diagnostic Data is being refreshed. Report URL: "+data+"</p>");
		$("#top_spinner").hide();
		setTimeout(() => {
			let url="onstar.php?token=token&command=get_report&sleep=50&filename=diagnostics.txt&url="+btoa(data);
		  $.get(url, function(data, status){
			$("#logs").append("<p>New Diagnostic Data is ready: "+data+"</p>");
			reloaChargingProfileData();
			//$("#logs").append("<p>This page will be refreshed in 5 seconds: "+data+"</p>");
			//setTimeout(() => {location.reload();}, "5000");
		  });
		}, "60000");
	});
}

function reloaChargingProfileData(){
	$("#top_spinner").show();
	$.get("onstar.php?token=token&command=c_refresh_cherging_profile", function(data, status){
		$("#logs").append("<p>Charging profile Data is being refreshed. Report URL: "+data+"</p>");
		$("#top_spinner").hide();
		setTimeout(() => {
			let url="onstar.php?token=token&command=get_report&sleep=50&filename=getChargingProfile.txt&url="+btoa(data);
		  $.get(url, function(data, status){
			$("#logs").append("<p>New charging profile Data is ready: "+data+"</p>");
			
			$("#logs").append("<p>This page will be refreshed in 5 seconds: "+data+"</p>");
			setTimeout(() => {location.reload();}, "5000");
		  });
		}, "60000");
	});
}

function getReport(){
	let data= $("#inpt_report_url").val();
	let url="onstar.php?token=token&command=get_report&sleep=50&filename=ondemand_report.txt&url="+btoa(data);
	$("#action_spinner").show();
	$.get(url, function(data, status){
		$("#action_spinner").hide();
		$("#logs").append("<p>Result: "+data+"</p>");
	  });
}



function connectmqtt() {
   clientID = "clientID - " + parseInt(Math.random() * 100);
   client = new Paho.MQTT.Client("mysite.com", 8087, clientID);
   client.onConnectionLost = onConnectionLost;
   client.onMessageArrived = onMessageArrived;
   client.connect({
        onSuccess: onConnect,
        userName: "mosa",
        password: "joy123m"
    });

}

function onConnect() {
   topic = "esphome-test-77420/sensor/output_active_power/#";
   //console.log("Subscribing to topic " + topic);
   client.subscribe(topic);
}


function onConnectionLost(responseObject) {
   console.log("ERROR: Connection is lost.");
   if (responseObject != 0) {
      console.log(responseObject.errorMessage);
   }
}

function onMessageArrived(message) {
console.log(message.payloadString);
$("#solar_charge_data").html("Charging at "+message.payloadString+" watts");
}

 connectmqtt();