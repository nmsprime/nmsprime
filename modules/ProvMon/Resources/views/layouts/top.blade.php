	<?php
		/**
		 * Shows the html links of the related objects recursivly
		 * TODO: should be placed in a global concept and not on module base
		 */
		$s = "";

		$parent = $view_var;
		do
		{

			if ($parent)
			{
				// Need to be tested !
				$tmp = explode('\\',get_class($parent));
				$view = end($tmp);
				$s = "<li>".HTML::linkRoute($view.'.edit', is_array($parent->view_index_label()) ? $parent->view_index_label()['header'] : $parent->view_index_label(), $parent->id).'</li>'.$s;
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

		echo "<li class='active'>".HTML::linkRoute('Modem.index', 'Modem').'</li>'.$s;
	?>
