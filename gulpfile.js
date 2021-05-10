var gulp = require("gulp");
var concat = require("gulp-concat");

function browser(cb) {
  return gulp.src([
      "node_modules/@sentry/browser/build/bundle.min.js",
      "src/js/init.js",
    ])
    .pipe(concat("wp-sentry-browser.min.js"))
    .pipe(gulp.dest("public/"));

    cb()
};

function tracing (cb) {
  gulp.src([
      "node_modules/@sentry/tracing/build/bundle.tracing.min.js",
      "src/js/init.js",
    ])
    .pipe(concat("wp-sentry-browser-tracing.min.js"))
    .pipe(gulp.dest("public/"));
    cb();
};

exports.default = gulp.parallel(browser, tracing);