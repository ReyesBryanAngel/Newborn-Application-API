<?php

namespace App\Models;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Str;
use App\Traits\UUID;

class User extends Authenticatable implements JWTSubject
{
    use UUID, HasFactory, Notifiable;
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'otp',
        'is_verified',
        'password',
        'email_verified_at',
        'password_reset_token'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function specimenForm()
    {
        return $this->hasMany(SpecimenForm::class);
    }

    public function courierInformation()
    {
        return $this->hasMany(CourierInformation::class);
    }

    public function generatePasswordResetToken()
    {
        $password_reset_token = Str::random(60);
        $this->save();
        return $password_reset_token;
    }

    public function generateOtp()
    {
        $otp = rand(100000, 999999);
        return $otp;
    }
}
