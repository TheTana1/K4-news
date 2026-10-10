<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class ShiftRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'month'          => 'required|date_format:Y-m',
            'days'           => 'required|array|min:1',
            'days.*.user_id' => 'required|integer|exists:users,id',
            'days.*.date'    => 'required|date',
            'days.*.type'    => 'required|in:off,full,half',
        ];
    }

    public function messages(): array
    {
        return [
            'month.required'    => 'Не указан месяц.',
            'month.date_format' => 'Неверный формат месяца. Ожидается ГГГГ-ММ.',

            'days.required' => 'Нет данных для сохранения.',
            'days.array'    => 'Некорректный формат данных.',
            'days.min'      => 'Нужно сохранить хотя бы одну смену.',

            'days.*.user_id.required' => 'Не указан сотрудник.',
            'days.*.user_id.integer'  => 'ID сотрудника должен быть числом.',
            'days.*.user_id.exists'   => 'Сотрудник не найден.',

            'days.*.date.required' => 'Не указана дата смены.',
            'days.*.date.date'     => 'Неверный формат даты.',

            'days.*.type.required' => 'Не указан тип смены.',
            'days.*.type.in'       => 'Тип смены может быть только: выходной, полный день, неполный день.',
        ];
    }

    public function after():array
    {
        return [
            function ($validator) {
                if ($validator->errors()->any()) {
                    Log::warning('Валидация не прошла', [
                        'errors' => $validator->errors()->toArray()
                    ]);
                } else {
                    Log::info('Валидация новости прошла успешно');
                }
            }
        ];
    }
}
