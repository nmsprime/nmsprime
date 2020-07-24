<?php

namespace Modules\HfcBase\Contracts;

interface ImpairedContract
{
    public function scopeForTroubleDashboard($query);

    public function toIcingaWeb();

    public function toControlling();

    public function toTicket();

    public function affectedModemsCount($netelements);

    public function hasAdditionalData();
}
