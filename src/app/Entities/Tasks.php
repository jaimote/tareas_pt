<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Tasks extends Entity
{
    protected $attributes = [
        'id'         => null,
        'title'      => null,
        'completed'  => false,
        'created_at' => null,
    ];
    protected $casts = [
        'id'         => 'integer',
        'completed'  => 'boolean',
        'created_at' => 'datetime',
    ];

}