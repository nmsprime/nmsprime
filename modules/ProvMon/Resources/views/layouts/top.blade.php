    <?php
        /**
         * Shows the html links of the related objects recursivly
         * TODO: should be placed in a global concept and not on module base
         */
        $s = '';

        $parent = $view_var;
        $classname = explode('\\',get_class($parent));
        $classname = end($classname);

        while ($parent)
        {
            $tmp   = explode('\\',get_class($parent));
            $view  = end($tmp);
            $icon  = $parent->view_icon();
            $label = is_array($ret = $parent->view_index_label()) ? $ret['header'] : $ret;

            $s = "<li>".HTML::decode(HTML::linkRoute($view.'.edit', $icon.$label, $parent->id)).'</li>'.$s;

            $parent = $parent->view_belongs_to();

            if ($parent instanceof \Illuminate\Support\Collection) {
                $parent = $parent->first();
            }
        }

        // Show link to actual site. This depends on if we are in Modem Analyses or CPE Analyses context
        if (!isset($type))
        {
            $route_ext = $classname == 'Modem' ? 'index' : 'netgw';
            $s .= "<li class='nav-tabs'>".HTML::linkRoute("ProvMon.$route_ext", 'Analyses', $view_var->id).'</li>';
        }
        elseif ($type == 'CPE')
            $s .= "<li class='nav-tabs'>".HTML::linkRoute('ProvMon.cpe', 'CPE Analysis', $view_var->id).'</li>';
        elseif ($type == 'MTA')
            $s .= "<li class='nav-tabs'>".HTML::linkRoute('ProvMon.mta', 'MTA Analysis', $view_var->id).'</li>';

        echo "<li class='active'><a href='".route("$classname.index")."'><i class='fa fa-hdd-o'></i>$classname</a></li>".$s;
    ?>
