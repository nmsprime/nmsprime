<?php

namespace Modules\ProvVoipEnvia\Entities;


// Model not found? execute composer dump-autoload in nmsprime root dir
class ProvVoipEnviaHelpers {


	/**
	 * Build the table HTML for given data.
	 *
	 * @param $data array containing the rows of the table (first is used as header)
	 *					each row has to be given as an array holding the cols of this row
	 *
	 * @return raw HTML string for direct use
	 *
	 * @author Patrick Reichel
	 */
	protected static function _get_user_action_table($data) {

		$replace_func = function($data) {
			$placeholders = array(
				'placeholder_yes' => '<span class="text-success">&#10004;</span>',
				'placeholder_no' => '<span class="text-danger">&#10008;</span>',
				'placeholder_unset' => '–',
			);
			foreach ($placeholders as $placeholder => $replacement) {
				$data = str_replace($placeholder, $replacement, $data);
			}
			return $data;
		};

		$td_style = "padding-left: 5px; padding-right: 5px; vertical-align: top;";
		$th_style = $td_style." padding-bottom: 4px; padding-top: 4px;";

		$ret = "";

		// the tables head
		$ret = '<table class="table-hover">';
		$ret .= '<thead><tr>';
		foreach (array_shift($data) as $col) {
			$ret .= '<th style="'.$th_style.'">'.$col.'</th>';
		}
		$ret .= '</tr></thead>';

		$ret .= '<tbody>';

		// the tables body (row by row)
		foreach ($data as $row) {
			$ret .= '<tr>';
			foreach ($row as $col) {
				$ret .= '<td style="'.$td_style.'">';
				$ret .= $replace_func($col);
				$ret .= '</td>';
			}
			$ret .= '</tr>';
		}

		$ret .= '</tbody>';

		$ret .= '</table>';
		return $ret;
	}


	/**
	 * Create table containing information about the contract
	 *
	 * @author Patrick Reichel
	 */
	public static function get_user_action_information_contract($contract) {

		$data = array();

		$head = array(
			'Number',
			'Address',
			'Contract start',
			'Contract end',
			'Internet access?',
		);
		array_push($data, $head);

		$row= array();

		if (is_null($contract->deleted_at)) {
			array_push($row, '<a href="'.\URL::route("Contract.edit", array("Contract" => $contract->id)).'">'.$contract->number.'</a>');
		}
		else {
			array_push($row, "<s>$contract->number</s>");
		}

		$tmp_address = "";
		$tmp_address .= (boolval($contract->company) ? $contract->company.",<br>" : "");
		$tmp_address .= (boolval($contract->firstname) ? $contract->firstname." " : "");
		$tmp_address .= (boolval($contract->lastname) ? $contract->lastname : "");
		$tmp_address .= ((boolval($contract->firstname) || boolval($contract->lastname)) ? ",<br>" : "");
		$tmp_address .= $contract->street.(boolval($contract->house_number) ? "&nbsp;".$contract->house_number : "").",<br>";
		$tmp_address .= $contract->city;
		$tmp_address .= (boolval($contract->district) ? " OT ".$contract->district : "");
		array_push($row, $tmp_address);

		array_push($row, boolval($contract->contract_start) ? $contract->contract_start : 'placeholder_unset');
		array_push($row, boolval($contract->contract_end) ? $contract->contract_end : 'placeholder_unset');
		array_push($row, ($contract->network_access > 0 ? 'placeholder_yes' : 'placeholder_no'));

		array_push($data, $row);

		$ret = static::_get_user_action_table($data);

		return $ret;
	}


	/**
	 * Create table containing information about related items
	 *
	 * @author Patrick Reichel
	 */
	public static function get_user_action_information_items($items) {

		$data = array();

		$head = array(
			'Product',
			'Type',
			'Valid from',
			'Fix?',
			'Valid to',
			'Fix?',
		);
		array_push($data, $head);

		foreach ($items as $item) {

			if (!in_array(\Str::lower($item->product->type), ['internet', 'voip'])) {
				continue;
			}

			$row = array();

			array_push($row, '<a href="'.\URL::route("Item.edit", array("Item" => $item->id)).'">'.$item->product->name.'</a>');
			array_push($row, $item->product->type);
			array_push($row, (boolval($item->valid_from) ? $item->valid_from : 'placeholder_unset'));
			if ($item->valid_from_fixed > 0) {
				array_push($row, 'placeholder_yes');
			}
			elseif ($item->valid_from) {
				array_push($row, 'placeholder_no');
			}
			else {
				array_push($row, '');
			}
			array_push($row, (boolval($item->valid_to) ? $item->valid_to : "placeholder_unset"));
			if ($item->valid_to_fixed > 0) {
				array_push($row, 'placeholder_yes');
			}
			elseif ($item->valid_to) {
				array_push($row, 'placeholder_no');
			}
			else {
				array_push($row, '');
			}

			array_push($data, $row);
		}

		$ret = static::_get_user_action_table($data);
		return $ret;
	}


	/**
	 * Create table containing information about the modem
	 *
	 * @author Patrick Reichel
	 */
	public static function get_user_action_information_modem($modem) {

		$data = array();

		$head = array(
			'MAC address',
			'Hostname',
			'Installation address',
			'Configfile',
			'QoS',
			'Network access?',
		);
		array_push($data, $head);

		$row = array();

		if (is_null($modem->deleted_at)) {
			array_push($row, '<a href="'.\URL::route("Modem.edit", array("Modem" => $modem->id)).'">'.$modem->mac.'</a>');
		}
		else {
			array_push($row, "<s>$modem->mac</s>");
		}
		array_push($row, $modem->hostname);

		$tmp_address = "";
		$tmp_address .= (boolval($modem->company) ? $modem->company.",<br>" : "");
		$tmp_address .= (boolval($modem->firstname) ? $modem->firstname." " : "");
		$tmp_address .= (boolval($modem->lastname) ? $modem->lastname : "");
		$tmp_address .= ((boolval($modem->firstname) || boolval($modem->lastname)) ? ",<br>" : "");
		$tmp_address .= $modem->street.(boolval($modem->house_number) ? "&nbsp;".$modem->house_number : "").",<br>";
		$tmp_address .= $modem->city;
		$tmp_address .= (boolval($modem->district) ? " OT ".$modem->district : "");
		array_push($row, $tmp_address);

		if ($modem->configfile) {
			array_push($row, $modem->configfile->name);
		}
		else {
			array_push($row, '–');
		}

		if ($modem->qos) {
			array_push($row, $modem->qos->name);
		}
		else {
			array_push($row, '–');
		}
		array_push($row, ($modem->network_access > 0 ? 'placeholder_yes': 'placeholder_no'));

		array_push($data, $row);

		$ret = static::_get_user_action_table($data);
		return $ret;
	}


	/**
	 * Create table containing information about related phonenumbers
	 *
	 * @author Patrick Reichel
	 */
	public static function get_user_action_information_phonenumbers($model, $phonenumbers) {

		$data = array();

		$head = array(
			'Phonenumber',
			'Activation target',
			'Activation confirmed',
			'Deactivation target',
			'Deactivation confirmed',
			'Active?',
		);
		array_push($data, $head);

		$closely_related = array();
		$distantly_related = array();

		// helper to wrap weak related informations
		$wrap = function ($content, $direct_related) {
			if (!$direct_related) {
				$content = "<i>$content</i>";
			}
			return $content;
		};

		foreach ($phonenumbers as $phonenumber) {

			$direct_related = $model->phonenumbers->contains($phonenumber)? : false;

			$row = array();
			$phonenumbermanagement = $phonenumber->phonenumbermanagement;

			if (!is_null($phonenumbermanagement)) {
				$tmp = '<a href="'.\URL::route("PhonenumberManagement.edit", array("phonenumbermanagement" => $phonenumbermanagement->id)).'">'.$phonenumber->prefix_number.'/'.$phonenumber->number.'</a>';
			}
			else {
				$tmp = '<a href="'.\URL::route("Phonenumber.edit", array("phonenumber" => $phonenumber->id)).'">'.$phonenumber->prefix_number.'/'.$phonenumber->number.'</a>';
			}

			array_push($row, $wrap($tmp, $direct_related));

			if (!is_null($phonenumbermanagement)) {
				array_push($row, $wrap((boolval($phonenumbermanagement->activation_date) ? $phonenumbermanagement->activation_date : "placeholder_unset"), $direct_related));
				array_push($row, $wrap((boolval($phonenumbermanagement->external_activation_date) ? $phonenumbermanagement->external_activation_date : "placeholder_unset"), $direct_related));
				array_push($row, $wrap((boolval($phonenumbermanagement->deactivation_date) ? $phonenumbermanagement->deactivation_date : "placeholder_unset"), $direct_related));
				array_push($row, $wrap((boolval($phonenumbermanagement->external_deactivation_date) ? $phonenumbermanagement->external_deactivation_date : "placeholder_unset"), $direct_related));
			}
			else {
				array_push($row, $wrap('mgmt n/a', $direct_related));
				array_push($row, $wrap('mgmt n/a', $direct_related));
				array_push($row, $wrap('mgmt n/a', $direct_related));
				array_push($row, $wrap('mgmt n/a', $direct_related));
			}

			array_push($row, $wrap(($phonenumber->active > 0 ? 'placeholder_yes': 'placeholder_no'), $direct_related));

			if ($direct_related) {
				array_push($closely_related, $row);
			}
			else {
				array_push($distantly_related, $row);
			}
		}

		$relation_placeholder = array();

		// create the placeholder if there are closely and distantly related phonenumbers
		if ($closely_related && $distantly_related) {
			// for every col in last row: add a col to our placeholder
			$placeholder_row = array();
			foreach ($row as $_) {
				/* array_push($placeholder_row, "<div style='font-size: 8px;'>&nbsp;</div>"); */
				array_push($placeholder_row, "<hr style='margin: 4px 0'>");
			}
			array_push($relation_placeholder, $placeholder_row);
		}

		$data = array_merge($data, $closely_related, $relation_placeholder, $distantly_related);

		$ret = static::_get_user_action_table($data);
		return $ret;
	}


	/**
	 * Create table containing information about related enviacontract
	 *
	 * @author Patrick Reichel
	 */
	public static function get_user_action_information_enviacontract($enviacontract) {

		$data = array();

		$head = array(
			'envia TEL contract ID',
			'State',
			'Start date',
			'End date',
		);
		array_push($data, $head);

		$row = [
			'<a href="'.\URL::route("EnviaContract.edit", array("EnviaContract" => $enviacontract->id)).'">'.$enviacontract->envia_contract_reference.'</a>',
			$enviacontract->state ? $enviacontract->state : '–',
			$enviacontract->start_date ? $enviacontract->start_date : '–',
			$enviacontract->end_date ? $enviacontract->end_date : '–',
		];
		array_push($data, $row);

		$ret = static::_get_user_action_table($data);
		return $ret;
	}


	/**
	 * Create table containing information about related envia orders
	 *
	 * @author Patrick Reichel
	 */
	public static function get_user_action_information_enviaorders($enviaorders) {

		$data = array();

		$head = array(
			'Order ID',
			'Ordertype',
			'Orderdate',
			'Orderstatus',
			'Method',
		);
		array_push($data, $head);

		foreach ($enviaorders as $enviaorder) {
			$row = [
				'<a href="'.\URL::route("EnviaOrder.edit", array("EnviaOrder" => $enviaorder->id)).'">'.$enviaorder->orderid.'</a>',
				$enviaorder->ordertype ? $enviaorder->ordertype : '–',
				$enviaorder->orderdate ? $enviaorder->orderdate : '–',
				$enviaorder->orderstatus ? $enviaorder->orderstatus : 'n/a',
				$enviaorder->method ? $enviaorder->method : '–',
			];
			array_push($data, $row);
		}

		$ret = static::_get_user_action_table($data);
		return $ret;
	}

}
