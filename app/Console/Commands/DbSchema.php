<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DbSchema extends Command
{
    protected $signature = 'db:schema
                            {table? : Показать только одну таблицу}
                            {--columns : Показать детали колонок}';

    protected $description = 'Вывести все таблицы и их поля';

    public function handle(): int
    {
        $only = $this->argument('table');

        $tables = DB::table('information_schema.tables')
            ->where('table_schema', 'public')
            ->where('table_type', 'BASE TABLE')
            ->orderBy('table_name')
            ->pluck('table_name');

        if ($only) {
            $tables = $tables->filter(fn($t) => $t === $only);
        }

        if ($tables->isEmpty()) {
            $this->error('Таблицы не найдены.');
            return self::FAILURE;
        }

        foreach ($tables as $table) {
            $this->newLine();
            $this->info("📋 {$table}");

            $columns = DB::table('information_schema.columns')
                ->where('table_schema', 'public')
                ->where('table_name', $table)
                ->orderBy('ordinal_position')
                ->get(['column_name', 'data_type', 'is_nullable', 'column_default']);

            if ($this->option('columns')) {
                $this->table(
                    ['Поле', 'Тип', 'Nullable', 'Default'],
                    collect($columns)->map(fn($c) => [
                        $c->column_name,
                        $c->data_type,
                        $c->is_nullable === 'YES' ? 'да' : 'нет',
                        $c->column_default ?? '—',
                    ])
                );
            } else {
                $fields = collect($columns)->pluck('column_name')->implode(', ');
                $this->line("  {$fields}");
            }

            // Индексы
            $indexes = DB::table('pg_indexes')
                ->where('schemaname', 'public')
                ->where('tablename', $table)
                ->orderBy('indexname')
                ->pluck('indexname')
                ->all();
            if (!empty($indexes)) {
                $this->line('  🔑 ' . implode(', ', $indexes));
            }
        }

        $this->newLine();
        $this->info("Всего таблиц: {$tables->count()}");

        return self::SUCCESS;
    }
}
