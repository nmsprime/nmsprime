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
				$s = HTML::linkRoute($view.'.edit', $parent->get_view_link_title(), $parent->id).' / '.$s;
			}

			$parent = $parent->view_belongs_to();
		}
		while ($parent);

		// Show link to actual site. This depends on if we are in Modem Analyses or CPE Analyses context
		if (!isset($type))
			$s .= HTML::linkRoute('Provmon.index', 'Analyses', $view_var->id);
		elseif ($type == 'CPE')
			$s .= HTML::linkRoute('Provmon.cpe', 'CPE Analysis', $view_var->id);
		elseif ($type == 'MTA')
			$s .= HTML::linkRoute('Provmon.mta', 'MTA Analysis', $view_var->id);

		echo HTML::linkRoute('Modem.index', 'Modems').': '.$s;
	?>
