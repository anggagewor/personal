<?php

namespace Modules\DatabaseManager\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseManagerController extends Controller
{
    /**
     * List all tables in the database.
     */
    public function tables(): JsonResponse
    {
        $tables = DB::select('SHOW TABLES');
        $dbName = DB::getDatabaseName();
        $key = "Tables_in_{$dbName}";

        $result = [];
        foreach ($tables as $table) {
            $tableName = $table->$key;
            $count = DB::table($tableName)->count();
            $result[] = [
                'name' => $tableName,
                'rows' => $count,
            ];
        }

        return response()->json(['data' => $result]);
    }

    /**
     * Get table structure (columns info).
     */
    public function structure(Request $request, string $table): JsonResponse
    {
        if (!Schema::hasTable($table)) {
            return response()->json(['message' => "Tabel '{$table}' tidak ditemukan."], 404);
        }

        $columns = DB::select("SHOW FULL COLUMNS FROM `{$table}`");

        $structure = array_map(fn ($col) => [
            'name' => $col->Field,
            'type' => $col->Type,
            'nullable' => $col->Null === 'YES',
            'key' => $col->Key,
            'default' => $col->Default,
            'extra' => $col->Extra,
            'comment' => $col->Comment,
        ], $columns);

        // Get indexes
        $indexes = DB::select("SHOW INDEX FROM `{$table}`");
        $indexMap = [];
        foreach ($indexes as $idx) {
            $indexMap[$idx->Key_name][] = [
                'column' => $idx->Column_name,
                'unique' => !$idx->Non_unique,
            ];
        }

        return response()->json([
            'data' => [
                'table' => $table,
                'columns' => $structure,
                'indexes' => $indexMap,
            ],
        ]);
    }

    /**
     * Fetch rows with pagination and filtering.
     */
    public function rows(Request $request, string $table): JsonResponse
    {
        if (!Schema::hasTable($table)) {
            return response()->json(['message' => "Tabel '{$table}' tidak ditemukan."], 404);
        }

        $perPage = (int) $request->query('per_page', 25);
        $page = (int) $request->query('page', 1);
        $sortBy = $request->query('sort_by');
        $sortDir = $request->query('sort_dir', 'asc');
        $filters = $request->query('filters', []);

        $query = DB::table($table);

        // Apply filters: [{"column": "name", "operator": "like", "value": "test"}]
        if (is_string($filters)) {
            $filters = json_decode($filters, true) ?? [];
        }

        foreach ($filters as $filter) {
            if (empty($filter['column']) || !isset($filter['value'])) {
                continue;
            }

            $column = $filter['column'];
            $operator = $filter['operator'] ?? '=';
            $value = $filter['value'];

            // Validate operator
            $allowed = ['=', '!=', '>', '<', '>=', '<=', 'like', 'not like', 'is null', 'is not null'];
            if (!in_array(strtolower($operator), $allowed)) {
                continue;
            }

            if (strtolower($operator) === 'is null') {
                $query->whereNull($column);
            } elseif (strtolower($operator) === 'is not null') {
                $query->whereNotNull($column);
            } elseif (strtolower($operator) === 'like') {
                $query->where($column, 'like', "%{$value}%");
            } else {
                $query->where($column, $operator, $value);
            }
        }

        // Sort
        if ($sortBy) {
            $query->orderBy($sortBy, strtolower($sortDir) === 'desc' ? 'desc' : 'asc');
        } else {
            // Default sort by first column (usually id)
            $columns = Schema::getColumnListing($table);
            if (!empty($columns)) {
                $query->orderBy($columns[0], 'desc');
            }
        }

        $total = $query->count();
        $rows = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();

        return response()->json([
            'data' => $rows,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    /**
     * Update a single row by primary key.
     */
    public function updateRow(Request $request, string $table): JsonResponse
    {
        if (!Schema::hasTable($table)) {
            return response()->json(['message' => "Tabel '{$table}' tidak ditemukan."], 404);
        }

        $request->validate([
            'primary_key' => ['required', 'string'],
            'primary_value' => ['required'],
            'data' => ['required', 'array'],
        ]);

        $primaryKey = $request->input('primary_key');
        $primaryValue = $request->input('primary_value');
        $data = $request->input('data');

        $affected = DB::table($table)
            ->where($primaryKey, $primaryValue)
            ->update($data);

        if ($affected === 0) {
            return response()->json(['message' => 'Row tidak ditemukan atau tidak ada perubahan.'], 404);
        }

        $row = DB::table($table)->where($primaryKey, $primaryValue)->first();

        return response()->json([
            'data' => $row,
            'message' => 'Row berhasil diperbarui.',
        ]);
    }

    /**
     * Delete a single row by primary key.
     */
    public function deleteRow(Request $request, string $table): JsonResponse
    {
        if (!Schema::hasTable($table)) {
            return response()->json(['message' => "Tabel '{$table}' tidak ditemukan."], 404);
        }

        $request->validate([
            'primary_key' => ['required', 'string'],
            'primary_value' => ['required'],
        ]);

        $primaryKey = $request->input('primary_key');
        $primaryValue = $request->input('primary_value');

        $affected = DB::table($table)->where($primaryKey, $primaryValue)->delete();

        if ($affected === 0) {
            return response()->json(['message' => 'Row tidak ditemukan.'], 404);
        }

        return response()->json(['message' => 'Row berhasil dihapus.']);
    }

    /**
     * Alter table: add column, drop column, modify column.
     */
    public function alterTable(Request $request, string $table): JsonResponse
    {
        if (!Schema::hasTable($table)) {
            return response()->json(['message' => "Tabel '{$table}' tidak ditemukan."], 404);
        }

        $request->validate([
            'action' => ['required', 'string', 'in:add_column,drop_column,modify_column'],
            'column' => ['required', 'string'],
            'type' => ['required_if:action,add_column,modify_column', 'string'],
            'nullable' => ['sometimes', 'boolean'],
            'default' => ['sometimes', 'nullable', 'string'],
            'after' => ['sometimes', 'nullable', 'string'],
        ]);

        $action = $request->input('action');
        $column = $request->input('column');
        $type = $request->input('type');
        $nullable = $request->input('nullable', true);
        $default = $request->input('default');
        $after = $request->input('after');

        try {
            switch ($action) {
                case 'add_column':
                    $sql = "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$type}";
                    if ($nullable) {
                        $sql .= ' NULL';
                    } else {
                        $sql .= ' NOT NULL';
                    }
                    if ($default !== null) {
                        $sql .= " DEFAULT " . $this->quoteDefault($default, $type);
                    }
                    if ($after) {
                        $sql .= " AFTER `{$after}`";
                    }
                    DB::statement($sql);
                    break;

                case 'drop_column':
                    DB::statement("ALTER TABLE `{$table}` DROP COLUMN `{$column}`");
                    break;

                case 'modify_column':
                    $sql = "ALTER TABLE `{$table}` MODIFY COLUMN `{$column}` {$type}";
                    if ($nullable) {
                        $sql .= ' NULL';
                    } else {
                        $sql .= ' NOT NULL';
                    }
                    if ($default !== null) {
                        $sql .= " DEFAULT " . $this->quoteDefault($default, $type);
                    }
                    DB::statement($sql);
                    break;
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengubah tabel: ' . $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => "Tabel '{$table}' berhasil diubah.",
        ]);
    }

    /**
     * Execute raw SQL query (SELECT only for safety).
     */
    public function query(Request $request): JsonResponse
    {
        $request->validate([
            'sql' => ['required', 'string', 'max:5000'],
        ]);

        $sql = trim($request->input('sql'));

        // Only allow SELECT statements for safety
        if (!preg_match('/^\s*SELECT\s/i', $sql)) {
            return response()->json([
                'message' => 'Hanya query SELECT yang diizinkan.',
            ], 422);
        }

        // Block dangerous subqueries
        if (preg_match('/\b(DROP|DELETE|UPDATE|INSERT|ALTER|TRUNCATE|CREATE)\b/i', $sql)) {
            return response()->json([
                'message' => 'Query mengandung statement berbahaya.',
            ], 422);
        }

        try {
            $results = DB::select($sql);

            return response()->json([
                'data' => $results,
                'meta' => [
                    'total' => count($results),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Query error: ' . $e->getMessage(),
            ], 422);
        }
    }

    private function quoteDefault(string $value, string $type): string
    {
        $numericTypes = ['int', 'bigint', 'smallint', 'tinyint', 'decimal', 'float', 'double'];
        $isNumeric = false;

        foreach ($numericTypes as $numType) {
            if (stripos($type, $numType) !== false) {
                $isNumeric = true;
                break;
            }
        }

        if ($isNumeric) {
            return $value;
        }

        return "'" . addslashes($value) . "'";
    }
}
