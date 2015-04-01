/*jshint node:true */

module.exports = function( grunt ) {
	'use strict';

	require( 'matchdep' ).filterDev( 'grunt-*' ).forEach( grunt.loadNpmTasks );

	grunt.initConfig({

		jshint: {
			options: {
				jshintrc: '.jshintrc'
			},
			plugin: [
				'Gruntfile.js',
				'assets/js/*.js'
			]
		},

		makepot: {
			plugin: {
				options: {
					mainFile: 'satispress.php',
					potHeaders: {
						poedit: true
					},
					type: 'wp-plugin',
					updateTimestamp: false
				}
			}
		},

		watch: {
			js: {
				files: [ '<%= jshint.plugin %>' ],
				tasks: [ 'jshint' ]
			}
		}

	});

	grunt.registerTask( 'default', [ 'jshint', 'watch' ] );

};
