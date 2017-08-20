/* This file is used to compile .less and .scss in /assets
 * folder. To start watching those file, use command "gulp"
 * in project root folder.
 */

// Config
var config = {
    '/assets/main/less/common.less': '/assets/main/css/common.min.css',
    '/assets/cms/less/common.less': '/assets/cms/css/common.min.css'
};

// Gulp
var gulp = require('gulp'),
    watch = require('gulp-watch'),
    sass = require('gulp-sass'),
    less = require('gulp-less'),
    sourcemaps = require('gulp-sourcemaps')
    uglify = require('gulp-uglify');


gulp.task('default', function() {
    // TODO
});

// TODO
