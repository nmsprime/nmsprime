	<?php
		/**
		 * Shows the html links of the related objects recursivly
		 * TODO: should be placed in a global concept and not on module base
		 */
		$s = '';

		$parent = $view_var;
		do
		{

			if ($parent)
			{
				// Need to be tested !
				$tmp = explode('\\',get_class($parent));
				$view = end($tmp);
				$lbl = is_array($parent->view_index_label()) ? $parent->view_index_label()['header'] : $parent->view_index_label();
				$s = "<li><a href='".$view.".edit'>".$parent->view_icon().$lbl."</a></li>".$s;
			}


			$parent = $parent->view_belongs_to();
		}
		while ($parent);

		// Show link to actual site. This depends on if we are in Modem Analyses or CPE Analyses context
		if (!isset($type))
			$s .= "<li class='nav-tabs'>".HTML::linkRoute('Provmon.index', 'Analyses', $view_var->id).'</li>';
		elseif ($type == 'CPE')
			$s .= "<li class='nav-tabs'>".HTML::linkRoute('Provmon.cpe', 'CPE Analysis', $view_var->id).'</li>';
		elseif ($type == 'MTA')
			$s .= "<li class='nav-tabs'>".HTML::linkRoute('Provmon.mta', 'MTA Analysis', $view_var->id).'</li>';

		echo "<li class='active'><a href='Modem.index'><i class='fa fa-hdd-o'></i>Modem</a></li>".$s;
	?>
