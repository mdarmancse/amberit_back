<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Poll extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['question', 'start_date', 'end_date', 'poll_banner', 'auth_required', 'status', 'created_by', 
            'updated_by'];
    /**
     * Attributes to include in the Audit.
     *
     * @var array
     */
    protected $auditInclude = ['question', 'start_date', 'end_date', 'poll_banner', 'auth_required', 'status'];

    public function poll_options(){
        return $this->hasMany(PollOption::class);
    }
}
