# IST Lab Display
This lab display will be shown on the screens outside of the IST labs.
## Accessing the URL
In order to access the lab display you must locate the URL it is stored on and go to display.php. The URL must include the room number that you need to display. For example:
```
display.php?room=070-XXXX
``` 
If the room name does not show up first check the room number you provided to ensure it's accuracy. Then check the Lab_Informaiton file to make sure the lab and it's information is within the csv file. If the room number is not i the file you must add it to the file for the page to load properly.

## Updating/Viewing the lab data
The lab data is stored within the Lab_Information.csv file stored on the server. In order to update/add/delete the lab number, name, lab default, open time, or close time you must change the values in Lab_Information.csv. For example one lab might have a room similar to the following:
```
070-3690,Mac Lab 2,closed,08:00,22:00
```
The room number must contain the building number (070 for GCCIS). The room name should be a reasonable length to identify the lab. The lab default is based on whether the lab should be open or closed if there are not currently classes being held within the room. The open and close time must be in 24hr format with a 0 prefix for times less than 10:00 hours.

## Author
* **Amber Libby**