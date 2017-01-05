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
						//echo 'Date: '.$event['date'];
						//echo ', Start Time: '.$event['start'];
						//echo ', End Time: '.$event['end'];
						//echo ', Meeting: '.$event['meeting'];
						//echo ', Meeting Type: '.$event['meetingtype'];
						//echo ', Room Number: '.$event['room']['room'].'<br/>';
					}
				}
				
				
				?>
	
			<table>
				<tr>
					<th>Time</th>
					<th>Class Schedule</th>
				</tr>
				<tr>
					<td>8:00am</td>
					<td>No class</td>
				</tr>
				<tr>
					<td>9:00am</td>
					<td>No class</td>
				</tr>
				<tr>
					<td>10:00am</td>
					<td>No class</td>
				</tr>
				<tr>
					<td>11:00am</td>
					<td>No class</td>
				</tr>
				<tr>
					<td>12:00pm</td>
					<td>No class</td>
				</tr>
				<tr>
					<td>1:00pm</td>
					<td>No class</td>
				</tr>
				<tr>
					<td>2:00pm</td>
					<td>No class</td>
				</tr>
				<tr>
					<td>3:00pm</td>
					<td>No class</td>
				</tr>
				<tr>
					<td>4:00pm</td>
					<td>No class</td>
				</tr>
				<tr>
					<td>5:00pm</td>
					<td>No class</td>
				</tr>
				<tr>
					<td>6:00pm</td>
					<td>No class</td>
				</tr>
				<tr>
					<td>7:00pm</td>
					<td>No class</td>
				</tr>
				<tr>
					<td>8:00pm</td>
					<td>No class</td>
				</tr>
				<tr>
					<td>9:00pm</td>
					<td>No class</td>
				</tr>
			</table>
				
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

