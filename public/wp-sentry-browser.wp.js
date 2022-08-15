;(function () {
    if (typeof wp_sentry === 'object') {
        var regexUrlList = function (urlList) {
            for (var url in urlList) {
                if (urlList.hasOwnProperty(url)) {
                    if (urlList[url].startsWith('regex:')) {
                        urlList[url] = new RegExp(urlList[url].slice(6), 'i');
                    }
                }
            }
        };

        if (typeof wp_sentry.allowUrls === 'object') {
            regexUrlList(wp_sentry.allowUrls);
        }

        if (typeof wp_sentry.denyUrls === 'object') {
            regexUrlList(wp_sentry.denyUrls);
        }

        if (typeof wp_sentry_hook === 'function') {
            var hookResult = wp_sentry_hook(wp_sentry);

            // If the hook returns false we do not continue to initialize Sentry
            if (hookResult === false) {
                return;
            }
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
