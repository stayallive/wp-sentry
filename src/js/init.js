;(function () {
    // Polyfill for IE and older browsers :)
    // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/startsWith#Browser_compatibility
    if (!String.prototype.startsWith) {
        String.prototype.startsWith = function (searchString, position) {
            position = position || 0;
            return this.indexOf(searchString, position) === position;
        };
    }

    if (typeof wp_sentry === 'object') {
        var regexListUrls = function (listUrls) {
            for (var url in listUrls) {
                if (listUrls.hasOwnProperty(url)) {
                    if (listUrls[url].startsWith('regex:')) {
                        listUrls[url] = new RegExp(listUrls[url].slice(6), 'i');
                    }
                }
            }
        };

        if (typeof wp_sentry.whitelistUrls === 'object') {
            regexListUrls(wp_sentry.whitelistUrls);
        }
        if (typeof wp_sentry.blacklistUrls === 'object') {
            regexListUrls(wp_sentry.blacklistUrls);
        }

        if (wp_sentry.tracesSampleRate) {
            wp_sentry.tracesSampleRate = parseFloat(wp_sentry.tracesSampleRate);
        }

        if (typeof wp_sentry_hook === 'function') {
            var hookResult = wp_sentry_hook(wp_sentry);

            // If the hook returns false we do not continue to initialize Sentry
            if (hookResult === false) {
                return;
            }
        }

        if (wp_sentry.integrations === undefined) {
            wp_sentry.integrations = [
                new Sentry.Integrations.BrowserTracing()
            ];
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
