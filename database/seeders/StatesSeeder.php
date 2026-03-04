<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatesSeeder extends Seeder
{
    public function run(): void
    {
        $states = [
            ['key' => 'draft',              'label_ru' => 'Черновик',                   'color' => 'secondary',     'order' => 1],
            ['key' => 'submitted',          'label_ru' => 'Отправлена в HR',            'color' => 'info',          'order' => 2],
            ['key' => 'hr_reviewed',        'label_ru' => 'HR рассматривает',           'color' => 'primary',       'order' => 3],
            ['key' => 'supervisor_review',  'label_ru' => 'На подписи у руководителя',  'color' => 'warning',       'order' => 4],
            ['key' => 'approved',           'label_ru' => 'Одобрена',                   'color' => 'success',       'order' => 5],
            ['key' => 'rejected',           'label_ru' => 'Отклонена',                  'color' => 'danger',        'order' => 6],
            ['key' => 'on_hold',            'label_ru' => 'Приостановлена',             'color' => 'warning',       'order' => 7],
            ['key' => 'searching',          'label_ru' => 'Идёт поиск',                 'color' => 'primary',       'order' => 8],
            ['key' => 'closed',             'label_ru' => 'HR закрыл',                  'color' => 'secondary',     'order' => 9],
            ['key' => 'confirmed_closed',   'label_ru' => 'Закрыта (подтверждено)',      'color' => 'dark',          'order' => 10],
        ];

        foreach ($states as $state) {
            DB::table('states')->updateOrInsert(['key' => $state['key']], $state);
        }
    }
}