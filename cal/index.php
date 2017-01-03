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

				request.onload = function() {
					var data = request.response;
				
					var body = document.querySelector('body');
					var test = document.createElement('p');
					test.textContent = JSON.stringify(data);
					body.appendChild(test);
				}	

    				$('#calendar').fullCalendar({		
					header: '',
					defaultView: 'listDay'
				})
			});
		</script>
	</head>
	<body>
		<div id='calendar'></div>
	</body>
</html>
