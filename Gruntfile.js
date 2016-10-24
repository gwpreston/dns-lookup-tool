
// Load Grunt
module.exports = function (grunt) {

	"use strict";

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		uglify: {
			release: {
				options: {
					sourceMap: true,
					compress: {
		        drop_console: true
		      }
				},
				files: [{
					expand: true,
					cwd: 'js/',
					src: ['*.js'],
					dest: 'dist/js/',
					ext: '.min.js',
					extDot: 'first'
				}],
			},
			build: {
				options: {
					sourceMap: true,
					beautify: true,
					mangle: false
				},
				files: [{
					expand: true,
					cwd: 'js/',
					src: ['*.js'],
					dest: 'dist/js/',
					ext: '.js',
					extDot: 'first'
				}],
			}
		},

		sass: {
			release : {
          options: {
              outputStyle: 'compressed',
              sourceMap: true
          },
          files: [{
              expand: true,
							cwd: 'sass',
							src: ['**/*.scss'],
							dest: 'dist/css/',
							ext: '.min.css'
		      }]
      },
			build : {
          options: {
							sourceMap: true
          },
          files: [{
              expand: true,
							cwd: 'sass',
							src: ['**/*.scss'],
							dest: 'dist/css/',
							ext: '.css'
          }]
      }
    },

		copy: {
		  dist: {
		    files: [{
						expand: true,
						cwd: 'bower_components/bootstrap-sass/assets/fonts/bootstrap/',
						src: ['**'],
						dest: 'dist/fonts/bootstrap',
						filter: 'isFile'
					}
		    ],
		  },
		},

		php: {
        dist: {
            options: {
								port: 8000
            }
        }
    },

		watch: {
			gruntfile: {
				options: {
					spawn: false,
					reload: true
				},
				files: [ 'Gruntfile.js' ]
			},
			sass: {
				files: [ 'sass/**/*.scss' ],
				tasks: [ 'sass' ]
			},
			js: {
				files: [ 'js/**/*.js' ],
				tasks: [ 'uglify' ]
			}
		}

	});

	// Load Grunt plugins
	require('load-grunt-tasks')(grunt);

	// Register Grunt tasks
	grunt.registerTask('default', [ 'copy', 'sass', 'uglify' ]);
	grunt.registerTask('develop', [ 'default', 'php', 'watch' ]);

};
