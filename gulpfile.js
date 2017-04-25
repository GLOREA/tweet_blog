const Gulp = require('gulp');
const FS = require('node-fs-extra');
const Sass = require('gulp-sass');
const GulpWatch = require('gulp-watch');

Gulp.task('mkdir', () => {
  FS.mkdirsSync('./dist/assets/style');
});

Gulp.task('scss', ['mkdir'], () => {
  console.log('scss!!');
  Gulp.src('./src/assets/scss/**/*.scss').pipe(Sass.sync().on('error', Sass.logError)).pipe(Gulp.dest('./dist/assets/style'));

});

Gulp.task('sass-watch', ['scss'], () => {
  GulpWatch(['./src/assets/scss/**/*.scss'], (event) => {
    console.log('File ' + event.path + ' was ' + event.type + ', running tasks...');
    Gulp.start('scss');
  });
});

Gulp.task('default', ['sass-watch']);
