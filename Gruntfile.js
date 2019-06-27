module.exports = function ( grunt ) {
	grunt.initConfig(
		{
			qunit: {
				files: [
				'tests/qunit/**/*.html'
				]
			},
			phpunit: {
				'default': {
					cmd: 'phpunit',
					args: [ '--verbose' ]
				},
				ajax: {
					cmd: 'phpunit',
					args: [ '--verbose', '--group', 'ajax' ]
				},
				multisite: {
					cmd: 'phpunit',
					args: [ '--verbose', '-c', 'tests/phpunit/multisite.xml' ]
				},
				'ms-files': {
					cmd: 'phpunit',
					args: [ '--verbose', '-c', 'tests/phpunit/multisite.xml', '--group', 'ms-files' ]
				},
				'external-http': {
					cmd: 'phpunit',
					args: [ '--verbose', '--group', 'external-http' ]
				},
			}
		}
	);

	grunt.registerMultiTask(
		'phpunit', 'Runs PHPUnit tests, including the ajax, external-http, and multisite tests.', function () {
			grunt.util.spawn(
				{
					cmd: this.data.cmd,
					args: this.data.args,
					opts: {
						stdio: 'inherit'
					}
				}, this.async()
			);
		}
	);

	grunt.loadNpmTasks( 'grunt-contrib-qunit' );

	grunt.registerTask( 'test', [ 'phpunit:default', 'qunit' ] );
};
