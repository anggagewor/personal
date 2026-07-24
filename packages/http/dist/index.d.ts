/**
 * @purdia/http — Axios HTTP Client
 *
 * Framework-agnostic HTTP client with:
 * - Multi-service instance management
 * - Bearer token injection (encrypted via @purdia/crypto)
 * - Silent token refresh with request queuing
 * - Configurable error/unauthorized handlers
 */
import type { AxiosInstance, AxiosRequestConfig } from 'axios';
export interface ApiResponse<T = unknown> {
    data: T;
    message?: string;
    meta?: PaginationMeta;
}
export interface PaginationMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}
export interface ApiError {
    message: string;
    errors?: Record<string, string[]>;
    status: number;
}
export interface ServiceConfig {
    baseURL: string;
    timeout?: number;
    headers?: Record<string, string>;
}
export interface HttpClientConfig {
    /** Service definitions (at least 'main' and 'auth' recommended) */
    services: Record<string, ServiceConfig>;
    /** Called when token refresh fails and user must re-authenticate */
    onUnauthorized?: () => void;
    /** Called on non-422 errors (e.g. show toast) */
    onError?: (error: ApiError) => void;
    /** Locale header value (default: 'id') */
    locale?: string | (() => string);
    /** Token storage keys */
    tokenKeys?: {
        access?: string;
        refresh?: string;
    };
}
/**
 * Initialize the HTTP client. Call once at app startup.
 *
 * @example
 * ```ts
 * import { initHttp } from '@purdia/http'
 *
 * initHttp({
 *   services: {
 *     main: { baseURL: '/api' },
 *     auth: { baseURL: '/api/auth' },
 *   },
 *   onUnauthorized: () => router.push('/login'),
 *   onError: (err) => toast.error(err.message),
 * })
 * ```
 */
export declare function initHttp(userConfig: HttpClientConfig): void;
/**
 * Get (or create) an axios instance for a given service.
 */
export declare function useHttp(service?: string): AxiosInstance;
export declare function get<T = unknown>(url: string, cfg?: AxiosRequestConfig): Promise<ApiResponse<T>>;
export declare function post<T = unknown>(url: string, data?: unknown, cfg?: AxiosRequestConfig): Promise<ApiResponse<T>>;
export declare function put<T = unknown>(url: string, data?: unknown, cfg?: AxiosRequestConfig): Promise<ApiResponse<T>>;
export declare function patch<T = unknown>(url: string, data?: unknown, cfg?: AxiosRequestConfig): Promise<ApiResponse<T>>;
export declare function del<T = unknown>(url: string, cfg?: AxiosRequestConfig): Promise<ApiResponse<T>>;
/**
 * Upload file(s) with multipart/form-data.
 */
export declare function upload<T = unknown>(url: string, formData: FormData, onProgress?: (percent: number) => void): Promise<ApiResponse<T>>;
/**
 * Download file as Blob.
 */
export declare function download(url: string, cfg?: AxiosRequestConfig): Promise<import("axios").AxiosResponse<Blob, any, {}>>;
export type { AxiosInstance, AxiosRequestConfig } from 'axios';
