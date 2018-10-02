<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Social extends Model
{
    const PROVIDER_FACEBOOK = 'facebook';

    protected $table = 'staff_social';
    protected $fillable = ['staff_id', 'provider_user_id', 'provider'];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function scopeWhereProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeWhereProviderFB($query)
    {
        return $query->where('provider', self::PROVIDER_FACEBOOK);
    }

    public function scopeWhereProviderUserId($query, $id)
    {
        return $query->where('provider_user_id', $id);
    }
}
