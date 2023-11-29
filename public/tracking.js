(function() {
    var projectCode = document.currentScript.getAttribute('data-project-code');

    function getSessionId() {
        var sessionId = localStorage.getItem('mt_session_id');
        if (!sessionId) {
            sessionId = Math.random().toString(36).substring(2, 15);
            localStorage.setItem('mt_session_id', sessionId);
        }
        return sessionId;
    }

    function collectLinkClicks() {
        document.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                sendData({ eventType: 'linkClick', linkUrl: link.href });
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
            deviceType: navigator.userAgent,
            timestamp: new Date().toISOString(),
            projectCode: projectCode,
            sessionId: getSessionId(),
            hostname: window.location.hostname,
            protocol: window.location.protocol,
            pathname: window.location.pathname,
            language: navigator.language,
            cookieEnabled: navigator.cookieEnabled,
            screenWidth: screen.width,
            screenHeight: screen.height,
            historyLength: history.length,
            wordCount: document.body.innerText.split(' ').length,
            formCount: document.forms.length
        };

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
