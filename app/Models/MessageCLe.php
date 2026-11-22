<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MessageCle extends Model
{
    use HasUuids;

    protected $table = 'message_cles';

    protected $fillable = ['message_id', 'agent_id', 'cle_chiffree'];
}