const Gulp = require('gulp');
const FS = require('node-fs-extra');
const Sass = require('gulp-sass');
const GulpWatch = require('gulp-watch');

Gulp.task('mkdir', () => {
  FS.mkdirsSync('./dist');
});

Gulp.task('html', ['mkdir'], () => {
  // 特に何もせずに移動するだけ
  Gulp.src(['./src/**/*.html', './src/**/*.php', '!./src/assets/**/*'])
    .pipe(Gulp.dest('./dist/'));
});

Gulp.task('assets', ['mkdir'], () => {
  // 特に何もせずに移動するだけ
  Gulp.src(['./src/assets/**/*', '!./src/assets/**/*.scss', '!./src/assets/**/*.coffee', '!./src/assets/**/*.ts'])
    .pipe(Gulp.dest('./dist/assets/'));
});

Gulp.task('script', ['mkdir'], () => {
  // 特に何もせずに移動するだけ
  // Gulp.src(['./src/assets/script/**/*.ts'])
  //   .pipe(Gulp.dest('./dist/'));
});

Gulp.task('scss', ['mkdir'], () => {
  Gulp.src('./src/assets/scss/**/*.scss')
    .pipe(Sass.sync().on('error', Sass.logError))
    .pipe(Gulp.dest('./dist/assets/style'));
});

Gulp.task('sass-watch', ['scss'], () => {
  GulpWatch(['./src/assets/scss/**/*.scss'], (event) => {
    console.log('File ' + event.path + ' was ' + event.type + ', running tasks...');
    Gulp.start('scss');
  });
});

Gulp.task('html-watch', ['html'], () => {
  GulpWatch(['./src/**/*.html', './src/**/*.php', '!./src/assets/**/*'], (event) => {
    console.log('File ' + event.path + ' was ' + event.type + ', running tasks...');
    Gulp.start('html');
  });
});

Gulp.task('assets-watch', ['assets'], () => {
  GulpWatch(['./src/assets/**/*', '!./src/assets/**/*.scss', '!./src/assets/**/*.coffee', '!./src/assets/**/*.ts'], (event) => {
    console.log('File ' + event.path + ' was ' + event.type + ', running tasks...');
    Gulp.start('assets');
  });
});

Gulp.task('watch', () => {
    Gulp.start('html-watch');
    Gulp.start('assets-watch');
    // Gulp.start('script-watch');
    Gulp.start('sass-watch');  
})

Gulp.task('default', ['watch']);
