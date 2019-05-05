/**
 * Webpack V4
 *
 * @description A totally custom, totally awesome implementation of Webpack
 * for a modern Wordpress build process.
 *
 * WP JS Coding Standards Reference:
 * @see https://make.wordpress.org/core/handbook/best-practices/inline-documentation-standards/javascript/
 *
 */

/**
 * Pull in all of our Webpack Plugin Dependencies
 */
const FriendlyErrorsWebpackPlugin = require( 'friendly-errors-webpack-plugin' );
const CleanObsoleteChunks = require( 'webpack-clean-obsolete-chunks' );
const CleanWebpackPlugin = require( 'clean-webpack-plugin' );
const WebpackAssetsManifest = require( 'webpack-assets-manifest' );
const WebpackMd5Hash = require( 'webpack-md5-hash' );

/**
 * Define relative path for resolving outputs
 */
const path = require( 'path' );

/**
 * Define devMode based on the command run at
 * the start of the build process. This allows
 * us to easily handle dev and production builds differently.
 */
const devMode = process.env.NODE_ENV !== 'production';

/**
 * Quick settings for the current project
 */
const projectSettings = {

    jsEntryFiles: {
        wpSentry: '../scripts/wpSentry.js',
    },
};

/**
 * Webpack Configuration
 */
const config = {

    entry: projectSettings.jsEntryFiles,

    devtool: devMode ? 'none' : 'none',

    output: {

        path: path.resolve( __dirname, '../../dist' ),

        filename: devMode ? 'js/[name].js' : 'js/[name].[chunkhash].bundle.js',
        chunkFilename: devMode ? 'js/[name].js' : 'js/[name].[chunkhash].js',

    },

    module: {
      rules: [

        /**
         * Babel Loader.
         *
         * A compiler that allows us to:
         * 1. Use the latest JS standards without breaking stuff on non-compatible browsers.
         * 2. Runs our JS through our linting setup
         *
         * @kind    loader
         * @see     https://github.com/babel/babel-loader
         * @since   1.0.0
         */
        {
            test: /\.js$/,
            exclude: /(node_modules|bower_components)/,
            use:
            ['babel-loader'],
        },
      ]
    },

    plugins: [

    /**
		 * WebpackMd5Hash
		 *
		 * @description Plugin to replace a standard webpack chunkhash with md5. This
		 * hashing is used for our production files ONLY.
		 *
		 * @since  1.0.0
		 * @see https://github.com/erm0l0v/webpack-md5-hash
		 */
        new WebpackMd5Hash(),



    /**
		 * FriendlyErrorsWebpackPlugin
		 *
		 * @description recognizes certain classes of webpack errors and cleans, aggregates
		 * and prioritizes them to provide a better Developer Experience.
		 *
		 * @since  1.0.0
		 * @see https://github.com/geowarin/friendly-errors-webpack-plugin
		 */
        new FriendlyErrorsWebpackPlugin({}),

    /**
		 * CleanWebpackPlugin
		 *
		 * @description Delete files in specified folder when a new build is run. Note that the the specified folder is removed
		 * before our build process runs. If the build process throws a fatal error, the site will break. When this happens,
		 * the error must be fixed, and we must stop terminal and re-initialize the build process.
		 *
		 * @since  1.0.0
		 * @see https://github.com/johnagan/clean-webpack-plugin/
		 */
        new CleanWebpackPlugin({}),

    /**
		 * CleanObsoleteChunks
		 *
		 * @description Clean old hashed files when new ones are
		 * generated when using --watch. This was implemented because
		 * clean-webpack-plugin only works when the initial build is run
		 * and would leave all the old files behind when new ones are recompiled
		 * with new hashes.
		 *
		 * @since  1.0.0
		 * @see https://github.com/GProst/webpack-clean-obsolete-chunks
		 */
        new CleanObsoleteChunks({}),

    /**
		 * WebpackAssetsManifest
		 *
		 * @description This Webpack plugin will generate a JSON file that matches
		 * the original filename with the new hashed name. This will help us
		 * when it comes time to load our assets on our production site. We
		 * can simply reference the key of the file name and get the hashed file.
		 * This allows us to implement a better cache busting system (no more query string cache issues).
		 *
		 * @since  1.0.0
		 * @see https://github.com/webdeveric/webpack-assets-manifest
		 */
        new WebpackAssetsManifest({}),

    ],

  /**
	 * Webpack Terminal Stats
	 *
	 * @description I like the way Roots/Sage looks when running a build,
	 * so I grabbed their stats settings.
	 *
	 * @since  1.0.0
	 * @see https://github.com/roots/sage/blob/master/resources/assets/build/webpack.config.js#L25
	 */
    stats: {
        hash: false,
        version: false,
        timings: false,
        children: false,
        errors: false,
        errorDetails: false,
        warnings: false,
        chunks: false,
        modules: false,
        reasons: false,
        source: false,
        publicPath: false,
    },
};

module.exports = config;
