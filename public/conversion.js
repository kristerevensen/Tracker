/*
*  MEASURETANK SIMPLE CONVERSION TRACKER
*  2023 Copyright
*  Measuretank.com
*  V1.03 ALPHA
*/

(function() {
    // Get project code from script attribute
    var goal_uuid = document.currentScript.getAttribute('data-conversion-uuid');

    // Function to retrieve the session ID from the existing tracking script (assumed to be set in localStorage)
    function getSessionId() {
        return localStorage.getItem('mt_session_id');
    }

    // Function to track a simple conversion
    function trackConversion(goal_uuid) {
        var sessionId = getSessionId();
        var currentTime = new Date().toISOString();

        var conversionData = {
            eventType: 'conversion',
            session_id: sessionId,
            goal_uuid: goal_uuid,
            timestamp: currentTime,
            pageUrl: window.location.href,
            referrer: document.referrer
        };
        console.log(conversionData);

        sendConversionData(conversionData);
    }

    // Function to send conversion data via a 1x1 pixel image request
    function sendConversionData(data) {
        var baseUrl = 'https://tracking.measuretank.com/collector';
        var params = [];

        // Convert the data object to URL parameters
        for (var key in data) {
            if (data.hasOwnProperty(key)) {
                params.push(`${encodeURIComponent(key)}=${encodeURIComponent(data[key])}`);
            }
        }

        // Create the full URL with query parameters
        var url = `${baseUrl}?${params.join('&')}`;

        // Create and load the tracking pixel (1x1 image)
        var trackingPixel = new Image(1, 1);
        trackingPixel.src = url;
        console.log(trackingPixel);

        // Optionally, append the image to the body (it won't be visible)
        document.body.appendChild(trackingPixel);
    }

    // Trigger conversion tracking immediately upon loading this script
    trackConversion(goal_uuid);
})();
