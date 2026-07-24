import { type Ref, type ComputedRef, type ShallowRef } from 'vue';
import { type UseApiOptions } from './useApi';
import { type ApiError, type ApiResponse, type PaginationMeta } from '@purdia/http';
export interface PaginationParams {
    page: number;
    per_page: number;
    search?: string;
    sort_by?: string;
    sort_dir?: 'asc' | 'desc';
    [key: string]: unknown;
}
export interface UsePaginationReturn<T> {
    /** Reactive list data */
    data: ShallowRef<T[]>;
    /** Loading state */
    loading: Ref<boolean>;
    /** Error state */
    error: ShallowRef<ApiError | null>;
    /** Pagination metadata from server */
    meta: Ref<PaginationMeta>;
    /** Current page (v-model compatible) */
    currentPage: Ref<number>;
    /** Items per page */
    perPage: Ref<number>;
    /** Search query (debounced watch triggers refetch) */
    search: Ref<string>;
    /** Sort field */
    sortBy: Ref<string>;
    /** Sort direction */
    sortDir: Ref<'asc' | 'desc'>;
    /** Total pages (derived from meta) */
    totalPages: ComputedRef<number>;
    /** Total items (derived from meta) */
    totalItems: ComputedRef<number>;
    /** Fetch current page */
    fetch: () => Promise<void>;
    /** Go to specific page and fetch */
    goToPage: (page: number) => Promise<void>;
    /** Reset to page 1 and refetch */
    refresh: () => Promise<void>;
    /** Extra filters — set and call fetch() */
    filters: Ref<Record<string, unknown>>;
}
export interface UsePaginationOptions<T> extends Omit<UseApiOptions<T[]>, 'initialData'> {
    /** Initial page (default 1) */
    initialPage?: number;
    /** Items per page (default 10) */
    initialPerPage?: number;
    /** Watch search changes and auto-refetch with debounce (ms, default 300) */
    searchDebounce?: number;
    /** Auto-fetch on composable creation (default true) */
    immediate?: boolean;
}
/**
 * Composable for paginated API lists with search, sort, and filter support.
 *
 * @example
 * ```ts
 * const { data: clients, loading, currentPage, totalPages } = usePagination<Client>('/clients')
 * ```
 */
export declare function usePagination<T>(endpoint: string | ((params: PaginationParams) => Promise<ApiResponse<T[]>>), options?: UsePaginationOptions<T>): UsePaginationReturn<T>;
