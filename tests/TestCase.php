<?php

// use Mockery as m;
class TestCase extends Illuminate\Foundation\Testing\TestCase {
	protected $useDatabase = true;
	/**
	 * Creates the application.
	 *
	 * @return Symfony\Component\HttpKernel\HttpKernelInterface
	 */
	public function createApplication(){
		$unitTesting = true;

		$testEnvironment = 'testing';
		require_once __DIR__ . '/../app/vendor/autoload.php'; // Autoload files using Composer autoload
		return require __DIR__.'/../app/bootstrap/start.php';
	}

	function setUp(){
		parent::setUp();
		if($this->useDatabase)
			$this->setUpDb();
	}

	function teardown(){
		$this->teardownDb();
	}

	function setUpDb(){
		Artisan::call('migrate');
		Artisan::call('db:seed');
	}

	function teardownDb(){
		Artisan::call('migrate:reset');
	}
}