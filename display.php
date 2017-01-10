<?php
/**
 * User: Jimmy McNatt
 * Date: 11/18/2015
 * Time: 11:32 AM
 */
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="./css/display.css" />
        <title></title>
        <script src="https://code.jquery.com/jquery-latest.js"></script>
        <script src="./scripts/display.js"></script>
		
		<!-- FullCalendar and Scheduler files -->
		<link href='cal/assets/fullcalendar.css' rel='stylesheet' />
		<script src='cal/lib/jquery.min.js'></script>
		<script src='cal/lib/moment.min.js'></script>
		<script src='cal/assets/fullcalendar.js'></script>
		<script type='text/javascript'>
			$(document).ready(function() 
			{
				var requestUrl = 'http://api.rit.edu/v1/rooms/070-2320/meetings?RITAuthorization=95da72f6e649aff4f45405cded98a109cb1514da';
				var request = new XMLHttpRequest();
				request.open('GET', requestUrl);
				request.responseType = 'json';
				request.send();

				//variable that contains all events on the current day in completed JSON format
				var todaysEventsJSON = '';
				
				
				request.onload = function() {
					var data = request.response;
				
					var body = document.querySelector('body');
					var test = document.createElement('p');
					//test.textContent = JSON.stringify(data);
					//body.appendChild(test);
					
					//Working on getting the values from the data into the correct JSON format needed for fullCalendar
					var dataObj = JSON.parse(JSON.stringify(data));
					
					//Today's Date
					//var todayDate = moment().format('YYYY-MM-DD');
					var todayDate = '2016-10-28';
					var todayTime = moment().format('HH:MM:SS');
					
					
					//JSON object creation for the today's current events
					var todaysEventsObj = '';
					
					
					
					//Display calendar -- pass in objects
    				$('#calendar').fullCalendar({
						defaultView: 'agendaDay',
						header:{
							left: false,
							center: false,
							right: false
						},
						defaultDate: '2016-10-28',
						minTime: '08:00:00',
						maxTime: '22:00:00',
						contentHeight: 'auto',
						allDaySlot: false,
						slotDuration: '00:60:00'
					
					});
					
					
					//Date and time of all events
					for(var i=0, len = dataObj.data.length; i < len; i++){
						//document.getElementById('testingID').innerHTML += dataObj.data[i].date + ' ' + dataObj.data[i].start + '</br>';
						var eventStartDate = dataObj.data[i].date;
						
						//is the event on the currentDate
						if (todayDate == eventStartDate){
							//document.getElementById('testingID').innerHTML += dataObj.data[i].date + ' ' + dataObj.data[i].start + '</br>';
							
							var eventStart = dataObj.data[i].date + ' ' + dataObj.data[i].start;
							var eventEnd = dataObj.data[i].date + ' ' + dataObj.data[i].end;
							
							//creation of the events object in the format fullcalendar requires
							todaysEventsObj = todaysEventsObj.concat("{title: '"+ dataObj.data[i].meeting + "',start:'" + eventStart + "', end: '" + eventEnd + "'},");
							var eventObj = '{"title": "'+ dataObj.data[i].meeting + '","start":"' + eventStart + '", "end": "' + eventEnd + '"}';
							var finalEventObj = JSON.parse(eventObj);
							$('#calendar').fullCalendar( 'renderEvent', finalEventObj);														
						}
					}
					todaysEventsJSON = todaysEventsObj.slice(0,-1);					
					console.log(eventObj);					
				}					
			});

		</script>
    </head>
    <body>
        <div class="container">
            <div id="top" class="top-container">
                <div id="top" class="top-title">
                    <span class="top-title-text">Projects Lab</span>
                </div>
                <div id="top" class="top-status open">
                    <span class="top-status-text">Closed</span>
                </div>
            </div>
            <div id="middle" class="middle-container">
                <div id="middle" class="middle-labassistants">
				
				<!--Calendar Here-->
				<div id='calendar'></div>
				<?php
				//reach api and copy contents into a var
				//$url = "http://api.rit.edu/v1/rooms/070-2160/meetings?RITAuthorization=95da72f6e649aff4f45405cded98a109cb1514da";
				//$json = file_get_contents($url);
				//$json_data = json_decode($json, true);
				//var_dump($json_data);
				//echo "My token: ". $json_data["access_token"];
				
				//current date
				//$currentDate = date("Y-m-d");
				//$currentDate = '2016-12-06';
				
				//get data from the array
				//foreach ($json_data[data] as $event){
					
					//only display if events are on the current date
					//if($event['date'] === $currentDate){
							//echo 'Date: '.$event['date'];
							//echo ', Start Time: '.$event['start'];
							//echo ', End Time: '.$event['end'];
							//echo ', Meeting: '.$event['meeting'];
							//echo ', Meeting Type: '.$event['meetingtype'];
							//echo ', Room Number: '.$event['room']['room'].'<br/>';
					//}
				//}
				
				
				?>
	
			
				
                </div>
                <div id="middle" class="middle-teachingassistants">

                </div>
            </div>
            <div id="bottom" class="bottom-container">
                 <!-- Labbies and TA's 
                <div id="bottom" class="bottom-staff">
                   
					
                </div> -->
				<div id="bottom" class="bottom-clock">
                    <p id="clock"></p>
                    <p id="fulldate"></p>
                </div>
            </div>
        </div>
    </body>
</html>

