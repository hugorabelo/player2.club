'use strict';

var gulp = require('gulp');
var uglify = require('gulp-uglify');
var concat = require('gulp-concat');
var jshint = require('gulp-jshint');
var stylish = require('jshint-stylish');

gulp.task('default', ['controllers', 'directives', 'factorys', 'plugins', 'watch']);

gulp.task('controllers', function () {
	return gulp.src('public/app/controllers/**/*.js')
	.pipe(concat('controllers.js'))
	.pipe(uglify())
	.pipe(gulp.dest('public/app/assets/'));
});

gulp.task('directives', function () {
	return gulp.src('public/app/directives/**/*.js')
	.pipe(concat('directives.js'))
	.pipe(uglify())
	.pipe(gulp.dest('public/app/assets/'));
});

gulp.task('factorys', function () {
	return gulp.src('public/app/factorys/**/*.js')
	.pipe(concat('factorys.js'))
	.pipe(uglify())
	.pipe(gulp.dest('public/app/assets/'));
});

gulp.task('plugins', function () {
	return gulp.src('public/app/plugins/**/*.js')
	.pipe(concat('plugins.js'))
	.pipe(uglify())
	.pipe(gulp.dest('public/app/assets/'));
});

gulp.task('lint', function() {
  return gulp.src('public/app/controllers/**/*.js')
    .pipe(jshint())
    .pipe(jshint.reporter(stylish));
});

gulp.task('watch', function () {
	gulp.watch('public/app/controllers/**/*.js', ['controllers']);
});
