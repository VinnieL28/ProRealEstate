<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'team_id',
        'dark_mode_pref',
        'first_name',
        'middle_name',
        'last_name',
        'phone_no',
        'telephone_no',
        'webwork_email',
        'employee_status',
        'emergency_contact_name',
        'emergency_contact_relation',
        'added_by',
        'updated_by',
        'first_login_otp',
        'first_login_otp_expires_at',
        'is_first_time_login',
        'status',
        'street',
        'suburb',
        'state',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'dark_mode_pref' => 'boolean',
        'first_login_otp_expires_at' => 'datetime',
        'is_first_time_login' => 'boolean',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)->withTimestamps()->withPivot('role_override');
    }

    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to_id');
    }

    public function callLogs(): HasMany
    {
        return $this->hasMany(CallLog::class);
    }

    public function smsLogs(): HasMany
    {
        return $this->hasMany(SmsLog::class);
    }

    public function hasRole(array|string $roles): bool
    {
        $roles = (array) $roles;
        return in_array($this->role, $roles, true);
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->hasRole($roles);
    }
}
