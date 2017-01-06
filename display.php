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

				<?php
				//reach api and copy contents into a var
				$url = "http://api.rit.edu/v1/rooms/070-2160/meetings?RITAuthorization=95da72f6e649aff4f45405cded98a109cb1514da";
				$json = file_get_contents($url);
				$json_data = json_decode($json, true);
				//var_dump($json_data);
				//echo "My token: ". $json_data["access_token"];
				
				//current date
				//$currentDate = date("Y-m-d");
				$currentDate = '2016-12-06';
				
				//get data from the array
				foreach ($json_data[data] as $event){
					
					//only display if events are on the current date
					if($event['date'] === $currentDate){
							echo 'Date: '.$event['date'];
							echo ', Start Time: '.$event['start'];
							echo ', End Time: '.$event['end'];
							echo ', Meeting: '.$event['meeting'];
							echo ', Meeting Type: '.$event['meetingtype'];
							echo ', Room Number: '.$event['room']['room'].'<br/>';
					}
				}
				
				
				?>
	
			
				
                </div>
                <div id="middle" class="middle-teachingassistants">

                </div>
            </div>
            <div id="bottom" class="bottom-container">
                
                <div id="bottom" class="bottom-staff">
                    <!-- Labbies and TA's -->
					
                </div>
				<div id="bottom" class="bottom-clock">
                    <p id="clock"></p>
                    <p id="fulldate"></p>
                </div>
            </div>
        </div>
    </body>
</html>

