<?php

namespace Modules\HfcBase\Http\Controllers;

use Acme\php\ArrayHelper;
use Modules\HfcReq\Entities\NetElement;
use Modules\HfcBase\Entities\IcingaObject;
use App\Http\Controllers\BaseViewController;

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

    private $colors = [
        'success' => 'green',
        'info'    => 'blue',
        'warning' => 'yellow',
        'danger'  => 'red',
    ];

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
        $operator = '=';

        if ($field == 'all' || ($field == 'id' && $search == 2)) {
            $field = 'id';
            $operator = '>';
            $search = 2;

            if (! NetElement::where($field, $operator, $search)->count()) {
                return \View::make('errors.generic', [
                    'error' => 422,
                    'message' => trans('messages.no_Netelements'),
                ]);
            }
        }

        // Currently parent_id is only requested for Tap-Ports and it's Tap
        if ($field == 'parent_id') {
            // Only show Tap and it's Tap-Ports when top element is a Tap
            $netelements = NetElement::where('id', $search)
                ->orWhere(function ($query) use ($field, $search) {
                    $query
                    ->where('netelementtype_id', 9)
                    ->where($field, $search);
                });
        } else {
            $netelements = NetElement::withActiveModems($field, $operator, $search)
                ->where('netelementtype_id', '!=', 9)
                ->with('modemsUpstreamAvg');
        }

        $netelements->with('netelementtype:id,name');

        if (IcingaObject::db_exists()) {
            $netelements->with('icingaobject.hoststatus');
        }

        $file = $this->generateSVG($netelements->get());

        if (! $file) {
            return \View::make('errors.generic', [
                'error' => 422,
                'message' => trans('messages.no_ERD_File'),
            ]);
        }

        $view_header = 'Entity Relation Diagram';

        $gid = $this->graph_id;
        $usemap = $this->generateUsemap();
        $tabs = self::getTabs($field, $search);
        $is_pos = $this->_is_valid_geopos($search);
        $file = route('HfcBase.get_file', ['type' => 'erd', 'filename' => basename($file)]);

        return \View::make('HfcBase::Tree.erd', $this->compact_prep_view(
            compact('field', 'search', 'tabs', 'file', 'is_pos', 'gid', 'usemap', 'view_header')
        ));
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

        if (in_array(strtolower($netelementtype), ['net', 'all', 'id', 'parent_id'])) {
            unset($tabs[3]);
        }

        if (in_array(strtolower($netelementtype), ['parent_id'])) {
            unset($tabs[4]);
        }

        return $tabs;
    }

    /**
     * Usemap is required for ERD right/left click function
     * NOTE: Do not load from url via asset() with file_get_contents().
     * file_get_contents() does not work with port forwarding or any kind of port option.
     * Also curl with port setting and ssl verify disabled does not work on port forwarding.
     *
     * @return string
     */
    protected function generateUsemap()
    {
        // file -> html link area
        $usemap = str_replace('alt', 'onContextMenu="return getEl(this.id)" alt', \Storage::get($this->file.'.map'));
        // add Popover
        // $usemap = str_replace('title=', 'target="_blank" class="erd-popover" data-html="true" data-toggle="popover" data-container="body" data-trigger="hover" data-placement="auto right" data-content=', $usemap);

        // generate Array to manipulate string
        $usemap = explode(PHP_EOL, $usemap);

        foreach ($usemap as $element => $html) {
            if (str_contains($html, 'shape="circle"')) {

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
            }
        }

        return implode(PHP_EOL, $usemap);
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
    public function generateSVG($netelements)
    {
        if (! $netelements->first()) {
            return false;
        }

        $n = 0;
        $p1 = '';
        $file = "digraph tree{$this->graph_id} { size=\"{$this->graph_size}\" {";

        // Nodes
        foreach ($netelements as $netelem) {
            $n++;
            $id = $netelem->id;
            $p2 = $netelem->pos;
            $name = $netelem->name;
            $color = $this->colors[$netelem->get_bsclass()];
            $type = $netelem->netelementtype->name;

            $url = '';
            if ($netelem->link) {
                $url = $netelem->link;
            } elseif ($netelem->netelementtype_id == 8) {
                $url = route('TreeErd.show', ['parent_id', $netelem->id]);
            } elseif ($netelem->netelementtype_id == 9) {
                $url = route('NetElement.tapControlling', $netelem->id);
            } else {
                $url = route('NetElement.controlling_edit', [$netelem->id, 0, 0]);
            }

            if ($p1 != $p2) {
                $file .= "\n}\nsubgraph cluster_$n {\n style=filled;color=lightgrey;fillcolor=lightgrey;";
            }

            if ($type == 'Net') {
                $file .= "\n node [id = \"$id\" label = \"$name\", shape = Mdiamond, style = filled, fillcolor=lightblue, color=black URL=\"$url\", target=\"\"];";
            } elseif ($type == 'Cluster') {
                $file .= "\n node [id = \"$id\" label = \"$name\", shape = Mdiamond, style = filled, fillcolor=white, color=\"$color\", URL=\"$url\", target=\"\"];";
            } elseif ($type == 'C') {
                $file .= "\n node [id = \"$id\" label = \"NetGw\\n$name\", shape = hexagon, style = filled, fillcolor=grey, color=\"$color\", URL=\"$url\", target=\"\"];";
            } elseif ($type == 'DATA') {
                $file .= "\n node [id = \"$id\" label = \"$name\", shape = rectangle, style = filled, fillcolor=\"$color\", color=darkgrey, URL=\"$url\", target=\"\"];";
            } else {
                $file .= "\n node [id = \"$id\" label = \"$name\", shape = rectangle, style = filled, fillcolor=\"$color\", color=\"$color\", URL=\"$url\", target=\"\"];";
            }

            $file .= " \"$id\"";

            $p1 = $p2;
        }
        $file .= "\n}\n\n node [shape = diamond];";

        //parent-Child-Relations
        foreach ($netelements as $netelem) {
            $color = 'black';
            $style = 'style=bold';
            $tp = $netelem->tp;
            $type = $netelem->netelementtype->name;

            if ($type == 'NODE') {
                $color = 'blue';
                $style = '';
            }
            if ($type == 'AMP' || $type == 'CLUSTER' || $tp == 'FOSTRA') {
                $color = 'red';
                $style = '';
            }

            if ($netelem->parent_id > 2 && ArrayHelper::objArraySearch($netelements, 'id', $netelem->parent_id)) {
                $file .= "\n  \"$netelem->parent_id\" -> \"$netelem->id\" [color = $color,$style]";
            }
        }

        //
        // TODO: Customer
        //
        if (\Module::collections()->has('HfcCustomer')) {
            foreach ($netelements as $netelem) {
                $idtree = $netelem->id;
                $id = $netelem->id;
                $type = $netelem->type;
                $url = \BaseRoute::get_base_url()."/Customer/netelement_id/$idtree";

                $num = $netelem->modems_online_count;
                $numa = $netelem->modems_count;
                $cri = $netelem->modems_critical_count;
                $avg = $netelem->modemsUsPwrAvg;
                $modemStateAnalysis = new \Modules\HfcCustomer\Entities\Utility\ModemStateAnalysis($num, $numa, $avg);

                if ($modemStateAnalysis->get()) {
                    $color = $modemStateAnalysis->toColor();
                    $file .= "\n node [label = \"$numa\\n$num/$cri\\n$avg\", shape = circle, style = filled, color=$color, URL=\"$url\", target=\"\"];";
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
