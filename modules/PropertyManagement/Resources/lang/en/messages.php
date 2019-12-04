<?php

return [
    'groupContract' => [
        'item' => 'The contract belongs to a group contract. TV items are charged very probably via the group contract.',
        'modem' => 'This is a group contract that serves just to charge the TV fees. No modems can be added to this contract.',
        'probably' => 'The contract could belong to a group contract. Please make sure if the customers TV-charges are already charged via the group contract of the administration.',
    ],
    'realty' => [
        'apartmentRelationInfo' => 'Apartments can only be assigned to a realty if this is an apartment building and no modems are assigned. Delete all modems if you want to add apartments to this realty.',
        'modemRelationInfo' => 'Modems can only be assigned to a realty if this is a family home and doesn\'t contain any apartments or a group contract. Delete all apartments if you want to assign modems directly to this realty.',
    ],
    'tvContract' => 'No modems can be added to this contract as it is directly assigned to an apartment and therefor is only used to charge items.',
];
