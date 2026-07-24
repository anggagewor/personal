import { type Ref, type ShallowRef } from 'vue';
import type { ApiError } from '@purdia/http';
export interface UseApiReturn<T> {
    /** Reactive data result */
    data: ShallowRef<T | null>;
    /** Loading state */
    loading: Ref<boolean>;
    /** Error object (null when no error) */
    error: ShallowRef<ApiError | null>;
    /**
     * Execute the request.
     * Optionally pass args that will be forwarded to the fetcher function.
     */
    execute: (...args: unknown[]) => Promise<T | null>;
    /** Reset state back to initial */
    reset: () => void;
}
export interface UseApiOptions<T> {
    /** Initial value for data (defaults to null) */
    initialData?: T | null;
    /** Execute immediately on composable creation */
    immediate?: boolean;
    /** Callback on success */
    onSuccess?: (data: T) => void;
    /** Callback on error */
    onError?: (error: ApiError) => void;
}
/**
 * Generic composable for API calls with loading/error/data state management.
 *
 * @example
 * ```ts
 * const { data: clients, loading, execute } = useApi(() => get<Client[]>('/clients'))
 * await execute()
 * ```
 */
export declare function useApi<T>(fetcher: (...args: unknown[]) => Promise<{
    data: T;
} | T>, options?: UseApiOptions<T>): UseApiReturn<T>;
