
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
					},
					{
						expand: true,
						src: ['php/*'],
						dest: 'dist/',
						filter: 'isFile',
						flatten: true
					},
					{
						expand: true,
						cwd: 'bower_components/jquery/dist/',
						src: ['jquery.min.js'],
						dest: 'dist/js',
						filter: 'isFile'
					},
					{
						expand: true,
						cwd: 'bower_components/respond/dest/',
						src: ['respond.min.js'],
						dest: 'dist/js',
						filter: 'isFile'
					},
					{
						expand: true,
						cwd: 'bower_components/html5shiv/dist/',
						src: ['html5shiv.min.js'],
						dest: 'dist/js',
						filter: 'isFile'
					}
		    ],
		  },
		},

		clean: {
			build: {
		    src: ['dist/']
		  }
		},

		php: {
        dist: {
            options: {
								port: 8000,
								open: true,
								base: 'dist'
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
			},
			php: {
				files: [ 'php/*.php' ],
				tasks: [ 'copy' ]
			}
		}

	});

	// Load Grunt plugins
	require('load-grunt-tasks')(grunt);

	// Register Grunt tasks
	grunt.registerTask('default', [ 'clean', 'copy', 'sass', 'uglify', 'php' ]);
	grunt.registerTask('develop', [ 'default', 'watch' ]);
	grunt.registerTask('serve', [ 'default' ]);

};
