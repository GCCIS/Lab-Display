<?php
/**
 * User: Jimmy McNatt
 * Date: 11/18/2015
 * Time: 11:32 AM
 */
 
	//Gather lab information from the URL
	/*
	* URL must follow these guidelines
	* /display.php?room=070-XXXX&name=Mac+Lab+1
	*/
	$roomNumber = $_GET['room'];
	$roomName = $_GET['name'];
 
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="./css/display.css" />
        <title>Lab-Display</title>
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
				//insert the room number from the URL to request JSON
				var requestUrl = 'http://api.rit.edu/v1/rooms/'+'<?php echo $roomNumber; ?>'+'/meetings?RITAuthorization=95da72f6e649aff4f45405cded98a109cb1514da';
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
					
					//Contains all the data from the request URL in 
					var dataObj = JSON.parse(JSON.stringify(data));
					
					//Today's Date and Time
					//var todayDate = moment().format('YYYY-MM-DD');
					var todayDate = '2017-02-01';
					
					/* NOT THE CORRECT MINUTES */
					var todayTime = moment().format('HH:MM:SS');
					
					
					
					
					//Display the calendar and define defaults
    				$('#calendar').fullCalendar({
						defaultView: 'agendaDay',
						header:{
							left: false,
							center: false,
							right: false
						},
						defaultDate: '2017-02-01',
						minTime: '08:00:00',
						maxTime: '22:00:00',
						contentHeight: 'auto',
						allDaySlot: false,
						eventColor: '#7F1F00'
					
					});
					
					//array to determine the lab status
					var todaysEventTimesArr = [];
							
					//Date and time of all events
					for(var i=0, len = dataObj.data.length; i < len; i++){
						//The Date that the event will begin
						var eventStartDate = dataObj.data[i].date;
						
						//check if the event is on the currentDate -- only render events that will show to save time
						if (todayDate == eventStartDate){
							//Create a proper string of when the events are beginning and ending
							var eventStart = dataObj.data[i].date + ' ' + dataObj.data[i].start;
							var eventEnd = dataObj.data[i].date + ' ' + dataObj.data[i].end;
							
							//creation of the events object in the format fullcalendar requires
							var eventObj = '{"title": "'+ dataObj.data[i].meeting + '","start":"' + eventStart + '", "end": "' + eventEnd + '"}';
							var finalEventObj = JSON.parse(eventObj);
							//Add events to the fullcalendar 
							$('#calendar').fullCalendar( 'renderEvent', finalEventObj);	
							
							
							//working on determining the lab status by adding start and end times to an array
							var eventStartTime = dataObj.data[i].start;
							var eventEndTime = dataObj.data[i].end;
							//even indexes are start -- odd indexes are end
							todaysEventTimesArr.push(eventStartTime, eventEndTime);
							
						}
					
							
					}//end of for
					
					//go through  events that were on today -- todaysEventTimesArr
					//Determine Labstatus ---- *****NOT YET WORKING****
					var labStatus = 'No Status';
					for(var i=0, len = todaysEventTimesArr.length; i < len; i++){	
						//then lab is closed
						
						if(todayTime>=todaysEventTimesArr[i] && todayTime<=todaysEventTimesArr[i+1]){
							labStatus = 'Closed';
							console.log('Closed '+ todayTime);
							return labStatus;
						}
						//then lab is open
						else{
							labStatus = 'Open';
							console.log('Open '+ todayTime);
							return labStatus;
						}
					}
					console.log(todaysEventTimesArr);
				}//end of request.onload()					
			});//end of .ready()
			

		</script>
    </head>
  
  <body>
        <div class="container">
            <div id="top" class="top-container">
                <div id="top" class="top-title">
					<!-- Room name gathered from the URL is placed on the page -->
                    <span class="top-title-text"><?php echo $roomName; ?></span>
                </div>
                <div id="top" class="top-status open">
                    <span class="top-status-text">Closed</span>
                </div>
            </div>
				
				<!--Calendar Here-->
				<div id='calendar'></div>
				<div id='labStaffContainer'>
					<div id='labStaff'>

						<?php
							define('SCHEDULE_NAME_LAB_ASSISTANT', 'Lab Assistant');
							define('SCHEDULE_NAME_TEACHING_ASSISTANT', 'Teaching Assistant');
							include("modules/simple_html_dom.php");
							$html = file_get_html("http://work.cias.rit.edu/ist/widgets/unifiedschedule");
							/*
							 * <table><tr> in the widgetContent
							 * The result should be a <td> which contains tables
							 * Each table is a schedule
							 */
							$widgetCells = $html->find("table#widgetContent tr td");
							
							foreach ($widgetCells as $widgetCell) {	
								
								/* Retrieve Lab Assistant Schedule */
								/* The title should be in the <th> */
								$title = $widgetCell->find('th', 0)->plaintext;
								/* Test to see if this is the lab assistant table */
								if (stripos($title, SCHEDULE_NAME_LAB_ASSISTANT, 0) !== false) {
									
									/*
									 * A span that contains the lab assistant's name should look like this
									 * <di  class='list-group-item' style='background-color: #B39EB5'>
									 * <strong>
									 * <span id='replaceme' />Kyle Bansavage
									 * </strong>
									 */
									$labAssistants = $widgetCell->find('div.list-group-item strong');
									
									foreach ($labAssistants as $labAssistant) {
										$labAssistantName = $labAssistant->plaintext;
										
										if($labAssistantName == 'NOT COVERED'){
											array_pop($labAssistantResults);
										}
										else{
											$labAssistantResults[] = $labAssistantName;
										}
									}
									
								}
									
								/* Retrieve Teaching Assistant Schedule */
								/* The title should be in the <th> */	
								$title = $widgetCell->find('th', 0)->plaintext;
								/* Test to see if this is the teaching assistant table */
								if (stripos($title, SCHEDULE_NAME_TEACHING_ASSISTANT, 0) !== false) {
									/* TA Names */
									$TAs = $widgetCell->find('div.list-group-item strong');
									foreach ($TAs as $TA) {
										$TAName = $TA->plaintext;
										
										if(TAName == 'NOT COVERED'){
											array_pop($teachingAssistantResults);
										}
										else{
											$teachingAssistantResults[] = $TAName;
										}
									}
								}
								
								
							}
							
						?>
						<table id="labAssistantTable">
							<tr>
								<th>Lab Assistants</th>
							</tr>
							<?php
							
								if(!empty($labAssistantResults)){
									foreach($labAssistantResults as $v){
										if($v == 'NOT COVERED'){
											echo '<tr><td>'.$v.'</td></tr>';
										}
										else{
											echo '<tr><td>'.$v.'</td></tr>';
										}
									}
								}
								else{
									 echo '<tr><td>No Labbie Available</td></tr>';
								}
							?>
						</table>
						<table id="teachingAssistantTable">
							<tr>
								<th>Teaching Assistants</th>
							</tr>
							<?php
								if(!empty($teachingAssistantResults)){
									foreach($teachingAssistantResults as $v){
										echo '<tr><td>'.$v.'</td></tr>';
									}
								}
								else{
									echo '<tr><td>No TA Available</td></tr>';
								}
							?>
						</table>
					</div>
				</div>
				<div class="bottom-clock">
                    <p id="clock"></p>
                    <p id="fulldate"></p>
                </div>
        </div>
    </body>
</html>

