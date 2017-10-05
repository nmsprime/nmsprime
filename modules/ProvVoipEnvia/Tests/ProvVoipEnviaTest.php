<?php

namespace Modules\ProvVoipEnvia\Entities;

class ProvVoipEnviaTest extends \TestCase {

	/**
	 * Test the correct nmsprime response to ping against Envia API.
	 *
	 * @return void
	 */
	public function testEnviaActionMisc_ping()
	{
		$model = new ProvVoipEnvia();

		// prepare dataset for test (as is returned by Envia on successful ping request)
		$data = array();
		$ping_success_xml = '<?xml version="1.0" encoding="UTF-8"?><misc_ping_response><pong>pong</pong></misc_ping_response>';
		$data['xml'] = $ping_success_xml;
		$data['status'] = 200;

		$out = $model->process_envia_data('misc_ping', $data);

		$this->assertTrue(\Str::contains($out, 'All works fine'));
	}

}
