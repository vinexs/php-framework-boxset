/* This file is used to compile .less and .scss in /assets
 * folder. To start watching those file, use command "gulp"
 * in project root folder.
 */

// Config
var config = {
    // Source location                  Compile to location
    'assets/main/less/common.less': 'assets/main/css/common.min.css',
    'assets/cms/less/common.less': 'assets/cms/css/common.min.css'
};

// Gulp Tasks
var gulp = require('gulp'),
    gutil = require('gulp-util'),
    watch = require('glob-watcher'),
    sass = require('gulp-sass'),
    less = require('gulp-less'),
    sourcemaps = require('gulp-sourcemaps'),
    uglify = require('gulp-uglify'),
    uglifycss = require('gulp-uglifycss');

var fileCompile = function(source, output) {
    var ext = source.split('.').pop();
    gutil.log(gutil.colors.magenta(source) +' changed, compile with '+ gutil.colors.cyan(ext) +' scheme.');
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
};

gulp.task('watch', function() {
    var paths = [];
    for (var source in config) {
        gutil.log('Watching '+ gutil.colors.magenta(source));
        paths.push(source);
    }
    var watcher = watch(paths);
    watcher.on('change', function(path, stat) {
        var output = config[path];
        fileCompile(path, output);
    });
});

gulp.task('default', ['watch']);
