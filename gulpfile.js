var gulp      = require('gulp')
,   minifyCSS = require('gulp-minify-css')
,   debug     = require('gulp-debug')
,   less      = require('gulp-less');

gulp.task('less', function () {
    gulp.src('src/worais-database-management/assets/*.less')
    .pipe(debug({title:'less'}))
    .pipe(less())
    .pipe(minifyCSS())
    .pipe(gulp.dest('src/worais-database-management/assets/'));
});

gulp.task('watch', function() {
	gulp.watch(['src/worais-database-management/assets/*.less'],  { interval: 1000, delay: 1000 },['less']);
})

gulp.task('build',['less']);
gulp.task('develop',  ['build','watch']);