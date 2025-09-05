<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usuario extends Model
{
    use HasFactory;

    protected $table = 'usuarios';
    protected $fillable = ['nome', 'email', 'senha'];
    protected $hidden = ['senha'];

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'usuario_id');
    }

    public function authTokens(): HasMany
    {
        return $this->hasMany(AuthToken::class, 'usuario_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($usuario) {
            $usuario->senha = password_hash($usuario->senha, PASSWORD_DEFAULT);
        });

        static::updating(function ($usuario) {
            if ($usuario->isDirty('senha')) {
                $usuario->senha = password_hash($usuario->senha, PASSWORD_DEFAULT);
            }
        });
    }
}