$(document).ready(function() {
    getClock();
});

/**
 * Gets the clock and initiates a continuous refresh of the current time
 * Gets the CSS #clock to the value of the current time
 */
function getClock() {
    // Get current data, hour, and minute
    var clock = new Date();
    var h = clock.getHours();
    var m = clock.getMinutes();
    var day = clock.getDate();
    var month = clock.getMonth() + 1;
    var year = clock.getFullYear();
    var suffix = "AM";

    // If the time is past noon, correct for 12 hour time instead of 24 hour time
    if (h > 12) {
        h -= 12;
        suffix = "PM";
    }

    else if (h == 0) {
        h = 12;
    }

    //Adds a 0 to single digit times
    if (m < 10){
        m = "0" + m;
    }

    $("#clock").html(h + ':' + m + ' ' + suffix);
    $("#fulldate").html(month + '/' + day + '/' + year);

    setTimeout(function() {
        getClock()
    }, 1000);
}