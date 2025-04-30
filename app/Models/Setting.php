<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    public $timestamps = false; // Geralmente não precisamos de timestamps para settings

    protected $primaryKey = 'key'; // Usar 'key' como chave primária
    public $incrementing = false; // A chave primária não é auto-incrementável
    protected $keyType = 'string'; // A chave primária é uma string

    protected $fillable = [
        'key', // Nome da configuração (ex: 'site_name', 'cora_client_id')
        'value', // Valor da configuração
        'serialized', // Flag para indicar se o valor é JSON/Serializado
    ];

    protected $casts = [
        'serialized' => 'boolean',
    ];

    /**
     * Get the value attribute.
     * Auto-unserialize if needed.
     *
     * @param  mixed  $value
     * @return mixed
     */
    public function getValueAttribute($value)
    {
        if ($this->serialized) {
            return json_decode($value, true) ?? unserialize($value) ?? $value;
        }
        return $value;
    }

    /**
     * Set the value attribute.
     * Auto-serialize if needed.
     *
     * @param  mixed  $value
     * @return void
     */
    public function setValueAttribute($value)
    {
        if (is_array($value) || is_object($value)) {
            $this->attributes['value'] = json_encode($value);
            $this->attributes['serialized'] = true;
        } else {
            $this->attributes['value'] = $value;
            $this->attributes['serialized'] = false;
        }
    }

     /**
      * Helper para buscar uma configuração. Cachear opcionalmente.
      */
     public static function getValue(string $key, $default = null)
     {
         // Adicionar cache aqui pode ser útil para performance
         // return Cache::rememberForever('setting_'.$key, function () use ($key, $default) {
         //    return self::find($key)?->value ?? $default;
         // });

         return self::find($key)?->value ?? $default;
     }

     /**
      * Helper para definir/atualizar uma configuração.
      */
      public static function setValue(string $key, $value): bool
      {
           $setting = self::updateOrCreate(
               ['key' => $key],
               ['value' => $value] // O Mutator cuida da serialização
           );
            // Invalidar cache se estiver usando
           // Cache::forget('setting_'.$key);
           return $setting->exists;
      }
}