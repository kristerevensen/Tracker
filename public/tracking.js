/*
*  MEASURETANK TRACKER
*  2023 Copyright
*  Measuretank.com
*  V2.03 ALPHA
*/
(function() {
    var projectCode = document.currentScript.getAttribute('data-project-code');

    function getSessionId() {
        var sessionId = localStorage.getItem('mt_session_id');
        var timestamp = localStorage.getItem('mt_session_timestamp');
        var currentTime = new Date().getTime();

        if (!sessionId || !timestamp || currentTime - parseInt(timestamp) > 1800000) {
            sessionId = Math.random().toString(36).substring(2, 15);
            localStorage.setItem('mt_session_id', sessionId);
            localStorage.setItem('mt_session_timestamp', currentTime.toString());
        } else {
            localStorage.setItem('mt_session_timestamp', currentTime.toString());
        }

        return sessionId;
    }

    function sendData(data) {
        var baseUrl = 'https://tracking.measuretank.com/collector';
        var params = [];

        for (var key in data) {
            if (data.hasOwnProperty(key)) {
                if (typeof data[key] === 'object') {
                    for (var subKey in data[key]) {
                        if (data[key].hasOwnProperty(subKey)) {
                            params.push(`${encodeURIComponent(key + '[' + subKey + ']')}=${encodeURIComponent(data[key][subKey])}`);
                        }
                    }
                } else {
                    params.push(`${encodeURIComponent(key)}=${encodeURIComponent(data[key])}`);
                }
            }
        }

        var url = `${baseUrl}?${params.join('&')}`;
        var trackingPixel = new Image(1, 1);
        trackingPixel.src = url;
        document.body.appendChild(trackingPixel);
    }

    function collectPageLoad() {
        var outBoundLinks = [],
            inBoundLinks = [];

        const links = document.querySelectorAll('a');
        for (const link of links) {
            const href = link.getAttribute('href');
            if (href && href.indexOf('http') === 0) {
                const url = new URL(href);
                if (url.hostname === window.location.hostname) {
                    inBoundLinks.push(href);
                } else {
                    outBoundLinks.push(href);
                }
            }
        }

        var pageContent = document.body.innerText;
        var contentHash = simpleHash(pageContent);

        var data = {
            eventType: 'pageLoad',
            contentHash: contentHash,
            url: window.location.href,
            title: document.title,
            referrer: document.referrer,
            device_type: navigator.userAgent,
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
            outbound_links: outBoundLinks.join(','), // Comma-separated string
            inbound_links: inBoundLinks.join(',')
        };

        sendData(data);
    }

    function collectLinkClicks() {
        document.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function(event) {
                var clickedLink = event.currentTarget;
                var linkText = clickedLink.textContent || clickedLink.innerText;
                var clickClass = clickedLink.className;
                var clickId = clickedLink.id;
                var dataAttributes = Array.from(clickedLink.attributes)
                    .filter(attr => attr.name.startsWith('data-'))
                    .reduce((attrs, attr) => {
                        attrs[attr.name] = attr.value;
                        return attrs;
                    }, {});
                var clickType = clickedLink.href.startsWith(window.location.origin) ? 'inbound' : 'outbound';
                var coordinates = { x: event.clientX, y: event.clientY };

                var data = {
                    eventType: 'linkClick',
                    session_id: getSessionId(),
                    linkUrl: clickedLink.href,
                    project_code: projectCode,
                    linkText: linkText,
                    clickClass: clickClass,
                    clickId: clickId,
                    dataAttributes: dataAttributes,
                    pageUrl: window.location.href,
                    clickType: clickType,
                    coordinates: coordinates
                };

                sendData(data);
            });
        });
    }

    function collectFormSubmissions() {
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                var formData = new FormData(form);
                var formElements = {};

                for (var [key, value] of formData.entries()) {
                    formElements[key] = value;
                }

                var formDetails = {
                    name: form.name || '',
                    id: form.id || '',
                    action: form.action || '',
                    method: form.method || '',
                    elementCount: form.elements.length,
                    elements: formElements
                };

                var data = {
                    eventType: 'formSubmit',
                    session_id: getSessionId(),
                    project_code: projectCode,
                    formDetails: formDetails,
                    pageUrl: window.location.href
                };

                sendData(data);
                form.submit();
            });
        });
    }

    function simpleHash(str) {
        var hash = 0, i, chr;
        if (str.length === 0) return hash;
        for (i = 0; i < str.length; i++) {
            chr = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + chr;
            hash |= 0;
        }
        return hash;
    }

    function getMetaDescription() {
        const metaDesc = document.querySelector('meta[name="description"]').getAttribute('content');
        return metaDesc;
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
