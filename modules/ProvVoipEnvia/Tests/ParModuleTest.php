<?php

class ParModuleTest extends TestCase {

	/**
	 * a test that never fails ⇒ verify that unit testing is up and running on module level
	 *
	 * @return void
	 */
	public function testUnittestsRunning()
	{
		// check if running
		$this->assertEquals(1, 1);
	}

}
