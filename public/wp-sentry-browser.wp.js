;(function () {
    if (typeof wp_sentry === 'object') {
        var listsWithRegexes = ['allowUrls', 'denyUrls', 'ignoreErrors', 'ignoreTransactions'];
        var parseListWithRegexes = function (list) {
            for (var url in list) {
                if (list.hasOwnProperty(url)) {
                    if (list[url].startsWith('regex:')) {
                        list[url] = new RegExp(list[url].slice(6), 'i');
                    }
                }
            }
        };

        for (var i = 0; i < listsWithRegexes.length; i++) {
            if (typeof wp_sentry[listsWithRegexes[i]] === 'object') {
                parseListWithRegexes(wp_sentry[listsWithRegexes[i]]);
            }
        }

        if (typeof wp_sentry_hook === 'function') {
            var hookResult = wp_sentry_hook(wp_sentry);

            // If the hook returns false we do not continue to initialize Sentry
            if (hookResult === false) {
                return;
            }
        }

        wp_sentry.integrations = [];

        // Enable replay if a sample rate is set
        if (wp_sentry.replaysSessionSampleRate && wp_sentry.replaysSessionSampleRate > 0) {
            wp_sentry.replaysSessionSampleRate = parseFloat(wp_sentry.replaysSessionSampleRate);
        }
        if (wp_sentry.replaysOnErrorSampleRate && wp_sentry.replaysOnErrorSampleRate > 0) {
            wp_sentry.replaysOnErrorSampleRate = parseFloat(wp_sentry.replaysOnErrorSampleRate);
        }
        if (wp_sentry.replaysSessionSampleRate > 0 || wp_sentry.replaysOnErrorSampleRate > 0) {
            wp_sentry.integrations.push(new Sentry.Integrations.Replay(wp_sentry.wpSessionReplayOptions));
        }

        // Enable tracing if a sample rate is set
        if (wp_sentry.tracesSampleRate) {
            wp_sentry.tracesSampleRate = parseFloat(wp_sentry.tracesSampleRate);
        }
        if (wp_sentry.tracesSampleRate && wp_sentry.tracesSampleRate > 0) {
            wp_sentry.integrations.push(new Sentry.Integrations.BrowserTracing(wp_sentry.wpBrowserTracingOptions));
        }

        Sentry.init(wp_sentry);

        if (typeof wp_sentry.context === 'object') {
            Sentry.configureScope(function (scope) {
                if (typeof wp_sentry.context.user === 'object') {
                    scope.setUser(wp_sentry.context.user);
                }

                if (typeof wp_sentry.context.tags === 'object') {
                    for (var tag in wp_sentry.context.tags) {
                        if (wp_sentry.context.tags.hasOwnProperty(tag)) {
                            scope.setTag(tag, wp_sentry.context.tags[tag]);
                        }
                    }
                }

                if (typeof wp_sentry.context.extra === 'object') {
                    for (var extra in wp_sentry.context.extra) {
                        if (wp_sentry.context.extra.hasOwnProperty(extra)) {
                            scope.setExtra(extra, wp_sentry.context.extra[extra]);
                        }
                    }
                }
            });
        }
    }
})();
