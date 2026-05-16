<?php

declare(strict_types=1);

namespace App\Models;

use Framework\Basic\BaseLaORMModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class SysMailLog extends BaseLaORMModel
{
    use SoftDeletes;

    protected $table = 'sa_system_mail';
    protected $primaryKey = 'id';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    const DELETED_AT = 'delete_time';

    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'gateway',
        'from',
        'email',
        'code',
        'content',
        'status',
        'response',
        'create_time',
        'update_time',
        'delete_time',
    ];

    protected $casts = [
        'id' => 'integer',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'delete_time' => 'datetime',
    ];
}
