<?php

namespace Modules\HfcCustomer\Entities;

use Illuminate\Database\Eloquent\Model;

/*
 * Modem Positioning Rule Geo Position Model
 *
 * This Model will hold all geopos for Entity Relation and
 * Topograhpy Card Bubbles. One Mpr (Modem Pos Rule) can hold
 * multiple MprGeopos, which means one Rule can have Multiple
 * Geopos. two Positions per Mpr rule means rectangle. More than
 * two Pos is a polygon. This is not implemented yet and requires
 * update to OpenLayers 3 first.
 *
 * Relations: Tree <- Mpr <- MprGeopos
 */
class MprGeopos extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'mprgeopos';

    // Add your validation rules here
    public static function rules($id = null)
    {
        return [
            'name' => 'required|string',
            'x' => 'required|numeric',
            'y' => 'required|numeric',
        ];
    }

    // Name of View
    public static function view_headline()
    {
        return 'Modem Positioning Rule Geoposition';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-map-marker"></i>';
    }

    // link title in index view
    public function view_index_label()
    {
        return 'GEOPOS'.$this->id.' : '.$this->x.', '.$this->y;
    }

    // Relation to Tree
    // NOTE: HfcBase Module is required !
    public function mpr()
    {
        return $this->belongsTo('Modules\HfcCustomer\Entities\Mpr');
    }

    /*
     * Relation Views
     */
    public function view_belongs_to()
    {
        return $this->mpr;
    }

    /**
     * BOOT:
     * - init MprGeopos Observer
     */
    public static function boot()
    {
        parent::boot();

        self::observe(new MprGeoposObserver);
    }
}

/**
 * MprGeopos Observer Class
 * Handles changes on MprGeopos, can handle:
 *
 * 'creating', 'created', 'updating', 'updated',
 * 'deleting', 'deleted', 'saving', 'saved',
 * 'restoring', 'restored',
 */
class MprGeoposObserver
{
    public function updated($mprgeopos)
    {
        if (! $mprgeopos->observer_enabled) {
            return;
        }

        \Queue::push(new \Modules\HfcCustomer\Console\MpsCommand);
    }

    public function deleted($mprgeopos)
    {
        if (! $mprgeopos->observer_enabled) {
            return;
        }

        \Queue::push(new \Modules\HfcCustomer\Console\MpsCommand);
    }
}
