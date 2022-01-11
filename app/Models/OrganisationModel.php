<?php

namespace App\Models;

use App\Scopes\ActiveOrganisationScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * App\Models\OrganisationModel
 *
 * @property int organisation_id
 * @property string uuid
 */

class OrganisationModel extends Model
{
    use SoftDeletes, HasFactory;

    protected $guarded = [];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    protected static function booted()
    {
        static::addGlobalScope(new ActiveOrganisationScope);

        self::creating(function (OrganisationModel $model) {
            $model->organisation_id = $model->organisation_id ?? auth()->user()->activeOrganisation->id;
            $model->uuid = Str::uuid()->__toString();
        });
    }
}
