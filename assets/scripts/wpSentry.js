import * as Sentry from '@sentry/browser';

/**
 * The function responsible for initializing Sentry and
 * providing context wherever possible.
 *
 * @since 3.0.0
 */
function initializeWPSentry(){

  // Turn our localized wp_sentry object into scoped constants
  const initOptions = wp_sentry.init_options;
  const userContext = wp_sentry.context.user;
  const tagsContext = wp_sentry.context.tags;
  const extraContext = wp_sentry.context.extra;

  /**
   * Initialize the Sentry SDK
   *
   * @link https://docs.sentry.io/error-reporting/configuration/?platform=browser
   * @param initOptions - the initial runtime configs
   */
  Sentry.init(
    initOptions
  );

  // Provide additional context to the Sentry SDK
  provideUserContext( userContext );
  provideTagsContext( tagsContext );
  provideExtraContext( extraContext );

}

/**
 * Provide Sentry context about the current user
 *
 * @link https://docs.sentry.io/enriching-error-data/context/?platform=browser#capturing-the-user
 * @param {object} userContext
 * @since 3.0.0
 */
function provideUserContext( userContext ){

  if( typeof userContext === 'object' ){

    Sentry.configureScope((scope) => {
      scope.setUser( userContext )
    });

  }

}

/**
 * Provide Sentry additional tag context
 *
 * @link https://docs.sentry.io/enriching-error-data/context/?platform=browser#tagging-events
 * @param {object} tagsContext
 * @since 3.0.0
 */
function provideTagsContext( tagsContext ){

  if( typeof tagsContext === 'object' ){

    Sentry.configureScope((scope) => {

      Object.entries(tagsContext).forEach( ( tag ) => {
        scope.setTag( tag[0], tag[1] )
      });

    })

  }

}

/**
 * Provide Sentry any "extra" data we want to have visibility on
 *
 * @link https://docs.sentry.io/enriching-error-data/context/?platform=browser#extra-context
 * @param {object} extraContext
 * @since 3.0.0
 */
function provideExtraContext( extraContext ){

  if( typeof extraContext === 'object' ){

    Sentry.configureScope((scope) => {

      Object.entries(extraContext).forEach( ( extra ) => {
          scope.setExtra( extra[0], extra[1] )
      });

    });

  }

}

// Let's fire it up!
initializeWPSentry();
