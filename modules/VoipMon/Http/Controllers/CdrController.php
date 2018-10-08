<?php

namespace Modules\VoipMon\Http\Controllers;

class CdrController extends \BaseController
{
    protected $index_create_allowed = false;
    protected $index_delete_allowed = false;

    public function view_form_fields($model = null)
    {
        if (! $model) {
            $model = new Cdr;
        }

        // label has to be the same like column in sql table
        return [
            ['form_type' => 'text', 'name' => 'calldate', 'description' => 'Call Start', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'callend', 'description' => 'Call End', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'duration', 'description' => 'Call Duration/s', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'mos_min_mult10', 'description' => 'min. MOS', 'help' => trans('helper.mos_min_mult10'), 'eval' => '$name/10', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'packet_loss_perc_mult1000', 'description' => 'Packet loss/%', 'eval' => '$name/1000', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'jitter_mult10', 'description' => 'Jitter/ms', 'eval' => '$name/10', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'delay_avg_mult100', 'description' => 'avg. Delay/ms', 'eval' => '$name/100', 'options' => ['readonly'], 'space' => '1'],
            /* monitoring quality indicators caller -> callee */
            ['form_type' => 'text', 'name' => 'caller', 'description' => 'Caller (-> Callee)', 'help' => trans('helper.caller'), 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'caller_domain', 'description' => '@Domain', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_mos_f1_min_mult10', 'description' => 'min. MOS 50ms', 'help' => trans('helper.a_mos_f1_min_mult10'), 'eval' => '$name/10', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_mos_f2_min_mult10', 'description' => 'min. MOS 200ms', 'help' => trans('helper.a_mos_f1_min_mult10'), 'eval' => '$name/10', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_mos_adapt_min_mult10', 'description' => 'min. MOS adaptive 500ms', 'help' => trans('helper.a_mos_adapt_min_mult10'), 'eval' => '$name/10', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_mos_f1_mult10', 'description' => 'avg. MOS 50ms', 'help' => trans('helper.a_mos_f1_mult10'), 'eval' => '$name/10', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_mos_f2_mult10', 'description' => 'avg. MOS 200ms', 'help' => trans('helper.a_mos_f2_mult10'), 'eval' => '$name/10', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_mos_adapt_mult10', 'description' => 'avg. MOS adaptive 500ms', 'help' => trans('helper.a_mos_f2_mult10'), 'eval' => '$name/10', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_received', 'description' => 'Received Packets', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_lost', 'description' => 'Lost Packets', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_packet_loss_perc_mult1000', 'description' => 'Packet loss/%', 'eval' => '$name/1000', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_delay_avg_mult100', 'description' => 'avg. Delay/ms', 'eval' => '$name/100', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_avgjitter_mult10', 'description' => 'avg. Jitter/ms', 'eval' => '$name/10', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_maxjitter', 'description' => 'max. Jitter/ms', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_sl1', 'description' => '1 loss in a row', 'help' => trans('helper.a_sl1'), 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_sl2', 'description' => '2 losses in a row', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_sl3', 'description' => '3 losses in a row', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_sl4', 'description' => '4 losses in a row', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_sl5', 'description' => '5 losses in a row', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_sl6', 'description' => '6 losses in a row', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_sl7', 'description' => '7 losses in a row', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_sl8', 'description' => '8 losses in a row', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_sl9', 'description' => '9 losses in a row', 'help' => trans('helper.a_sl9'), 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_d50', 'description' => 'PDV 50ms - 70ms', 'help' => trans('helper.a_d50'), 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_d70', 'description' => 'PDV 70ms - 90ms', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_d90', 'description' => 'PDV 90ms - 120ms', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_d120', 'description' => 'PDV 120ms - 150ms', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_d150', 'description' => 'PDV 150ms - 200ms', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_d200', 'description' => 'PDV 200ms - 300ms', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'a_d300', 'description' => 'PDV >300 ms', 'help' => trans('helper.a_d300'), 'options' => ['readonly'], 'space' => '1'],
            /* monitoring quality indicators caller -> callee */
            ['form_type' => 'text', 'name' => 'called', 'description' => 'Callee (-> Caller)', 'help' => trans('helper.called'), 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'called_domain', 'description' => '@Domain', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_mos_f1_min_mult10', 'description' => 'min. MOS 50ms', 'help' => trans('helper.a_mos_f1_min_mult10'), 'eval' => '$name/10', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_mos_f2_min_mult10', 'description' => 'min. MOS 200ms', 'help' => trans('helper.a_mos_f2_min_mult10'), 'eval' => '$name/10', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_mos_adapt_min_mult10', 'description' => 'min. MOS adaptive 500ms', 'help' => trans('helper.a_mos_adapt_min_mult10'), 'eval' => '$name/10', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_mos_f1_mult10', 'description' => 'avg. MOS 50ms', 'help' => trans('helper.a_mos_f1_mult10'),   'eval' => '$name/10', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_mos_f2_mult10', 'description' => 'avg. MOS 200ms', 'help' => trans('helper.a_mos_f2_mult10'), 'eval' => '$name/10', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_mos_adapt_mult10', 'description' => 'avg. MOS adaptive 500ms', 'help' => trans('helper.a_mos_adapt_mult10'), 'eval' => '$name/10', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_received', 'description' => 'Received Packets', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_lost', 'description' => 'Lost Packets', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_packet_loss_perc_mult1000', 'description' => 'Packet loss/%', 'eval' => '$name/1000', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_delay_avg_mult100', 'description' => 'avg. Delay/ms', 'eval' => '$name/100', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_avgjitter_mult10', 'description' => 'avg. Jitter/ms', 'eval' => '$name/10', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_maxjitter', 'description' => 'max. Jitter/ms', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_sl1', 'description' => '1 loss in a row', 'help' => trans('helper.a_sl1'), 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_sl2', 'description' => '2 losses in a row', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_sl3', 'description' => '3 losses in a row', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_sl4', 'description' => '4 losses in a row', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_sl5', 'description' => '5 losses in a row', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_sl6', 'description' => '6 losses in a row', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_sl7', 'description' => '7 losses in a row', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_sl8', 'description' => '8 losses in a row', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_sl9', 'description' => '9 losses in a row', trans('helper.a_sl9'), 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_d50', 'description' => 'PDV 50ms - 70ms', 'help' => trans('helper.a_d50'), 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_d70', 'description' => 'PDV 70ms - 90ms', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_d90', 'description' => 'PDV 90ms - 120ms', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_d120', 'description' => 'PDV 120ms - 150ms', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_d150', 'description' => 'PDV 150ms - 200ms', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_d200', 'description' => 'PDV 200ms - 300ms', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'b_d300', 'description' => 'PDV >300 ms', 'help' => trans('helper.a_d300'), 'options' => ['readonly']],
        ];
    }
}
