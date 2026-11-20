<?php

namespace App\Http\Requests;

use App\Models\Correccion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CorreccionRequest extends FormRequest
{
    // los errores se leen con $errors->correccion porque la vista del proyecto tiene varios formularios
    protected $errorBag = 'correccion';

    public function rules()
    {
        $reglaFecha = ['required', 'date'];

        // al crear no tiene sentido una fecha límite que ya pasó
        if ($this->isMethod('post')) {
            $reglaFecha[] = 'after_or_equal:today';
        }

        return [
            'descripcion_observacion' => ['required', 'string', 'min:10', 'max:1000'],
            'fecha_limite' => $reglaFecha,
            'estado' => ['nullable', Rule::in(array_keys(Correccion::ESTADOS))],
        ];
    }

    public function messages()
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'descripcion_observacion.min' => 'La observación debe tener al menos :min caracteres.',
            'descripcion_observacion.max' => 'La observación no puede superar los :max caracteres.',
            'fecha_limite.date' => 'La fecha límite no es válida.',
            'fecha_limite.after_or_equal' => 'La fecha límite no puede ser anterior a hoy.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ];
    }

    public function attributes()
    {
        return [
            'descripcion_observacion' => 'observación',
            'fecha_limite' => 'fecha límite',
        ];
    }
}
