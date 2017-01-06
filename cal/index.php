<!DOCTYPE html>
<html>
	<head>
		<link rel='stylesheet' href='assets/fullcalendar.css' />
		<script src='lib/jquery.min.js'></script>
		<script src='lib/moment.min.js'></script>
		<script src='assets/fullcalendar.js'></script>
		<script type='text/javascript'>
			function processData(eventData) {
				var events = eventData['members'];
				var today = new Date();
				for (i = 0; i < events.length; i++) {
					var eventDate = new Date(events[i].date);
				}
			}

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
						defaultView: 'basicDay',
						header:{
							left: false,
							center: false,
							right: false
						}
					
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
		<div id='calendar'></div>
		<div id='testingID'></div>
	</body>
</html>
