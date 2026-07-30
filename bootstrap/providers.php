<?php

use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,

    // Module Service Providers
    Modules\User\Infrastructure\Providers\UserServiceProvider::class,
    Modules\Note\Infrastructure\Providers\NoteServiceProvider::class,
    Modules\Bookmark\Infrastructure\Providers\BookmarkServiceProvider::class,
    Modules\Task\Infrastructure\Providers\TaskServiceProvider::class,
    Modules\Activity\Infrastructure\Providers\ActivityServiceProvider::class,
    Modules\Calendar\Infrastructure\Providers\CalendarServiceProvider::class,
    Modules\Pomodoro\Infrastructure\Providers\PomodoroServiceProvider::class,
    Modules\Scratchpad\Infrastructure\Providers\ScratchpadServiceProvider::class,
    Modules\Habit\Infrastructure\Providers\HabitServiceProvider::class,
    Modules\Finance\Infrastructure\Providers\FinanceServiceProvider::class,
    Modules\ReadingList\Infrastructure\Providers\ReadingListServiceProvider::class,
    Modules\Journal\Infrastructure\Providers\JournalServiceProvider::class,
    Modules\Goal\Infrastructure\Providers\GoalServiceProvider::class,
    Modules\Tag\Infrastructure\Providers\TagServiceProvider::class,
    Modules\Quote\Infrastructure\Providers\QuoteServiceProvider::class,
    Modules\Wishlist\Infrastructure\Providers\WishlistServiceProvider::class,
    Modules\Trash\Infrastructure\Providers\TrashServiceProvider::class,
    Modules\Market\Infrastructure\Providers\MarketServiceProvider::class,
    Modules\Gold\Infrastructure\Providers\GoldServiceProvider::class,
    Modules\Budget\Infrastructure\Providers\BudgetServiceProvider::class,
    Modules\Vault\Infrastructure\Providers\VaultServiceProvider::class,
    Modules\GoogleDrive\Infrastructure\Providers\GoogleDriveServiceProvider::class,
    Modules\Converter\Infrastructure\Providers\ConverterServiceProvider::class,
    Modules\Accounting\Infrastructure\Providers\AccountingServiceProvider::class,
    Modules\Shared\Infrastructure\Providers\SharedServiceProvider::class,
];
