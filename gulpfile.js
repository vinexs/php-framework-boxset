/* This file is used to compile .less and .scss in /assets
 * folder. To start watching those file, use command "gulp"
 * in project root folder.
 */

// Config
var config = {
    // Source location                  Compile to location
    './assets/main/less/common.less': './assets/main/css/common.min.css',
    './assets/cms/less/common.less': './assets/cms/css/common.min.css'
};

// Gulp
var gulp = require('gulp'),
    watch = require('gulp-watch'),
    sass = require('gulp-sass'),
    less = require('gulp-less'),
    sourcemaps = require('gulp-sourcemaps'),
    uglify = require('gulp-uglify'),
    uglifycss = require('gulp-uglifycss'),
    colors = require('colors');

var getTimeTag = function() {
    var date = new Date();
    var zeroPadLeft = function(str) {
        return String(str).length == 1 ? '0'+ str : str;
    };
    var time = zeroPadLeft(date.getHours()) +':'+
                zeroPadLeft(date.getMinutes()) +':'+
                zeroPadLeft(date.getSeconds());
    return colors.white('[') + colors.grey(time) + colors.white(']');
};

var watchCompile = function(source, output) {
    gulp.watch(source, (data)=>{
        if (data.type == 'changed') {
            var ext = source.split('.').pop();
            console.log(getTimeTag() + ' '+ colors.magenta(source) +' changed, compile with '+ colors.cyan(ext) +' scheme.');
            switch (ext) {
                case 'less':
                    gulp.src(source)
                        .pipe(sourcemaps.init())
                        .pipe(less())
                        .pipe(uglifycss({uglyComments: true}))
                        .pipe(sourcemaps.write('.'))
                        .pipe(gulp.dest(output));
                    break;
                case 'scss':
                case 'sass':
                    gulp.src(source)
                        .pipe(sourcemaps.init())
                        .pipe(sass({outputStyle: 'compressed'}))
                        .pipe(uglifycss({uglyComments: true}))
                        .pipe(sourcemaps.write('.'))
                        .pipe(gulp.dest(output));
                    break;
                case 'js':
                    gulp.src(source)
                        .pipe(uglify())
                        .pipe(gulp.dest(output));
                    break;
            }
        }
    });
};

gulp.task('watch', function() {
    // watch config file
    for (var source in config) {
        console.log(getTimeTag() + ' Watching '+ colors.magenta(source));
        watchCompile(source, config[source]);
    }
});




gulp.task('default', ['watch']);
