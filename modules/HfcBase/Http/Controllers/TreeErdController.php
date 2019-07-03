<?php

namespace Modules\HfcBase\Http\Controllers;

use Acme\php\ArrayHelper;
use Modules\HfcReq\Entities\NetElement;
use App\Http\Controllers\BaseViewController;
use Modules\HfcCustomer\Entities\ModemHelper;

/*
 * Tree Erd (Entity Relation Diagram) Controller
 *
 * One Object represents one SVG Graph
 *
 * @author: Torsten Schmidt
 */
class TreeErdController extends HfcBaseController
{
    protected $edit_left_md_size = 12;
    /*
     * Local tmp folder required for generating the images
     * relative (to /storage/app)
     */
    public static $path_rel = 'data/hfcbase/erd/';

    // graph id used for graphviz (svg) naming and html map
    private $graph_id;

    // SVG image size setting
    private $graph_size = '(*,*)';

    /*
     * check if $s is a valid geoposition
     */
    private function _is_valid_geopos($s)
    {
        $validator = \Validator::make(['a' => "$s"], ['a' => 'geopos']);

        return ! $validator->fails();
    }

    /*
     * Constructor: Set local vars
     */
    public function __construct()
    {
        $this->graph_id = rand(0, 1000000);

        // Note: we create several files with differnt endings *.dot, *.svg, *.map
        // the relative (to /storage/app) file path based on a random hash
        $this->file = self::$path_rel.sha1(uniqid(mt_rand(), true));

        return parent::__construct();
    }

    /**
     * Show Cluster or Network Entity Relation Diagram
     *
     * @param field: search field name in netelement table
     * @param search: the search value to look in netelement table $field
     * @return view with SVG image
     *
     * Note: called from sidebar.blade.php
     *
     * @author: Torsten Schmidt
     */
    public function show($field, $search)
    {
        // prepare search query
        $s = $field == 'all' ? 'id>2' : "$field='$search'";

        // Generate SVG file
        $file = $this->graph_generate(NetElement::whereRaw($s));
        if (! $file) {
            return \View::make('errors.generic');
        }

        // Prepare and display SVG
        $is_pos = $this->_is_valid_geopos($search);
        $gid = $this->graph_id;
        $target = $this->html_target;

        // Generate Usemap
        // Usemap is required for ERD right/left click function
        // NOTE: Do not load from url via asset() with file_get_contents().
        //       file_get_contents() does not work with port forwarding or any kind of port option.
        //       Also curl with port setting and ssl verify disabled does not work on port forwarding. Tested about 2 hours.

        // file -> html link area
        $usemap = str_replace('alt', 'onContextMenu="return getEl(this.id)" alt', \Storage::get($this->file.'.map'));
        // add Popover
        // $usemap = str_replace('title=', 'target="_blank" class="erd-popover" data-html="true" data-toggle="popover" data-container="body" data-trigger="hover" data-placement="auto right" data-content=', $usemap);

        // generate Array to manipulate string
        $usemap = explode(PHP_EOL, $usemap);

        foreach ($usemap as $element => $html) {
            if (str_contains($html, 'shape="circle"')) {
                // $usemap[$element] = explode('\n', $html);

                // Make title of circle more descriptive
                preg_match('/title="(.*?)"/', $html, $matches);

                if ($matches) {
                    $numbers = explode('\n', $matches[1]);

                    $title = [];
                    $title['numModems'] = BaseViewController::translate_label('Total Number of Modems').': '.$numbers[0];
                    $title['criticalModems'] = BaseViewController::translate_label('Number of Online').' Modems / ';
                    $title['criticalModems'] .= BaseViewController::translate_label('Number of Critical').' Modems : '.$numbers[1];
                    $title['power'] = BaseViewController::translate_label('Avg. Upstream Power: ').$numbers[2];
                    $title = implode('&#013;', $title);

                    $usemap[$element] = preg_replace('/title="(.*?)"/', "title=\"$title\"", $html);
                }

                // $usemap[$element][0] = str_replace('data-content="', 'title="'.BaseViewController::translate_label('Modem Summary').'" data-content="'.BaseViewController::translate_label('Total Number of Modems').': ', $usemap[$element][0]);
                // $usemap[$element][1] = BaseViewController::translate_label('Number of Online').' Modems / '.BaseViewController::translate_label('Number of Critical').' Modems : '.$usemap[$element][1];
                // $usemap[$element][2] = BaseViewController::translate_label('Avg. Upstream Power: ').$usemap[$element][2];
                // $usemap[$element] = implode('<br>', $usemap[$element]);
            }
        }

        $usemap = implode(PHP_EOL, $usemap);

        $view_header = 'Entity Relation Diagram';
        $route_name = 'Tree';

        $preselect_field = $field;
        $preselect_value = $search;
        $tabs = self::getTabs($field, $search);

        $file = route('HfcBase.get_file', ['type' => 'erd', 'filename' => basename($file)]);

        return \View::make('HfcBase::Tree.erd', $this->compact_prep_view(compact('route_name', 'file', 'target', 'is_pos', 'gid', 'usemap', 'preselect_field', 'view_header', 'tabs', 'view_var', 'preselect_value', 'field', 'search')));
    }

    /**
     * Shows all necessary tabs for Erd view.
     *
     * @author Roy Schneider
     * @param Modules\HfcReq\Entities\NetElement ->netelementtype, ->id
     * @return array
     */
    public static function getTabs($netelementtype, $id)
    {
        $tabs = [['name' => 'Edit', 'route' => 'NetElement.edit', 'link' => $id],
                ['name' => 'Entity Diagram', 'route' => 'TreeErd.show', 'link' => [$netelementtype, $id]],
                ['name' => 'Topography', 'route' => 'TreeTopo.show', 'link' => [$netelementtype, $id]],
                ['name' => 'Controlling', 'route' => 'NetElement.controlling_edit', 'link' => [$id, 0, 0]],
                ['name' => 'Diagrams', 'route' => 'ProvMon.diagram_edit', 'link' => $id],
                ];

        if (strtolower($netelementtype) == 'net') {
            unset($tabs[3]);
        }

        return $tabs;
    }

    /**
     * Generate the SVG and HTML Map File
     *
     * @param query: The Query to get the Tree Objects to be displayed
     * @return the path of the generated file(s) without ending
     *         this files could be included via asset ()
     *
     * @author: Torsten Schmidt
     */
    public function graph_generate($query)
    {
        //
        // INIT
        //
        $gid = $this->graph_id;

        $file = "digraph tree$gid {

	size=\"$this->graph_size\"


	{
		";

        $n = 0;
        $p1 = '';

        $netelements = $query->where('id', '>', '2')->with('netelementtype', 'parent')->orderBy('pos')->get();

        if (! $netelements->count()) {
            return;
        }

        //
        // Node
        //
        foreach ($netelements as $netelem) {
            $id = $netelem->id;
            $name = $netelem->name;
            $type = $netelem->netelementtype->name;
            $state = $netelem->get_bsclass();
            $ip = $netelem->ip;
            $p2 = $netelem->pos;
            $parent = $netelem->parent;
            $n++;

            if ($p1 != $p2) {
                $file .= "\n}\nsubgraph cluster_$n {\n style=filled;color=lightgrey;fillcolor=lightgrey;";
            }

            $url = $netelem->link ?: route('NetElement.controlling_edit', [$netelem->id, 0, 0]);

            //
            // Amplifier - what?? - all types are considered here
            //
            $color = 'green';
            if ($state == 'warning') {
                $color = 'yellow';
            }
            if ($state == 'danger') {
                $color = 'red';
            }
            if ($state == 'info') {
                $color = 'blue';
            }

            if ($type == 'Net') {
                $file .= "\n node [id = \"$id\" label = \"$name\", shape = Mdiamond, style = filled, fillcolor=lightblue, color=black URL=\"$url\", target=\"".$this->html_target.'"];';
            } elseif ($type == 'Cluster') {
                $file .= "\n node [id = \"$id\" label = \"$name\", shape = Mdiamond, style = filled, fillcolor=white, color=$color, URL=\"$url\", target=\"".$this->html_target.'"];';
            } elseif ($type == 'C') {
                $file .= "\n node [id = \"$id\" label = \"CMTS\\n$name\", shape = hexagon, style = filled, fillcolor=grey, color=$color, URL=\"$url\", target=\"".$this->html_target.'"];';
            } elseif ($type == 'DATA') {
                $file .= "\n node [id = \"$id\" label = \"$name\", shape = rectangle, style = filled, fillcolor=$color, color=darkgrey, URL=\"$url\", target=\"".$this->html_target.'"];';
            } else {
                $file .= "\n node [id = \"$id\" label = \"$name\", shape = rectangle, style = filled, fillcolor=$color, color=$color, URL=\"$url\", target=\"".$this->html_target.'"];';
            }

            $file .= " \"$id\"";

            $p1 = $p2;
        }
        $file .= "\n}";

        $file .= "\n\n node [shape = diamond];";
        //
        // Parent - Child Relations
        //
        foreach ($netelements as $netelem) {
            $_parent = $netelem->parent;
            $parent = 0;
            if ($_parent) {
                $parent = $_parent->id;
            }

            $type = $netelem->netelementtype->name;
            $tp = $netelem->tp;
            $color = 'black';
            $style = 'style=bold';
            if ($type == 'NODE') {
                $color = 'blue';
                $style = '';
            }
            if ($type == 'AMP' || $type == 'CLUSTER' || $tp == 'FOSTRA') {
                $color = 'red';
                $style = '';
            }

            if ($parent > 2 && ArrayHelper::objArraySearch($netelements, 'id', $parent)) {
                $file .= "\n  \"$parent\" -> \"$netelem->id\" [color = $color,$style]";
            }
        }

        //
        // TODO: Customer
        //
        if (\Module::collections()->has('HfcCustomer')) {
            $n = 0;
            foreach ($netelements as $netelem) {
                $idtree = $netelem->id;
                $id = $netelem->id;
                $type = $netelem->type;
                $url = \BaseRoute::get_base_url()."/Customer/netelement_id/$idtree";
                $n++;

                $state = ModemHelper::ms_state($idtree);
                if ($state != -1) {
                    $color = ModemHelper::ms_state_to_color($state);
                    $num = ModemHelper::ms_num($idtree);
                    $numa = ModemHelper::ms_num_all($idtree);
                    $cri = ModemHelper::ms_cri($idtree);
                    $avg = ModemHelper::ms_avg($idtree);

                    $file .= "\n node [label = \"$numa\\n$num/$cri\\n$avg\", shape = circle, style = filled, color=$color, URL=\"$url\", target=\"".$this->html_target.'"];';
                    $file .= " \"C$idtree\"";
                    $file .= "\n \"$id\" -> C$idtree [color = green]";
                }
            }
        }

        $date = date('l jS \of F Y H:i:s A');
        $file .= "\nlabel = \" - Entity Relation Diagram - \\n$date\";\n fontsize=20;\n\n}";

        //
        // Write Base Files *.dot for SVG translation ..
        //
        \Storage::put($this->file.'.dot', $file);
        //
        // Create SVG
        // Debug File: Add o exec: '1>$fn.log 2>&1';
        //
        $fn = \Storage::getAdapter()->applyPathPrefix($this->file);
        exec("dot -v -Tcmapx -o $fn.map -Tsvg -o $fn.svg $fn.dot");

        return str_replace(storage_path(), '', $fn);
    }
}
