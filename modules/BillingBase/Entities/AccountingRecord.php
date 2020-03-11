<?php

namespace Modules\BillingBase\Entities;

class AccountingRecord extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'accountingrecord';

    public $observer_enabled = false;

    /**
     * Stores a Record in the database - TODO: move to controller!
     */
    public function store_item($item, $acc, $settlementrun_id)
    {
        // $count = $item->count ? $item->count : 1;

        $data = [
            'contract_id' 	=> $item->contract->id,
            'name'			=> $item->product->name,
            'product_id'	=> $item->product->id,
            'ratio'			=> $item->ratio,
            'count'			=> $item->count,
            'charge'		=> $item->charge,
            'invoice_nr'	=> $acc->invoice_nr,
            'sepaaccount_id' => $acc->id,
            'settlementrun_id' => $settlementrun_id,
        ];

        $this->create($data);
    }

    /**
     * Add a Call Data Record in the database - TODO: move to controller!
     */
    public function add_cdr($contract, $acc, $charge, $count, $settlementrun_id)
    {
        $data = [
            'contract_id' 	=> $contract->id,
            'name'			=> 'Telefone Calls',
            'product_id'	=> 0,
            'ratio'			=> 1,
            'count'			=> $count,
            'charge'		=> $charge,
            'invoice_nr'	=> $acc->invoice_nr,
            'sepaaccount_id' => $acc->id,
            'settlementrun_id' => $settlementrun_id,
        ];

        $this->create($data);
    }
}
