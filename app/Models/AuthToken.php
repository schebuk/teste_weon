<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthToken extends Model
{
    use HasFactory;

    protected $table = 'auth_tokens';
    protected $fillable = ['usuario_id', 'token', 'expires_at'];
    
    public static function generateToken($usuarioId)
    {
        self::where('usuario_id', $usuarioId)->delete();

        return self::create([
            'usuario_id' => $usuarioId,
            'token' => Str::random(60),
            'expires_at' => Carbon::now()->addHours(8)
        ]);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function getExpiresAtAttribute($value)
    {
        return Carbon::parse($value);
    }

    public function isValid()
    {
        return $this->expires_at && $this->expires_at->isFuture();
    }
}