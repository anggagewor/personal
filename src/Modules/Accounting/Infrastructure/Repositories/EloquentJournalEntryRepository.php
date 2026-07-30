<?php

namespace Modules\Accounting\Infrastructure\Repositories;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Domain\Contracts\JournalEntryRepositoryInterface;
use Modules\Accounting\Domain\Entities\JournalEntry;
use Modules\Accounting\Domain\ValueObjects\JournalLine;
use Modules\Accounting\Infrastructure\Models\JournalEntryModel;
use Modules\Accounting\Infrastructure\Models\JournalLineModel;

class EloquentJournalEntryRepository implements JournalEntryRepositoryInterface
{
    public function findById(int $id): ?JournalEntry
    {
        $model = JournalEntryModel::with('lines')->find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByUserPaginated(int $userId, int $perPage = 15): array
    {
        $paginator = JournalEntryModel::with('lines')
            ->where('user_id', $userId)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate($perPage);

        return [
            'data' => array_map(fn ($model) => $this->toEntity($model), $paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function getNextEntryNumber(int $userId): int
    {
        $max = JournalEntryModel::where('user_id', $userId)->max('entry_number');

        return $max ? $max + 1 : 1;
    }

    public function save(JournalEntry $entry): JournalEntry
    {
        return DB::transaction(function () use ($entry) {
            $totalDebit = array_sum(array_map(fn (JournalLine $line) => $line->debit, $entry->lines));

            $model = JournalEntryModel::updateOrCreate(
                ['id' => $entry->id],
                [
                    'user_id' => $entry->userId,
                    'entry_number' => $entry->entryNumber,
                    'date' => $entry->date->format('Y-m-d'),
                    'description' => $entry->description,
                    'total_debit' => $totalDebit,
                ]
            );

            // Delete old lines and insert new ones
            JournalLineModel::where('journal_entry_id', $model->id)->delete();

            foreach ($entry->lines as $line) {
                JournalLineModel::create([
                    'journal_entry_id' => $model->id,
                    'account_id' => $line->accountId,
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                ]);
            }

            return $this->toEntity($model->fresh()->load('lines'));
        });
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            JournalLineModel::where('journal_entry_id', $id)->delete();
            JournalEntryModel::where('id', $id)->delete();
        });
    }

    public function getLedgerForAccount(int $accountId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = DB::table('accounting_journal_lines as jl')
            ->join('accounting_journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->where('jl.account_id', $accountId)
            ->select([
                'je.date',
                'je.entry_number',
                'je.description',
                'jl.debit',
                'jl.credit',
            ]);

        if ($startDate) {
            $query->where('je.date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('je.date', '<=', $endDate);
        }

        return $query->orderBy('je.date')
            ->orderBy('je.id')
            ->get()
            ->all();
    }

    public function getAccountBalances(int $userId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = DB::table('accounting_journal_lines as jl')
            ->join('accounting_journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->where('je.user_id', $userId)
            ->groupBy('jl.account_id')
            ->select([
                'jl.account_id',
                DB::raw('SUM(jl.debit) as debit'),
                DB::raw('SUM(jl.credit) as credit'),
            ]);

        if ($startDate) {
            $query->where('je.date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('je.date', '<=', $endDate);
        }

        $results = $query->get();

        $balances = [];

        foreach ($results as $row) {
            $balances[$row->account_id] = [
                'debit' => (float) $row->debit,
                'credit' => (float) $row->credit,
            ];
        }

        return $balances;
    }

    public function deleteAllByUser(int $userId): void
    {
        DB::transaction(function () use ($userId) {
            $entryIds = JournalEntryModel::where('user_id', $userId)->pluck('id');

            if ($entryIds->isNotEmpty()) {
                JournalLineModel::whereIn('journal_entry_id', $entryIds)->delete();
                JournalEntryModel::where('user_id', $userId)->delete();
            }
        });
    }

    private function toEntity(JournalEntryModel $model): JournalEntry
    {
        $lines = $model->lines->map(fn ($lineModel) => new JournalLine(
            id: $lineModel->id,
            accountId: $lineModel->account_id,
            debit: (float) $lineModel->debit,
            credit: (float) $lineModel->credit,
        ))->all();

        return new JournalEntry(
            id: $model->id,
            userId: $model->user_id,
            entryNumber: $model->entry_number,
            date: new DateTimeImmutable($model->date->format('Y-m-d')),
            description: $model->description,
            lines: $lines,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
        );
    }
}
