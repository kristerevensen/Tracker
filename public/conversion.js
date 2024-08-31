/*
*  MEASURETANK SIMPLE CONVERSION TRACKER
*  2023 Copyright
*  Measuretank.com
*  V1.01
*/

(function() {
    // Hent prosjektkoden fra script-attributtet
    var projectCode = document.currentScript.getAttribute('data-project-code');

    // Hent conversionType og conversionValue fra script-attributtene
    var conversionType = document.currentScript.getAttribute('data-conversion-type');
    var conversionValue = document.currentScript.getAttribute('data-conversion-value');

    // Hent session ID fra det eksisterende sporingsscriptet (antatt satt i localStorage)
    function getSessionId() {
        return localStorage.getItem('mt_session_id');
    }

    // Funksjon for å spore en enkel konvertering
    function trackConversion(conversionType, conversionValue) {
        var sessionId = getSessionId();
        var currentTime = new Date().toISOString();

        var conversionData = {
            eventType: 'conversion',
            session_id: sessionId,
            project_code: projectCode,
            conversionType: conversionType,
            conversionValue: conversionValue,
            timestamp: currentTime,
            pageUrl: window.location.href,
            referrer: document.referrer
        };

        sendConversionData(conversionData);
    }

    // Funksjon for å sende konverteringsdata
    function sendConversionData(data) {
        var blob = new Blob([JSON.stringify(data)], { type: 'application/json' });
        navigator.sendBeacon('https://tracking.measuretank.com/collector', blob);  // Bruk samme endpoint
    }


    // Kjør konverteringssporing umiddelbart ved innlasting av dette skriptet
    trackConversion(conversionType, conversionValue);
})();
