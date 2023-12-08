(function() {
    var projectCode = document.currentScript.getAttribute('data-project-code');
    //console.log('Start:');
    //console.log('Project: '+projectCode);
    function getSessionId() {
        var sessionId = localStorage.getItem('mt_session_id');
        if (!sessionId) {
            sessionId = Math.random().toString(36).substring(2, 15);
            localStorage.setItem('mt_session_id', sessionId);
        }
        //console.log('SessionID: '+sessionId);
        return sessionId;
    }

    function collectLinkClicks() {
        document.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {

                var sendData2 = sendData({ eventType: 'linkClick', linkUrl: link.href });
                //console.log(sendData2);
            });
        });
    }

    function collectFormSubmissions() {
        document.querySelectorAll('form').forEach(function(form, index) {
            form.addEventListener('submit', function() {
                sendData({
                    eventType: 'formSubmit',
                    formDetails: {
                        name: form.name,
                        id: form.id,
                        elementCount: form.elements.length
                    }
                });
            });
        });
    }

    function collectData() {
        var data = {
            url: window.location.href,
            title: document.title,
            referrer: document.referrer,
            device_type: navigator.userAgent,
            timestamp: new Date().toISOString(),
            project_code: projectCode,
            sessionId: getSessionId(),
            hostname: window.location.hostname,
            protocol: window.location.protocol,
            pathname: window.location.pathname,
            language: navigator.language,
            cookie_enabled: navigator.cookieEnabled,
            screen_width: screen.width,
            screen_height: screen.height,
            history_length: history.length,
            word_count: document.body.innerText.split(' ').length,
            form_count: document.forms.length
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

    function sendData(data) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'https://tracking.measuretank.com/collector', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.send(JSON.stringify(data));
    }

    if (window.addEventListener) {
        window.addEventListener('load', function() {
            collectData();
            collectLinkClicks();
            collectFormSubmissions();
        }, false);
    } else if (window.attachEvent) {
        window.attachEvent('onload', function() {
            collectData();
            collectLinkClicks();
            collectFormSubmissions();
        });
    }
})();
