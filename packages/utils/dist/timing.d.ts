/**
 * Debounce a function — only execute after `ms` of inactivity.
 */
export declare function debounce<T extends (...args: unknown[]) => unknown>(fn: T, ms?: number): T;
/**
 * Throttle a function — execute at most once every `ms`.
 */
export declare function throttle<T extends (...args: unknown[]) => unknown>(fn: T, ms?: number): T;
/**
 * Promise-based sleep.
 */
export declare function sleep(ms: number): Promise<void>;
