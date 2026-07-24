/**
 * Format number as Indonesian Rupiah currency.
 */
export declare function formatCurrency(value: number, options?: {
    locale?: string;
    currency?: string;
}): string;
/**
 * Format number with thousand separators.
 */
export declare function formatNumber(value: number, locale?: string): string;
/**
 * Format a Date or ISO string to localized date string.
 */
export declare function formatDate(value: Date | string, options?: Intl.DateTimeFormatOptions, locale?: string): string;
/**
 * Format a Date or ISO string as relative time (e.g. "3 jam yang lalu").
 */
export declare function formatRelativeTime(value: Date | string, locale?: string): string;
