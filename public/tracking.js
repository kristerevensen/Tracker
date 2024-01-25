/*
*  MEASURETANK TRACKER
*  2023 Copyright
*  Measuretank.com
*  V2.02 ALPHA
*/

(function() {
    var projectCode = document.currentScript.getAttribute('data-project-code');
    //console.log('Start:');
    //console.log('Project: '+projectCode);

    //function getSessionId() {
     ////   var sessionId = localStorage.getItem('mt_session_id');
     //   if (!sessionId) {
     //       sessionId = Math.random().toString(36).substring(2, 15);
     //       localStorage.setItem('mt_session_id', sessionId);
     //   }
     //   //console.log('SessionID: '+sessionId);
     //   return sessionId;
    //}

    function getSessionId() {
        var sessionId = localStorage.getItem('mt_session_id');
        var timestamp = localStorage.getItem('mt_session_timestamp');
        var currentTime = new Date().getTime();

        // Sjekk om sessionId er satt og om det har gått mer enn 30 minutter (1800000 millisekunder)
        if (!sessionId || !timestamp || currentTime - parseInt(timestamp) > 1800000) {
            sessionId = Math.random().toString(36).substring(2, 15);
            localStorage.setItem('mt_session_id', sessionId);
            localStorage.setItem('mt_session_timestamp', currentTime.toString());
        } else {
            // Oppdaterer tidsstempelet for aktiv bruk
            localStorage.setItem('mt_session_timestamp', currentTime.toString());
        }

        return sessionId;
    }

    //function collectLinkClicks() {
    //    document.querySelectorAll('a').forEach(function(link) {
    //        link.addEventListener('click', function() {
//
    //            var sendData2 = sendData({ eventType: 'linkClick', linkUrl: link.href });
    //            //console.log(sendData2);
    //        });
    //    });
    //}

    //function collectFormSubmissions() {
    //    document.querySelectorAll('form').forEach(function(form, index) {
    //        form.addEventListener('submit', function() {
    //            sendData({
    //                eventType: 'formSubmit',
    //                formDetails: {
     //                   name: form.name,
    //                    id: form.id,
    //                    elementCount: form.elements.length
    //                }
   //             });
   //         });
   //     });
   // }

   function collectFormSubmissions() {
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function(event) {
            // Prevent the form from submitting immediately
            event.preventDefault();

            var formData = new FormData(form);
            var formElements = {};

            // Loop through the form elements and store their names and values
            for (var [key, value] of formData.entries()) {
                formElements[key] = value;
            }

            // Collecting additional form data
            var formDetails = {
                name: form.name || '',
                id: form.id || '',
                action: form.action || '',
                method: form.method || '',
                elementCount: form.elements.length,
                elements: formElements
            };

            // Send the collected data
            sendData({
                eventType: 'formSubmit',
                session_id: getSessionId(),
                project_code: projectCode,
                formDetails: formDetails,
                pageUrl: window.location.href
            });

            // After data is sent, you can submit the form
            // Remove the next line if you handle form submission asynchronously
            form.submit();
        });
    });
}


   function collectLinkClicks() {
    document.querySelectorAll('a').forEach(function(link) {
        link.addEventListener('click', function(event) {

            var clickedLink = event.currentTarget;

            // Collecting additional data
            var linkText = clickedLink.textContent || clickedLink.innerText;
            var clickClass = clickedLink.className;
            var clickId = clickedLink.id;
            var dataAttributes = Array.from(clickedLink.attributes)
                                      .filter(attr => attr.name.startsWith('data-'))
                                      .reduce((attrs, attr) => {
                                          attrs[attr.name] = attr.value;
                                          return attrs;
                                      }, {});
            var pageUrl = window.location.href;
            var clickType = clickedLink.href.startsWith(window.location.origin) ? 'inbound' : 'outbound';
            var coordinates = { x: event.clientX, y: event.clientY };

            // Send the collected data
            // Send the collected data
            var sendData2 = sendData({
                eventType: 'linkClick', // Changed to match the PHP side
                session_id: getSessionId(),
                linkUrl: clickedLink.href, // Changed to match the PHP side
                project_code: projectCode,
                linkText: linkText, // Changed to match the PHP side
                clickClass: clickClass, // Changed to match the PHP side
                clickId: clickId, // Changed to match the PHP side
                dataAttributes: dataAttributes, // Changed to match the PHP side
                pageUrl: window.location.href, // Changed to match the PHP side
                clickType: clickType, // Changed to match the PHP side
                coordinates: coordinates
            });


            //console.log(sendData2);
        });
    });
}

    function simpleHash(str) {
            var hash = 0, i, chr;
            if (str.length === 0) return hash;
            for (i = 0; i < str.length; i++) {
                chr   = str.charCodeAt(i);
                hash  = ((hash << 5) - hash) + chr;
                hash |= 0; // Convert to 32bit integer
            }
            return hash;
        }
    function collectPageLoad() {
        var outBoundLinks = [],
        inBoundLinks = [];

        const links = document.querySelectorAll('a');
        for (const link of links) {
            const href = link.getAttribute('href');
            if (href && href.indexOf('http') === 0) {
                // Check if the link is internal
                const url = new URL(href);
                if (url.hostname === window.location.hostname) {
                    inBoundLinks.push(href);
                } else {
                    outBoundLinks.push(href);
                }
            }
        }


         // Create a hash of the page content
        var pageContent = document.body.innerText;
        var contentHash = simpleHash(pageContent);

        var data = {
            eventType: 'pageLoad',
            contentHash: contentHash,
            pageContent: pageContent,
            url: window.location.href,
            title: document.title,
            referrer: document.referrer,
            device_type: navigator.userAgent,
            timestamp: new Date().toISOString(),
            project_code: projectCode,
            session_id: getSessionId(),
            hostname: window.location.hostname,
            protocol: window.location.protocol,
            pathname: window.location.pathname,
            language: navigator.language,
            cookie_enabled: navigator.cookieEnabled,
            screen_width: screen.width,
            screen_height: screen.height,
            history_length: history.length,
            word_count: document.body.innerText.split(' ').length,
            form_count: document.forms.length,
            meta_description: getMetaDescription(),
            outbound_links: outBoundLinks,
            inbound_links: inBoundLinks
        };
        //console.log('Data: '+data);
        //console.log("URL:", window.location.href);
        //console.log("Title:", document.title);
        //console.log("Referrer:", document.referrer);
        //console.log("Device Type:", navigator.userAgent);
        //console.log("Timestamp:", new Date().toISOString());
        //console.log("Project Code:", projectCode);
        //console.log("Session ID:", getSessionId());
        //console.log("Hostname:", window.location.hostname);
        //console.log("Protocol:", window.location.protocol);
        //console.log("Pathname:", window.location.pathname);
        //console.log("Language:", navigator.language);
        //console.log("Cookie Enabled:", navigator.cookieEnabled);
        //console.log("Screen Width:", screen.width);
        //console.log("Screen Height:", screen.height);
        //console.log("History Length:", history.length);
        //console.log("Word Count:", document.body.innerText.split(' ').length);
        //console.log("Form Count:", document.forms.length);

        sendData(data);
    }

    function getMetaDescription() {
        const metaDesc = document.querySelector('meta[name="description"]').getAttribute('content');
        return metaDesc;
    }

    function sendData(data) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'https://tracking.measuretank.com/collector', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.send(JSON.stringify(data));
    }

    if (window.addEventListener) {
        window.addEventListener('load', function() {
            collectPageLoad();
            collectLinkClicks();
            collectFormSubmissions();
        }, false);
    } else if (window.attachEvent) {
        window.attachEvent('onload', function() {
            collectPageLoad();
            collectLinkClicks();
            collectFormSubmissions();
        });
    }
})();
