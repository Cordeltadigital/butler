var autoprefixer = require('autoprefixer')
var pxtorem = require('postcss-pxtorem')
var concat = require('gulp-concat')
var gulp = require('gulp')
var postcss = require('gulp-postcss')
var sass = require('gulp-sass')
var sourcemaps = require('gulp-sourcemaps')
var browserSync = require('browser-sync').create()
sass.compiler = require('node-sass')
var sassGlob = require('gulp-sass-glob')

gulp.task('sass', function () {
  return gulp
    .src(['./sass/app.scss'])
    .pipe(sassGlob())
    .pipe(sourcemaps.init())
    .pipe(sass.sync().on('error', sass.logError))
    .pipe(sourcemaps.write())
    .pipe(concat('style.css'))
    .pipe(
      postcss([
        autoprefixer({
          browsers: ['last 3 versions']
        }),
        pxtorem({
          rootValue: 16,
          unitPrecision: 5,
          propList: ['*'],
          selectorBlackList: [':root', 'html', 'body'],
          replace: false
        })
      ])
    )
    .pipe(gulp.dest('./dist/css'))
    .pipe(browserSync.stream())
})


gulp.task('js', function () {
  return gulp
    .src(['./js/**/*.js'])
    .pipe(sourcemaps.init())
    .pipe(sourcemaps.write('.'))
    .pipe(concat('app.js'))
    .pipe(gulp.dest('./dist/js/'))
    .pipe(browserSync.stream())
})

gulp.task('start', ['sass', 'js'], function () {
  browserSync.init({
    proxy: 'artsound.local'
  })

  gulp.watch('./**/*.php').on('change', browserSync.reload)
  gulp.watch('./sass/**/*.scss', ['sass']).on('change', browserSync.reload)
  gulp.watch('./js/**/*.js', ['js']).on('change', browserSync.reload)
})
