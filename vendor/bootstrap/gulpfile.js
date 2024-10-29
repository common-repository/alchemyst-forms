var gulp 		    = require('gulp'),
    autoprefixer    = require('gulp-autoprefixer'),
	sass 		    = require('gulp-sass'),
	watch 		    = require('gulp-watch');

gulp.task('scss', function () {
    return gulp.src('./bootstrap.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(sass({outputStyle: 'compressed'}))
        .pipe(autoprefixer())
        .pipe(gulp.dest('css'));
});

gulp.task('watch', function () {
    gulp.watch('./**/*.scss', ['scss']);
});

gulp.task('default', ['watch']);
