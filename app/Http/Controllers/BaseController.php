<?php

namespace App\Http\Controllers;

use Log;
use Str;
use Auth;
use View;
use Config;
use Bouncer;
use Request;
use Session;
use Redirect;
use BaseModel;
use Validator;
use GlobalConfig;
use App\V1\Service;
use App\V1\V1Trait;
use Monolog\Logger;
use App\V1\Repository;
use Yajra\DataTables\DataTables;

/*
 * BaseController: The Basic Controller in our MVC design.
 */
class BaseController extends Controller
{
    use V1Trait;
    /*
     * Default VIEW styling options
     * NOTE: All these values could be used in the inheritances classes
     */
    protected $edit_view_save_button = true;
    protected $save_button_name = 'Save';
    // key in messages language file
    protected $save_button_title_key = null;

    protected $edit_view_second_button = false;
    protected $second_button_name = 'Missing action name';
    protected $second_button_title_key = null;

    protected $edit_view_third_button = false;
    protected $third_button_name = 'Missing action name';
    protected $third_button_title_key = null;

    protected $relation_create_button = 'Create';

    // if set to true a create button on index view is available
    protected $index_create_allowed = true;
    protected $index_delete_allowed = true;

    protected $edit_left_md_size = 8;
    protected $index_left_md_size = 12;
    protected $edit_right_md_size = null;

    protected $index_tree_view = false;

    /**
     * Placeholder for Many-to-Many-Relation multiselect fields that should be handled generically (e.g. users of Ticket)
     * If special Abilities are needed to edit the valies, place classname in key like:
     * [ App\User::class => 'users_ids']
     * NOTE: When model is deleted all pivot entries will be detached and special handling in BaseModel@delete is omitted
     */
    protected $many_to_many = [];

    /**
     * File upload paths to handle file upload fields generically - see e.g. CompanyController, SepaAccountController
     *
     * NOTE: upload field has to be named like the corresponding select field of the upload field
     *
     * @var array 	['upload_field' => 'relative storage path']
     */
    protected $file_upload_paths = [];

    /**
     * Constructor
     *
     * Basically this is a placeholder for eventually later use. I need to
     * overwrite the constructor in a subclass – and want to call the parent
     * constructor if there are changes in base classes. But calling the
     * parent con is only possible if it is explicitely defined…
     *
     * @author Patrick Reichel
     */
    public function __construct()
    {
        // place your code here
    }

    /**
     * Base Function containing the default tabs for each model
     * Overwrite/Extend this function in child controller to add more tabs refering to new pages
     * Use models view_has_many() to add/structure panels inside separate tabs
     *
     * @param model: the model object to be displayed
     * @return: array of tab descriptions - e.g. [['name' => '..', 'route' => '', 'link' => [$model->id]], .. ]
     * @author: Torsten Schmidt, Nino Ryschawy
     */
    protected function editTabs($model)
    {
        $class = get_class($model);

        if (Str::contains($class, 'GuiLog')) {
            return;
        }

        $class_name = $model->get_model_name();

        return [[
            'name' => 'Edit',
            // 'route' => $class_name.'.edit',
            // 'link' => ['model_id' => $model->id, 'model' => $class_name],
        ],
            [
                'name' => 'Logging',
                'route' => 'GuiLog.filter',
                'link' => ['model_id' => $model->id, 'model' => $class_name],
            ],
        ];
    }

    public static function get_model_obj()
    {
        $classname = NamespaceController::get_model_name();

        // Rewrite model to check with new assigned Model
        if (! $classname) {
            return;
        }

        if (! class_exists($classname)) {
            return;
        }

        $obj = new $classname;

        return $obj;
    }

    public static function get_controller_obj()
    {
        $classname = NamespaceController::get_controller_name();

        if (! $classname) {
            return;
        }

        if (! class_exists($classname)) {
            return;
        }

        $obj = new $classname;

        return $obj;
    }

    public static function get_config_modules()
    {
        $modules = \Module::enabled();
        $links = ['Global Config' => 'GlobalConfig'];

        foreach ($modules as $module) {
            $mod_path = explode('/', $module->getPath());
            $tmp = end($mod_path);

            $mod_controller_name = 'Modules\\'.$tmp.'\\Http\\Controllers\\'.$tmp.'Controller';
            $mod_controller = new $mod_controller_name;

            if (method_exists($mod_controller, 'view_form_fields')) {
                $links[($module->get('description') == '') ? $tmp : $module->get('description')] = $tmp;
            }
        }
        // Sla (service level agreement) is not a separate module, but belongs to GlobalConfig
        $links['Sla'] = 'Sla';

        return $links;
    }

    /**
     * Set all nullable field without value given to null.
     * Use this to e.g. set dates to null (instead of 0000-00-00).
     *
     * Call this method on demand from your prepare_input()
     *
     * @author Patrick Reichel
     *
     * @param $nullable_fields array containing fields to check
     */
    protected function _nullify_fields($data, $nullable_fields = [])
    {
        foreach ($nullable_fields as $field) {
            if (isset($data[$field]) && ! $data[$field]) {
                $data[$field] = null;
            }
        }

        return $data;
    }

    /**
     * Returns a default input data array, that shall be overwritten
     * from the appropriate model controller if needed.
     *
     * Note: Will be running before Validation
     *
     * Tasks: Checkbox Entries will automatically set to 0 if not checked
     */
    protected function prepare_input($data)
    {
        // Checkbox Unset ?
        foreach ($this->view_form_fields(static::get_model_obj()) as $field) {
            // skip file upload fields
            if ($field['form_type'] == 'file') {
                continue;
            }

            // Checkbox Unset ?
            if (! isset($data[$field['name']]) && ($field['form_type'] == 'checkbox')) {
                $data[$field['name']] = 0;
            }

            // JavaScript controlled checkboxes sometimes returns “on” if checked – which results in
            // logical false (=0) in database so we have to overwrite this by 1
            // this is e.g. the case for the active checkbox on ProvVoip\Phonenumber
            // the value in $_POST seems to be browser dependend – extend the array if needed
            if (
                ($field['form_type'] == 'checkbox')
                &&
                (in_array(\Str::lower($data[$field['name']]), ['on', 'checked']))
            ) {
                $data['active'] = '1';
            }

            // multiple select?
            if ($field['form_type'] == 'select' && isset($field['options']['multiple'])) {
                $field['name'] = str_replace('[]', '', $field['name']);
                continue; 			// multiselects will have array in data so don't trim
            }
        }

        return $data;
    }

    /**
     * Normalizes numeric values to minimize problems for e.g. German users (using a comma instead a dot in float).
     *
     * @param $value the numeric string to normalize
     *
     * @author Patrick Reichel
     */
    protected function normalizeNumericString($value)
    {
        // take care of nullable fields
        if (is_null($value)) {
            return;
        }

        // Germans use comma as decimal separator – replace by dot
        $value = str_replace(',', '.', $value);

        return $value;
    }

    /**
     * Returns a default input data array, that shall be overwritten
     * from the appropriate model controller if needed.
     *
     * Note: Will be running _after_ Validation
     */
    protected function prepare_input_post_validation($data)
    {
        return $data;
    }

    /**
     * Returns an array of validation rules in dependence of the formular Input data
     * of the http request, that shall be overwritten from the appropriate model
     * controller if needed.
     *
     * Note: Will be running before Validation
     */
    protected function prepare_rules($rules, $data)
    {
        return $rules;
    }

    /**
     * Prepare tabs for edit page
     * Merge defined tabs from editTabs() and view_has_many()
     *
     * @author Nino Ryschawy
     * @param relations  from view_has_many()
     * @param tabs       from editTabs()
     * @return array     tabs for split-no-panel.blade and edit.blade
     */
    protected function prepare_tabs($relations, $tabs)
    {
        // Generate tabs from array structure of relations
        foreach ($relations as $tab => $panels) {
            if (! $this->tabDefined($tab, $tabs)) {
                $tabs[] = ['name' => $tab];
            }
        }

        return $tabs;
    }

    /**
     * Check if tab of relations (defined in view_has_many()) is already defined tabs from editTabs()
     *
     * @return bool
     */
    private function tabDefined($relationsTab, $editTabs)
    {
        foreach ($editTabs as $key => $array) {
            if ($array['name'] == $relationsTab) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle file uploads.
     * - check if a file is uploaded
     * - if so:
     *   - move file to dst_path
     *   - overwrite base_field in Input with filename
     *
     * @param base_field Input field to be processed
     * @param dst_path Path to move uploaded file in
     * @author Patrick Reichel
     */
    protected function handle_file_upload($base_field, $dst_path)
    {
        $upload_field = $base_field.'_upload';

        if (! Request::hasFile($upload_field)) {
            return;
        }

        // get filename
        $filename = Request::file($upload_field)->getClientOriginalName();

        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $fn = pathinfo($filename, PATHINFO_FILENAME);
        $filename = sanitize_filename($fn).".$ext";

        // move file
        Request::file($upload_field)->move($dst_path, $filename);

        // place filename as chosen value in Input field
        Request::merge([$base_field => $filename]);

        if (\Module::collections()->has('ProvBase') && $base_field == 'firmware' && Request::get('device') == 'tr069') {
            // file upload using curl_file_create and method PUT adds headers
            // Content-Disposition, Content-Type and boundaries, which corrupts
            // the file to be uploaded, thus call curl from command line
            /*
            \Modules\ProvBase\Entities\Modem::callGenieAcsApi("files/$filename", 'PUT',
                ['file' => curl_file_create("/tftpboot/fw/$filename")],
                ['Content-Type: application/x-www-form-urlencoded']
            );
            */
            exec("curl -i \"http://localhost:7557/files/$filename\" -X PUT --data-binary @\"/tftpboot/fw/$filename\"");
        }

        return $filename;
    }

    /**
     * Handle file uploads generically in store and update function
     *
     * NOTE: use global Variable 'file_upload_paths' in Controller to specify DB column and storage path
     *
     * @param array 	Input data array passed by reference
     */
    private function _handle_file_upload(&$data)
    {
        foreach ($this->file_upload_paths as $column => $path) {
            $filename = $this->handle_file_upload($column, storage_path($path));

            if ($filename !== null) {
                $data[$column] = $filename;
            }
        }
    }

    /**
     * Set required default Variables for View
     * Use it like:
     *   View::make('Route.Name', $this->compact_prep_view(compact('ownvar1', 'ownvar2')));
     *
     * @author Torsten Schmidt
     */
    public function compact_prep_view()
    {
        $a = func_get_args()[0];

        $a['user'] = Auth::user();

        $model = static::get_model_obj();

        if (! $model) {
            $model = new BaseModel;
        }

        if (! isset($a['action'])) {
            $a['action'] = 'update';
        }

        if (! isset($a['networks'])) {
            $a['networks'] = [];
            if (\Module::collections()->has('HfcReq') && Bouncer::can('view', \Modules\HfcBase\Entities\TreeErd::class)) {
                $a['networks'] = \Modules\HfcReq\Entities\NetElement::getNetsWithClusters();
            }
        }

        if (! isset($a['view_header_links'])) {
            $a['view_header_links'] = BaseViewController::view_main_menus();
        }

        if (! isset($a['route_name'])) {
            $a['route_name'] = NamespaceController::get_route_name();
        }

        if (! isset($a['ajax_route_name'])) {
            $a['ajax_route_name'] = $a['route_name'].'.data';
        }

        if (! isset($a['model_name'])) {
            $a['model_name'] = NamespaceController::get_model_name();
        }

        if (! isset($a['view_header'])) {
            $a['view_header'] = $model->view_headline();
        }

        if (! isset($a['view_no_entries'])) {
            $a['view_no_entries'] = $model->view_no_entries();
        }

        if (! isset($a['headline'])) {
            $a['headline'] = '';
        }

        if (! isset($a['form_update'])) {
            $a['form_update'] = NamespaceController::get_route_name().'.update';
        }

        if (! isset($a['edit_left_md_size'])) {
            $a['edit_left_md_size'] = $this->edit_left_md_size;
        }

        if (! isset($a['index_left_md_size'])) {
            $a['index_left_md_size'] = $this->index_left_md_size;
        }

        if (! is_null($this->edit_right_md_size) && ! isset($a['edit_right_md_size'])) {
            $a['edit_right_md_size'] = $this->edit_right_md_size;
        }

        if (! isset($a['html_title'])) {
            $a['html_title'] = 'NMS Prime - '.BaseViewController::translate_view(NamespaceController::module_get_pure_model_name(), 'Header');
        }

        if ((\Module::collections()->has('ProvVoipEnvia')) && (! isset($a['envia_interactioncount']))) {
            $a['envia_interactioncount'] = \Modules\ProvVoipEnvia\Entities\EnviaOrder::get_user_interaction_needing_enviaorder_count();
        }

        if (\Module::collections()->has('Dashboard')) {
            $a['modem_statistics'] = \Modules\Dashboard\Http\Controllers\DashboardController::get_modem_statistics();
        }

        if (! isset($a['view_help'])) {
            $a['view_help'] = $this->view_help();
        }

        $a['edit_view_save_button'] = $this->edit_view_save_button;
        $a['save_button_name'] = $this->save_button_name;
        $a['second_button_name'] = $this->second_button_name;
        $a['edit_view_second_button'] = $this->edit_view_second_button;
        $a['second_button_title_key'] = $this->second_button_title_key;
        $a['third_button_name'] = $this->third_button_name;
        $a['edit_view_third_button'] = $this->edit_view_third_button;
        $a['third_button_title_key'] = $this->third_button_title_key;
        $a['save_button_title_key'] = $this->save_button_title_key;

        // Get Framework Informations
        $gc = \Cache::remember('GlobalConfig', 60, function () {
            return GlobalConfig::first();
        });
        $a['framework']['header1'] = $gc->headline1;
        $a['framework']['header2'] = $gc->headline2;
        $a['framework']['version'] = $gc->version();

        return $a;
    }

    /**
     * Perform a fulltext search.
     *
     * @author Patrick Reichel
     */
    public function fulltextSearch()
    {
        // get the search scope
        $scope = Request::get('scope');

        // get the mode to use and transform to sql syntax
        $mode = Request::get('mode');

        // get the query to search for
        $query = Request::get('query');

        if ($scope == 'all') {
            $view_path = 'Generic.searchglobal';
            $obj = new BaseModel;
            $view_header = 'Global Search';
        } else {
            $obj = static::get_model_obj();
            $view_path = 'Generic.index';

            if (View::exists(NamespaceController::get_view_name().'.index')) {
                $view_path = NamespaceController::get_view_name().'.index';
            }
        }

        $create_allowed = static::get_controller_obj()->index_create_allowed;
        $delete_allowed = static::get_controller_obj()->index_delete_allowed;

        $view_var = collect();
        foreach ($obj->getFulltextSearchResults($scope, $mode, $query, Request::get('preselect_field'), Request::get('preselect_value')) as $result) {
            $view_var = $view_var->merge($result->get());
        }

        return View::make($view_path, $this->compact_prep_view(compact('view_header', 'view_var', 'create_allowed', 'delete_allowed', 'query', 'scope')));
    }

    /**
     * Overwrite this method in your controllers to inject additional data in your edit view
     * Default is an empty array that simply will be ignored on generic views
     *
     * For an example view EnviaOrder and their edit.blade.php
     *
     * @author Patrick Reichel
     *
     * @return data to be injected; should be an array
     */
    protected function getAdditionalDataForEditView($model)
    {
        return [];
    }

    /**
     * Use this in your Controller->view_form_fields() methods to add a first [0 => ''] in front of your options coming from database
     * Don't use array_merge for this topic as this will reassing numerical keys and in doing so destroying the mapping of database IDs!!
     *
     * Watch ProductController for a usage example.
     *
     * @author Patrick Reichel
     *
     * @param $options the options array generated from database
     * @param $first_value value to be set at $options[0] – defaults to empty string
     *
     * @return $options array with 0 element on first position
     */
    protected function _add_empty_first_element_to_options($options, $first_value = '')
    {
        $ret = [0 => $first_value];

        foreach ($options as $key => $value) {
            $ret[$key] = $value;
        }

        return $ret;
    }

    /**
     * Display a listing of all objects of the calling model
     *
     * @return View
     */
    public function index()
    {
        $model = static::get_model_obj();
        $headline = BaseViewController::translate_view($model->view_headline(), 'Header', 2);
        $view_header = BaseViewController::translate_view('Overview', 'Header');
        $create_allowed = static::get_controller_obj()->index_create_allowed;
        $delete_allowed = static::get_controller_obj()->index_delete_allowed;

        if ($this->index_tree_view) {
            // TODO: remove orWhere statement when it is sure that parent_id is nullable and can not be 0 in all NMSPrime instances and after new installation!!!
            $view_var = $model::whereNull('parent_id')->orWhere('parent_id', 0)->get();
            $undeletables = $model::undeletables();

            return View::make('Generic.tree', $this->compact_prep_view(compact('headline', 'view_header', 'view_var', 'create_allowed', 'undeletables')));
        }

        $view_path = 'Generic.index';
        if (View::exists(NamespaceController::get_view_name().'.index')) {
            $view_path = NamespaceController::get_view_name().'.index';
        }

        // TODO: show only entries a user has at view rights on model and net!!
        Log::warning('Showing only index() elements a user can access is not yet implemented');

        return View::make($view_path, $this->compact_prep_view(compact('headline', 'view_header', 'model', 'create_allowed', 'delete_allowed')));
    }

    /**
     * Show the form for creating a new model item
     *
     * @return View
     */
    public function create()
    {
        $model = static::get_model_obj();
        $action = 'create';

        $view_header = BaseViewController::translate_view($model->view_headline(), 'Header');
        $headline = BaseViewController::compute_headline(NamespaceController::get_route_name(), $view_header, null, $_GET);
        $fields = BaseViewController::prepare_form_fields(static::get_controller_obj()->view_form_fields($model), $model);
        $form_fields = BaseViewController::add_html_string($fields, 'create');
        // $form_fields = BaseViewController::add_html_string (static::get_controller_obj()->view_form_fields($model), $model, 'create');

        $view_path = 'Generic.create';
        $form_path = 'Generic.form';

        // proof if there is a special view for the calling model
        if (View::exists(NamespaceController::get_view_name().'.create')) {
            $view_path = NamespaceController::get_view_name().'.create';
        }
        if (View::exists(NamespaceController::get_view_name().'.form')) {
            $form_path = NamespaceController::get_view_name().'.form';
        }

        return View::make($view_path, $this->compact_prep_view(compact('view_header', 'form_fields', 'form_path', 'headline', 'action')));
    }

    /**
     * API equivalent of create()
     *
     * @author Ole Ernst
     *
     * @return JsonResponse
     */
    public function api_create($ver)
    {
        if ($ver !== '0') {
            return response()->json(['ret' => "Version $ver not supported"]);
        }

        $model = static::get_model_obj();
        $fields = BaseViewController::prepare_form_fields(static::get_controller_obj()->view_form_fields($model), $model);
        $fields = $this->apiHandleHtmlFields($fields);

        return response()->json($fields);
    }

    /**
     * As form fields with form_type => 'html' can have multiple Input fields these have to be extracted for the API
     *  This replaces the html form field by multiple fields expressing the input fields that the html field contains
     *
     * @author Nino Ryschawy
     * @return array
     */
    private function apiHandleHtmlFields($fields)
    {
        foreach ($fields as $key => $field) {
            if (! (isset($field['form_type']) && $field['form_type'] == 'html' && isset($field['html']))) {
                continue;
            }

            preg_match_all('/<input.*?>/', $field['html'], $matches);

            if (! $matches) {
                continue;
            }

            foreach ($matches[0] as $input) {
                preg_match('/name=(.*?) /', $input, $name);

                if (! $name) {
                    $name = $field['name'] ?? 'without name';
                    Log::error("Name of input field $name of view_form_fields missing");

                    continue;
                }

                $name = str_replace(['"', "'"], '', $name[1]);

                $field['name'] = $name;

                if (count($matches[0]) > 1) {
                    $field['description'] .= ' '.$name;
                }

                $fields[] = $field;
            }

            unset($fields[$key]);
        }

        return $fields;
    }

    /**
     * Generic store function - stores an object of the calling model
     * @param redirect: if set to false returns id of the new created object (default: true)
     * @return: html redirection to edit page (or if param $redirect is false the new added object id)
     */
    public function store($redirect = true)
    {
        $obj = static::get_model_obj();
        $controller = static::get_controller_obj();

        // Prepare and Validate Input
        $data = $controller->prepare_input(Request::all());
        $rules = $controller->prepare_rules($obj->rules(), $data);
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            Log::info('Validation Rule Error: '.$validator->errors());

            $msg = trans('validation.invalid_input');
            $obj->addAboveMessage($msg, 'error', 'form');

            return Redirect::back()->withErrors($validator)->withInput();
        }
        $data = $controller->prepare_input_post_validation($data);

        // Handle file uploads generically - this must happen after the validation as moving the file before leads always to validation error
        $this->_handle_file_upload($data);

        $obj = $obj::create($data);

        // Add N:M Relations
        $this->_set_many_to_many_relations($obj, $data);

        $id = $obj->id;
        if (! $redirect) {
            return $id;
        }

        $msg = trans('messages.created');
        $obj->addAboveMessage($msg, 'success', 'form');

        return Redirect::route(NamespaceController::get_route_name().'.edit', $id)->with('message', $msg)->with('message_color', 'success');
    }

    /**
     * API equivalent of store()
     *
     * @author Ole Ernst
     *
     * @return JsonResponse
     */
    public function api_store($ver)
    {
        if ($ver === '0') {
            $obj = static::get_model_obj();
            $controller = static::get_controller_obj();

            // Prepare and Validate Input
            $data = $this->_api_prepopulate_fields($obj, $controller);
            $data = $controller->prepare_input($data);
            $rules = $controller->prepare_rules($obj->rules(), $data);
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                $ret = [];
                foreach ($validator->errors()->getMessages() as $field => $error) {
                    $ret[$field] = $error;
                }

                return response()->json(['ret' => $ret]);
            }
            $data = $controller->prepare_input_post_validation($data);

            $obj = $obj::create($data);

            // Add N:M Relations
            self::_set_many_to_many_relations($obj, $data);

            return response()->json(['ret' => 'success', 'id' => $obj->id]);
        } elseif ($ver === '1') {
            $data = Request::all();
            $model = (new Service(new Repository(static::get_model_obj())))->create($data);

            return $this->response($model, 200);
        } else {
            return response()->json(['ret' => "Version $ver not supported"]);
        }
    }

    /**
     * Show the editing form of the calling Object
     *
     * @param  int  $id
     * @return View
     */
    public function edit($id)
    {
        $model = static::get_model_obj();
        $view_var = $model->findOrFail($id);

        $view_header = BaseViewController::translate_view($model->view_headline(), 'Header');
        $headline = BaseViewController::compute_headline(NamespaceController::get_route_name(), $view_header, $view_var);

        $fields = BaseViewController::prepare_form_fields(static::get_controller_obj()->view_form_fields($view_var), $view_var);
        $form_fields = BaseViewController::add_html_string($fields, 'edit');

        // view_has_many should actually be a controller function!
        $relations = $view_var->view_has_many();
        $tabs = $this->prepare_tabs($relations, $this->editTabs($view_var));

        // check if there is additional data to be passed to blade template
        // on demand overwrite base method getAdditionalDataForEditView($model)
        $additional_data = $this->getAdditionalDataForEditView($view_var);

        $view_path = 'Generic.edit';
        $form_path = 'Generic.form';

        // proof if there are special views for the calling model
        if (View::exists(NamespaceController::get_view_name().'.edit')) {
            $view_path = NamespaceController::get_view_name().'.edit';
        }
        if (View::exists(NamespaceController::get_view_name().'.form')) {
            $form_path = NamespaceController::get_view_name().'.form';
        }

        // $config_routes = BaseController::get_config_modules();
        return View::make($view_path, $this->compact_prep_view(compact('model_name', 'view_var', 'view_header', 'form_path', 'form_fields', 'headline', 'tabs', 'relations', 'action', 'additional_data')));
    }

    /**
     * Update the specified data in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $obj = static::get_model_obj()->findOrFail($id);
        $controller = static::get_controller_obj();

        // Prepare and Validate Input
        $data = $controller->prepare_input(Request::all());
        $data['id'] = $obj->id = $id;
        $rules = $controller->prepare_rules($obj->rules(), $data);
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            Log::info('Validation Rule Error: '.$validator->errors());

            $msg = trans('validation.invalid_input');
            $obj->addAboveMessage($msg, 'error', 'form');

            return Redirect::back()->withErrors($validator)->withInput();
        }

        // Handle file uploads generically - this must happen after the validation as moving the file before leads always to validation error
        $this->_handle_file_upload($data);
        $data = $controller->prepare_input_post_validation($data);

        // update timestamp, this forces to run all observer's
        // Note: calling touch() forces a direct save() which calls all observers before we update $data
        //       when exit in middleware to a new view page (like Modem restart) this kill update process
        //       so the solution is not to run touch(), we set the updated_at field directly
        $data['updated_at'] = \Carbon\Carbon::now(Config::get('app.timezone'));

        // Note: Eloquent Update requires updated_at to either be in the fillable array or to have a guarded field
        //       without updated_at field. So we globally use a guarded field from now, to use the update timestamp
        $obj->update($data);

        // Add N:M Relations
        if (isset($this->many_to_many) && is_array($this->many_to_many)) {
            $this->_set_many_to_many_relations($obj, $data);
        }

        // create messages depending on error state created while observer execution
        // TODO: check if giving msg/color to route is still wanted or obsolete by the new tmp_*_above_* messages format
        if (! Session::has('error')) {
            $msg = 'Updated!';
            $color = 'success';
            $obj->addAboveMessage($msg, 'success', 'form');
        } else {
            $msg = Session::get('error');
            $color = 'warning';
            $obj->addAboveMessage($msg, 'error', 'form');
        }

        $route_model = NamespaceController::get_route_name();

        if (in_array($route_model, self::get_config_modules())) {
            return Redirect::route('Config.index');
        }

        return Redirect::route($route_model.'.edit', $id)->with('message', $msg)->with('message_color', $color);
    }

    /**
     * API equivalent of update()
     *
     * @author Ole Ernst
     *
     * @return JsonResponse
     */
    public function api_update($ver, $id)
    {
        if ($ver === '0') {
            $obj = static::get_model_obj()->findOrFail($id);
            $controller = static::get_controller_obj();

            // Prepare and Validate Input
            $data = $this->_api_prepopulate_fields($obj, $controller);
            $data['id'] = $obj->id = $id;
            $data = $controller->prepare_input($data);
            $rules = $controller->prepare_rules($obj->rules(), $data);
            $validator = Validator::make($data, $rules);
            $data = $controller->prepare_input_post_validation($data);

            if ($validator->fails()) {
                $ret = [];
                foreach ($validator->errors()->getMessages() as $field => $error) {
                    $ret[$field] = $error;
                }

                return response()->json(['ret' => $ret]);
            }

            $data['updated_at'] = \Carbon\Carbon::now(Config::get('app.timezone'));

            $obj->update($data);

            // Add N:M Relations
            self::_set_many_to_many_relations($obj, $data);

            return response()->json(['ret' => 'success']);
        } elseif ($ver === '1') {
            $data = Request::all();
            $model = (new Service(new Repository(static::get_model_obj())))->update($id, $data);

            return $this->response($model, 200);
        } else {
            return response()->json(['ret' => "Version $ver not supported"]);
        }
    }

    /**
     * Prepopluate all data fields of the corresponding object, so that an API
     * request only needs to send the fields which should be updated and not all
     *
     * @author Ole Ernst
     *
     * @return array
     */
    private function _api_prepopulate_fields($obj, $ctrl)
    {
        $fields = BaseViewController::prepare_form_fields($ctrl->view_form_fields($obj), $obj);
        $fields = $this->apiHandleHtmlFields($fields);
        $inputs = Request::all();
        $data = [];

        foreach ($fields as $field) {
            $name = $field['name'];
            $data[$name] = Request::has($name) ? Request::input($name) : $field['field_value'];
        }

        return $data;
    }

    /**
     * Store Many to Many Relations in Pivot table
     *
     * IMPORTANT NOTES:
     *	 To assign a model to the pivot table we need an extra multiselect field in the controllers
     *	 	view_form_fields() that must be mentioned inside the guarded array of the model and the many_to_many array of the Controller!!
     *	 The multiselect's field name must be in form of the models relation function and a concatenated '_ids'
     *		like: '<relation-function>_ids' , e.g. 'users_ids' for the multiselect in Tickets view to assign users
     *
     * @param object 	The Object to store/update
     * @param array 	Input Data
     *
     * @author Nino Ryschawy
     */
    private function _set_many_to_many_relations($obj, $data)
    {
        if (Bouncer::cannot('update', get_class($obj))) {
            return;
        }

        foreach ($this->many_to_many as $key => $field) {
            if (isset($field['classes']) &&
                (Bouncer::cannot('update', $field['classes'][0]) || Bouncer::cannot('update', $field['classes'][1]))) {
                Session::push('error', "You are not allowed to edit {$field['classes'][0]} or {$field['classes'][1]}");
                continue;
            }

            if (! isset($data[$field['field']])) {
                $data[$field['field']] = [];
            }

            $changed_attributes = collect();
            $eloquent_relation_function = explode('_', $field['field'])[0];
            $eloquent_relation = $obj->$eloquent_relation_function();
            $attached_entities = $eloquent_relation->get();
            $attached_ids = $attached_entities->pluck('id')->all();

            // attach new assignments
            foreach ($data[$field['field']] as $form_id) {
                if ($form_id && ! in_array($form_id, $attached_ids)) {
                    $eloquent_relation->attach($form_id, ['created_at' => date('Y-m-d H:i:s')]);

                    $attribute = $attached_entities->where('id', '=', $form_id)->first();

                    $attribute = $attribute->name ?? $attribute->login_name ?? 'id '.$form_id;
                    $attribute .= ' attached';
                    $changed_attributes->push($attribute);
                }
            }

            // detach removed assignments (from selected multiselect options)
            foreach ($attached_ids as $foreign_id) {
                if (! in_array($foreign_id, $data[$field['field']])) {
                    $removed_entity = $attached_entities->where('id', '=', $foreign_id)->first();
                    $eloquent_relation->detach($foreign_id);

                    $attribute = $removed_entity->name ?? $removed_entity->login_name ?? 'id '.$foreign_id;
                    $attribute .= ' removed';
                    $changed_attributes->push($attribute);
                }
            }
        }

        if (isset($changed_attributes) && $changed_attributes->isNotEmpty()) {
            $user = Auth::user();
            \App\GuiLog::log_changes([
                'user_id' => $user ? $user->id : 0,
                'username' 	=> $user ? $user->first_name.' '.$user->last_name : 'cronjob',
                'method' 	=> 'updated N:M',
                'model' 	=> Str::singular(Str::studly($obj->table)),
                'model_id'  => $obj->id,
                'text'		=> $changed_attributes->implode("\n"),
            ]);
        }

        return isset($changed_attributes) ? $changed_attributes : collect();
    }

    /**
     * Removes a specified model object from storage
     *
     * @param  int  $id: bulk delete if == 0
     * @return Response
     */
    public function destroy($id)
    {
        // helper to inform the user about success on deletion
        $to_delete = 0;
        $deleted = 0;
        // bulk delete
        if ($id == 0) {
            $obj = static::get_model_obj();

            // Error Message when no Model is specified - NOTE: delete_message must be an array of the structure below !
            if (! Request::get('ids')) {
                $message = trans('messages.base.delete.noEntry');
                $obj->addAboveMessage($message, 'error');

                return Redirect::back()->with('delete_message', ['message' => $message, 'class' => NamespaceController::get_route_name(), 'color' => 'danger']);
            }

            foreach (Request::get('ids') as $id => $val) {
                $obj = $obj->findOrFail($id);
                $to_delete++;

                /* detach all pivot entries if many-to-many relations exist
                 * Note: This should be implemented as soft detach, but this functionality is actually not implemented by laravel
                 * So we could do it by ourself (DB::update(...)) but as many pivot tables do actually not have deleted_at timestamp
                 * we can preliminary just keep them in DB
                 */
                // foreach ($this->many_to_many as $rel) {
                // 	$func = explode('_', $rel)[0];
                // 	$obj->$func()->detach();
                // }

                if ($obj->delete()) {
                    $deleted++;
                }
            }
        } else {
            $to_delete++;
            $obj = static::get_model_obj();
            if ($obj->findOrFail($id)->delete()) {
                $deleted++;
            }
        }
        $obj = isset($obj) ? $obj : static::get_model_obj();
        $class = NamespaceController::get_route_name();

        if (! $deleted && ! $obj->force_delete) {
            $color = 'danger';
            $message = trans('messages.base.delete.fail', ['model' => $class, 'id' => '']);
            $obj->addAboveMessage($message, 'error');
        } elseif (($deleted == $to_delete) || $obj->force_delete) {
            $color = 'success';
            $message = trans('messages.base.delete.success', ['model' => $class, 'id' => '']);
            $obj->addAboveMessage($message, 'success');
        } else {
            $color = 'warning';
            $message = trans('messages.base.delete.multiSuccess', ['deleted' => $deleted, 'to_delete' => $to_delete, 'model' => $class]);
            $obj->addAboveMessage($message, 'warning');
        }

        return Redirect::back()->with('delete_message', ['message' => $message, 'class' => $class, 'color' => $color]);
    }

    /**
     * API equivalent of destroy()
     * Recursive deletion is not implemented, as this should be handled by the client
     *
     * @author Ole Ernst
     *
     * @return JsonResponse
     */
    public function api_destroy($ver, $id)
    {
        if ($ver === '0') {
            $obj = static::get_model_obj();
            if ($obj->findOrFail($id)->delete()) {
                $ret = 'success';
            } else {
                $ret = 'failure';
            }

            return response()->json(['ret' => $ret]);
        } elseif ($ver === '1') {
            $service = new Service(new Repository(static::get_model_obj()));
            $data = $service->delete($id);

            return $this->response([]);
        } else {
            return response()->json(['ret' => "Version $ver not supported"]);
        }
    }

    /**
     * Detach a pivot entry of an n-m relationship
     *
     * @param 	id 			Integer 	Model ID the relational model is attached to
     * @param 	function 	String 		Function Name of the N-M Relation
     * @return 	Response 	Object 		Redirect back
     *
     * @author Nino Ryschawy
     */
    public function detach($id, $function)
    {
        $model = NamespaceController::get_model_name();
        $model = $model::find($id);

        if (\Request::has('ids')) {
            $model->{$function}()->detach(array_keys(\Request::get('ids')));
        }

        return \Redirect::back();
    }

    /**
     * API equivalent of the edit view
     *
     * @author Ole Ernst
     *
     * @return JsonResponse
     */
    public function api_get($ver, $id)
    {
        if ($ver === '0') {
            return static::get_model_obj()->findOrFail($id);
        } elseif ($ver === '1') {
            $resourceOptions = $this->parseResourceOptions();
            $service = new Service(new Repository(static::get_model_obj()));
            $data = $service->getById($id, $resourceOptions);
            $parsedData = $this->parseData($data, $resourceOptions);

            return $this->response($parsedData);
        } else {
            return response()->json(['ret' => "Version $ver not supported"]);
        }
    }

    /**
     * Get status of object via API
     *
     * @author Ole Ernst
     *
     * @return JsonResponse
     */
    public function api_status($ver, $id)
    {
        if ($ver === '0') {
            return response()->json(['ret' => 'success']);
        } else {
            return response()->json(['ret' => "Version $ver not supported"]);
        }
    }

    /**
     * API equivalent of index()
     *
     * @author Ole Ernst
     *
     * @return JsonResponse
     */
    public function api_index($ver)
    {
        if ($ver === '0') {
            $query = static::get_model_obj();
            foreach (Request::all() as $key => $val) {
                $query = $query->where($key, $val);
            }

            try {
                return $query->get();
            } catch (\Exception $e) {
                return response()->json(['ret' => $e]);
            }
        } elseif ($ver === '1') {
            $resourceOptions = $this->parseResourceOptions();
            $service = new Service(new Repository(static::get_model_obj()));
            $data = $service->getAll($resourceOptions);
            $parsedData = $this->parseData($data, $resourceOptions);

            return $this->response($parsedData);
        } else {
            return response()->json(['ret' => "Version $ver not supported"]);
        }
    }

    // Deprecated:
    protected $output_format;

    /**
     *  json abstraction layer
     */
    protected function json()
    {
        $this->output_format = 'json';

        $data = Request::all();
        $id = $data['id'];
        $func = $data['function'];

        if ($func == 'update') {
            return $this->update($id);
        }
    }

    /**
     *  Maybe a generic redirect is an option, but
     *  howto handle fails etc. ?
     */
    protected function generic_return($view, $param = null)
    {
        if ($this->output_format == 'json') {
            return $param;
        }

        if (isset($param)) {
            return Redirect::route($view, $param);
        } else {
            return Redirect::route($view);
        }
    }

    /**
     * Tree View Specific Stuff
     *
     * TODO: Implement the Tree View as Javascript Tree Table - preparations are already made in comments (use jstree.min.js)
     * 		 see Color Admin Bootstrap Theme: http://wrapbootstrap.com/preview/WB0N89JMK -> UI-Elements -> Tree View
     *
     * @author Nino Ryschawy
     *
     * global Variables
     *	$INDEX  : used for shifting the children elements
     *	$I 		: used to increment over specficied colours (defined in variable)
     */
    public static $INDEX = 0;
    public static $I = 0;
    public static $colours = ['', 'text-danger', 'text-success', 'text-warning', 'text-info'];

    /**
     * Returns the Tree View (Table) as HTML Text
     *
     * IMPORTANT NOTES
     * If the Model uses the Generic BaseController@index function a separate index.blade.php has to be installed in
     *	modules/Resources/Modelname/ that includes the Generic.tree blade
     * The Generic.tree blade calls this function
     * The Model currently has to have a function called get_tree_list that shall return the ordered tree of objects
     *	(with delete_disabled) - see NetElementType.php
     */
    public static function make_tree_table()
    {
        $data = '';

        // tree with select fields
        // $data .= '<div id="jstree-checkable" class="jstree jstree-2 jstree-default jstree-checkbox-selection" role="tree" aria-multiselectable="true" tabindex="0" aria-activedescendant="j1" aria-busy="false" aria-selected="false">';
        // $data .= '<ul class="jstree-container-ul jstree-children jstree-wholerow-ul jstree-no-dots" role="group">';

        // default tree
        // $data = '<div id="jstree-default" class="jstree jstree-1 jstree-default" role="tree" aria-multiselectable="true" tabindex="0" aria-activedescendant="j1" aria-busy="false">';
        // $data .= '<ul class="jstree-children" role="group" style>';

        $model = NamespaceController::get_model_name();
        $data .= self::_create_index_view_data($model::get_tree_list());

        // $data .= '</ul></div></div>';

        return $data;
    }

    /**
     * writes whole index view table data as HTML in string
     *
     * @param array with all Model Objects in hierarchical tree structure
     *
     * @author Nino Ryschawy
     */
    private static function _create_index_view_data($ordered_tree)
    {
        $data = '';

        foreach ($ordered_tree as $object) {
            // foreach ($ordered_tree as $key => $object)
            if (is_array($object)) {
                self::$INDEX += 1;
                if (self::$INDEX == 1) {
                    self::$I--;
                }

                // $data .= '<ul role="group" class="jstree-children" style>';
                $data .= self::_create_index_view_data($object);
            // $data .= '</ul>';
            } else {
                // $data .= self::_print_label_elem($object, isset($ordered_tree[$key+1]));
                $data .= self::_print_label_elem($object);
            }

            if (self::$INDEX == 0) {
                self::$I++;
            }
        }

        self::$INDEX -= 1;
        $data .= (self::$INDEX == 0) && (strpos(substr($data, strlen($data) - 8), '<br><br>') === false) ? '<br>' : '';

        return $data;
    }

    /**
     * Returns the HTML string for one label Element for Tree Index View
     *
     * @param $object 	Model Object
     *
     * @author Nino Ryschawy
     *
     * TODO: implement with jstree.min.js
     */
    // public static function _print_label_elem($object, $list = false)
    private static function _print_label_elem($object)
    {
        $cur_model_complete = get_class($object);
        $cur_model_parts = explode('\\', $cur_model_complete);
        $cur_model = array_pop($cur_model_parts);

        $data = '';

        // default tree
        // $data .= '<li role="treeitem" data-jstree="{&quot;opened&quot;:true, &quot;selected&quot;:true" aria-selected="false" aria-level="'.self::$INDEX.'" aria-labelledby="'.self::$I.'_anchor" aria-expanded="true" id="j'.self::$I.'" class="jstree-node jstree-open">';
        // 	$data .= $list ? '<i class="jstree-icon jstree-ocl" role="presentation"></i>' : '';

        // tree with select fields
        // $data .= '<li role="treeitem" aria-selected="false" aria-level="'.self::$INDEX.'" aria-labelledby="'.self::$I.'_anchor" id="j'.self::$I.'" class="jstree-node  jstree-leaf">';
        // 	$data .= '<div unselectable="on" role="presentation" class="jstree-wholerow">&nbsp;</div><i class="jstree-icon jstree-ocl" role="presentation"></i>';

        for ($cnt = 0; $cnt <= self::$INDEX; $cnt++) {
            $data .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        }

        $data .= \Form::checkbox('ids['.$object->id.']', 1, null, null, ['style' => 'simple', 'disabled' => $object->index_delete_disabled ? 'disabled' : null]).'&nbsp;&nbsp;';
        // $data .= \HTML::linkRoute($cur_model.'.edit', $object->view_index_label(), $object->id, ['class' => self::$colours[self::$I % count(self::$colours)]]);
        $name = self::$INDEX == 0 ? '<strong>'.$object->view_index_label().'</strong>' : $object->view_index_label();
        $data .= '<a class="'.self::$colours[self::$I % count(self::$colours)].'" href="'.route($cur_model.'.edit', $object->id).'">'.$name.'</a>';
        $data .= '<br>';
        // link for javascript tree
        // $data .= '<a class="jstree-anchor" href="'.route($cur_model.'.edit', $object->view_index_label(), $object->id).'" tabindex="-1" id="'.self::$I.'_anchor">';
        // $data .= '<i class="jstree-icon jstree-themeicon fa fa-folder text-warning fa-lg jstree-themeicon-custom" role="presentation"></i>';
        // $data .= $object->view_index_label().'</a>';
        // $data .= '</li>';

        return $data;
    }

    /**
     * Return a List of Filenames (e.g. PDF Logos, Tex Templates)
     *
     * @param 	dir 	Directory name relative to storage/app/config/
     * @return  array 	of Filenames
     *
     * @author 	Nino Ryschawy
     */
    public static function get_storage_file_list($dir)
    {
        $files[null] = 'None';
        foreach (\Storage::files("config/$dir") as $file) {
            $name = basename($file);
            $files[$name] = $name;
        }

        return $files;
    }

    /**
     * Grep Log entries of all severity Levels above the specified one of a specific Logfile
     *
     * @author Nino Ryschawy
     *
     * @return array 		Last Log entry first
     */
    public static function get_logs($filename, $severity_level = Logger::DEBUG)
    {
        $levels = Logger::getLevels();

        foreach ($levels as $key => $value) {
            if ($severity_level <= $value) {
                break;
            }

            unset($levels[$key]);
        }

        $levels = implode('\|', array_keys($levels));
        $filename = $filename[0] == '/' ? $filename : storage_path("logs/$filename");

        exec("grep \"$levels\" $filename", $logs);

        return array_reverse($logs);
    }

    /**
     * Process datatables ajax request.
     *
     * For Performance tests and fast Copy and Paste: $start = microtime(true) and $end = microtime(true);
     * calls view_index_label() which determines how datatables are configured
     * you can find examples in every model with index page
     * Documentation is written here, because this seems like the first place to look for it
     * @param table - tablename of model
     * @param index_header - array like [$table.'.column1' , $table.'.column2', ..., 'foreigntable1.column1', 'foreigntable2.column1', ..., 'customcolumn']
     * order in index_header is important and datatables will have the column order given here
     * @param bsclass - defines a Bootstrap class for colering the Rows
     * @param header - defines whats written in Breadcrumbs Header at Edit and Create Pages
     * @param edit - array like [$table.'.column1' => 'customfunction', 'foreigntable.column' => 'customfunction', 'customcolumn' => 'customfunction']
     * customfunction will be called for every element in table.column, foreigntable.column or customcolumn
     * CAREFUL customcolumn will not be sortable or searchable - to use them anyways use the disable_sortsearch key
     * @param eager_loading array like [foreigntable1, foreigntable2, ...] - eager load foreign tables
     * @param order_by array like ['0' => 'asc'] - order table by id in ascending order, ['1' => 'desc'] - order table after first column in descending order
     * @param disable_sortsearch array like ['customcolumn' => 'false'] disables sorting & searching for the chosen column (e.g. when it is impossible) => prevent errors
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @author Christian Schramm
     *
     * NOTE: Further Datatables Documentation
     * 			https://datatables.yajrabox.com
     * 			https://yajrabox.com/docs/laravel-datatables/
     */
    public function index_datatables_ajax()
    {
        $model = static::get_model_obj();
        $dt_config = $model->view_index_label();

        $header_fields = $dt_config['index_header'];
        $edit_column_data = $dt_config['edit'] ?? [];
        $filter_column_data = $dt_config['filter'] ?? [];
        $eager_loading_tables = $dt_config['eager_loading'] ?? [];
        $additional_raw_where_clauses = $dt_config['where_clauses'] ?? [];
        $raw_columns = $dt_config['raw_columns'] ?? []; // not run through htmlentities()

        if (empty($eager_loading_tables)) { //use eager loading only when its needed
            $request_query = $model::select($dt_config['table'].'.*');

            $first_column = head($header_fields);
            if (strpos($first_column, $dt_config['table'].'.') === 0) {
                $first_column = substr($first_column, strlen($dt_config['table']) + 1);
            }
        } else {
            $request_query = $model::with($eager_loading_tables)->select($dt_config['table'].'.*'); //eager loading | select($select_column_data);
            if (starts_with(head($header_fields), $dt_config['table'])) {
                $first_column = substr(head($header_fields), strlen($dt_config['table']) + 1);
            } else {
                $first_column = head($header_fields);
            }
        }

        // apply additional where clauses
        foreach ($additional_raw_where_clauses as $where_clause) {
            $request_query = $request_query->whereRaw($where_clause);
        }

        $DT = DataTables::make($request_query)
            ->addColumn('responsive', '')
            ->addColumn('checkbox', '');

        foreach ($filter_column_data as $column => $custom_query) {
            // backward compatibility – accept strings as input, too
            if (is_string($custom_query)) {
                $custom_query = ['query' => $custom_query, 'eagers' => []];
            } elseif (! is_array($custom_query)) {
                throw new \Exception('$custom_query has to be string or array');
            }

            $DT->filterColumn($column, function ($query, $keyword) use ($custom_query) {
                foreach ($custom_query['eagers'] as $eager) {
                    $query->with($eager);
                }
                $query->whereRaw($custom_query['query'], ["%{$keyword}%"]);
            });
        }

        $DT->editColumn('checkbox', function ($model) {
            if (method_exists($model, 'set_index_delete')) {
                $model->set_index_delete();
            }

            return "<input style='simple' align='center' class='' name='ids[".$model->id."]' type='checkbox' value='1' ".
                ($model->index_delete_disabled ? 'disabled' : '').'>';
        })
            ->editColumn($first_column, function ($model) use ($first_column) {
                return '<a href="'.route(NamespaceController::get_route_name().'.edit', $model->id).'"><strong>'.
                $model->view_icon().$model[$first_column].'</strong></a>';
            });

        foreach ($edit_column_data as $column => $functionname) {
            if ($column == $first_column) {
                $DT->editColumn($column, function ($model) use ($functionname) {
                    return '<a href="'.route(NamespaceController::get_route_name().'.edit', $model->id).
                        '"><strong>'.$model->view_icon().$model->$functionname().'</strong></a>';
                });
            } else {
                $DT->editColumn($column, function ($model) use ($functionname) {
                    return $model->$functionname();
                });
            }
        }

        $DT->setRowClass(function ($model) {
            if (method_exists($model, 'get_bsclass')) {
                return $model->get_bsclass() ?: 'info';
            }

            return $model->view_index_label()['bsclass'] ?? 'info';
        });

        array_unshift($raw_columns, 'checkbox', $first_column); // add everywhere used raw columns

        return $DT->rawColumns($raw_columns)->make();
    }

    /**
     * Process autocomplete ajax request
     *
     * @return array
     *
     * @author Ole Ernst
     */
    public function autocomplete_ajax($column)
    {
        $model = static::get_model_obj();

        return $model->select($column)
            ->where($column, 'like', '%'.\Request::get('q').'%')
            ->distinct()
            ->pluck($column);
    }

    /**
     *  The official Documentation Help Menu Function
     *
     *  See: See: config/documenation.php array
     *
     *  @author Torsten Schmidt
     *
     *  @return array of ['doc' => link, 'youtube' => link, 'url' => 'link']
     */
    public function view_help()
    {
        // helper to get model name from controller context
        $a = explode('\\', strtolower(NamespaceController::get_model_name()));

        return config('documentation.'.strtolower(end($a)));
    }
}
