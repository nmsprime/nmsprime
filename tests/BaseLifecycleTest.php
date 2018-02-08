<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Base class to derive lifecycle tests for a model from
 *
 * This will reuse the static get_fake_data method in your model's seeder class (e.g. for create).
 * Assure that your seeder is up do date and running!
 *
 * @author Patrick Reichel
 */
class BaseLifecycleTest extends TestCase {

	// flag to show debug output from test runs
	/* protected $debug = True; */
	protected $debug = False;

	// define how often the create, update and delete tests should be run
	protected $testrun_count = 10;

	// flag to indicate if creating without data should fail
	protected $creating_empty_should_fail = True;

	// flag to indicate if creating the same data entry twice should fail (usually the case if there are unique fields
	protected $creating_twice_should_fail = True;

	// fields to be updated with random data
	protected $update_fields = [];

	// array holding the edit form structure
	protected $edit_field_structure = [];


	/**
	 * Constructor
	 *
	 * @author Patrick Reichel
	 */
	public function __construct() {

		$this->class_name = get_called_class();
		if ($this->class_name == 'BaseLifecycleTest') {
			// this is a base class doing nothing – derive your own lifecycle tests classes
			exit(0);
		}

		return parent::__construct();

	}

	public function createApplication() {

		$app = parent::createApplication();
		$this->_get_user();

		return $app;
	}

	/**
	 * Gets a user having the permissions needed for tests.
	 *
	 * @TODO: Switch from hardcoded user to dynamic getting from database or create a new one!
	 *
	 * @author Patrick Reichel
	 */
	protected function _get_user() {

		// TODO: do not hard code any user class, instead fetch a user dynamically
		//       ore add it only for testing (see Laravel factory stuff)
		$this->user = App\Authuser::findOrFail(1);
	}


	/**
	 * Tries to extract the structure of the <form> in edit view from controller.
	 * Should be called within each test visiting and filling create/edit form.
	 *
	 * If this fails: overwrite (hardcode?) in your derived class.
	 *
	 * @author Patrick Reichel
	 *
	 * @return array containing form field names as keys and testing methods as values
	 */
	protected function _get_form_structure($model = null) {

		/* require_once */
		$controller = new $this->controller();

		$form_raw = $controller->view_form_fields($model);

		$structure = array();

		foreach ($form_raw as $form_raw_field) {

			// check if field is hidden – in which case we don't want to fill it
			if (@$form_raw_field['hidden']) {
				continue;
			}

			// if there is no name set we cannot fill the field
			if (!$name = @$form_raw_field['name']) {
				continue;
			}

			// get the HTML type
			if (!$type = @$form_raw_field['form_type']) {
				continue;
			}

			// get possible (select) or default (others) values
			if (!@$form_raw_field['value']) {
				$value = null;
			}
			else {
				if (in_array($type, ['select', 'radio'])) {
					$value = array_keys($form_raw_field['value']);
				}
				else {
					$value = $form_raw_field['value'];
				}
			}

			$this->edit_field_structure[$name]['type'] = $type;
			$this->edit_field_structure[$name]['values'] = $value;

		}
	}


	/**
	 * Get fake data for one instance from seeder.
	 *
	 * @author Patrick Reichel
	 */
	protected function _get_fake_data() {

		return call_user_func($this->seeder."::get_fake_data");
	}


	/**
	 * Wrapper to fill a create form.
	 *
	 * @author Patrick Reichel
	 */
	protected function _fill_create_form($data) {

		$this->_fill_form($data, 'create');
	}


	/**
	 * Wrapper to fill a create form.
	 *
	 * @author Patrick Reichel
	 */
	protected function _fill_edit_form($data) {

		$this->_fill_form($data, 'update');
	}


	/**
	 * Fills a form with given data depending on form structure.
	 *
	 * @author Patrick Reichel
	 */
	protected function _fill_form($data, $method) {

		if ($this->debug) echo "\nFilling form";

		foreach ($this->edit_field_structure as $field_name => $structure) {

			// on update: only update defined fields
			if ($method == 'update') {
				if (!in_array($field_name, $this->update_fields)) {
					continue;
				}
			}

			// if the faker gave no data for the name => this field seems not to be required
			if (!$faked_data = @$data[$field_name]) {
				continue;
			}

			// convert DateTime objects to string
			if ($faked_data instanceof DateTime) {
				$_ = (array) $faked_data;
				$faked_data = explode(" ", $_['date'])[0];
			}
			// fill depending on field type
			switch ($structure['type']) {

			case 'select':
			case 'radio':
				// us faker data only if available as option; choose random value out of available randomly
				if (!in_array($faked_data, $structure['values'])) {
					$faked_data = $structure['values'][array_rand($structure['values'])];
				}

				if ($this->debug) echo "\nSelecting “".$faked_data."” in $field_name";
				$this->select($faked_data, $field_name);
				break;

			case 'checkbox':
				if (boolval($faked_data)) {
					if ($this->debug) echo "\nChecking $field_name";
					$this->check($field_name);
				}
				else {
					if ($this->debug) echo "\nUnchecking $field_name";
					$this->uncheck($field_name);
				}
				break;

			default:
				// simply put faked data into the field (should be text or textarea)
				if ($this->debug) echo "\nTyping “".$faked_data."” in $field_name";
				$this->type($faked_data, $field_name);
				break;

			}
		}

	}


	/**
	 * Try to create without data – we expect this to fail.
	 *
	 * @author Patrick Reichel
	 */
	public function testEmptyCreate() {

		echo "\nStarting ".$this->class_name."->".__FUNCTION__."()";

		if ($this->creating_empty_should_fail) {
			$msg_expected = "please correct the following errors";
		}
		else {
			$msg_expected = "Created!";
		};

		$this->actingAs($this->user)
			->visit(route("Contract.create"))
			->press("Save")
			->see($msg_expected)
		;
	}


	/**
	 * Try to create.
	 *
	 * @author Patrick Reichel
	 */
	public function testCreateWithFakeData() {

		echo "\nStarting ".$this->class_name."->".__FUNCTION__."()";
		$this->_get_form_structure();

		for ($i = 0; $i < $this->testrun_count; $i++) {

			$data = $this->_get_fake_data();

			$this->actingAs($this->user)
				->visit(route("Contract.create"));

			$this->_fill_create_form($data);

			$this->press("_save")
				->see("Created!")
			;
		}
	}


	/**
	 * Try to create the same data twice.
	 *
	 * @author Patrick Reichel
	 */
	public function testCreateTwiceUsingTheSameData() {

		echo "\nStarting ".$this->class_name."->".__FUNCTION__."()";
		$this->_get_form_structure();

		$data = $this->_get_fake_data();

		// this is the first create which should run
		$this->actingAs($this->user)
			->visit(route("Contract.create"));
		$this->_fill_create_form($data);
		$this->press("_save")
			->see("Created!")
		;

		// this is the second create (using the same data) which usually should fail
		if ($this->creating_twice_should_fail) {
			$msg_expected = "please correct the following errors";
		}
		else {
			$msg_expected = "Created!";
		}
		$this->actingAs($this->user)
			->visit(route("Contract.create"));
		$this->_fill_create_form($data);
		$this->press("_save")
			->see($msg_expected)
		;
	}


	/**
	 * Try to update a database entry.
	 *
	 * @author Patrick Reichel
	 */
	public function testUpdate() {

		echo "\nStarting ".$this->class_name."->".__FUNCTION__."()";
		echo "\n	WARNING: Not yet implemented!";
		/* $this->_get_form_structure($model_instance); */
	}


	/**
	 * Try to delete a database entry.
	 *
	 * @author Patrick Reichel
	 */
	public function testDelete() {

		echo "\nStarting ".$this->class_name."->".__FUNCTION__."()";
		echo "\n	WARNING: Not yet implemented!";
	}


}
