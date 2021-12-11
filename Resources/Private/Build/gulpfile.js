/* jshint node: true */
'use strict';

const { src, dest, watch, series, parallel } = require('gulp');
const sass = require('gulp-sass')(require('node-sass'));
const rollup = require('rollup').rollup;
const rollupConfig = require('./rollup.config');
const uglify = require('gulp-uglify');
const plumber = require('gulp-plumber');
const rename = require('gulp-rename');

const project = {
	base: __dirname + '/../../Public',
	css: __dirname + '/../../Public/Css',
	js: __dirname + '/../../Public/JavaScript/Lux',
	images: __dirname + '/../../Public/Images'
};

// SCSS zu css
function css() {
	const config = {};
	config.outputStyle = 'compressed';

	return src(__dirname + '/../Sass/*.scss')
		.pipe(plumber())
		.pipe(sass(config))
		.pipe(rename({
			suffix: '.min'
		}))
		.pipe(dest(project.css));
};

function jsFrontend(done) {
  rollup(rollupConfig).then(bundle => {
    rollupConfig.output.plugins = rollupConfig
    bundle.write(rollupConfig.output).then(() => done());
  });
};

function jsBackend() {
	return src([__dirname + '/JavaScript/Backend/*.js'])
		.pipe(plumber())
		.pipe(uglify())
		.pipe(rename({
			suffix: '.min'
		}))
		.pipe(dest(project.js));
};

// "npm run build"
const build = series(jsFrontend, jsBackend, css);

// "npm run watch"
const def = () => parallel([
  watch(__dirname + '/../Sass/*.scss', [css]),
  watch(__dirname + '/JavaScript/*.js', [jsFrontend, jsBackend])
]);

module.exports = {
  default: def,
  build,
  css,
  jsBackend,
  jsFrontend
};
