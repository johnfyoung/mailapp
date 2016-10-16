/**
 * Created by johny on 10/1/16.
 */

var gulp = require('gulp');

gulp.task('default', function(){
    gulp.src('node_modules/jquery/dist/jquery.min.js')
        .pipe(gulp.dest('public/vendor/jquery'));

    gulp.src('node_modules/bootstrap/dist/**/*')
        .pipe(gulp.dest('public/vendor/bootstrap'));
});