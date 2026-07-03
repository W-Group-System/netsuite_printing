<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Model;

class ApVoucher extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    public function checkers()
    {
        return $this->hasOne(Name::class, 'id' , 'checked_by');
    }
    public function approvers()
    {
        return $this->hasOne(Name::class, 'id' , 'approved_by');
    }

}
