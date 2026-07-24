/**
 * @purdia/auth — Route Guard
 *
 * Vue Router navigation guard that handles:
 * - Redirecting unauthenticated users to login
 * - Redirecting authenticated users away from guest-only pages
 */
import type { Router } from 'vue-router';
export interface AuthGuardOptions {
    /** Route name for login page (default: 'login') */
    loginRoute?: string;
    /** Route name for authenticated landing (default: 'dashboard') */
    homeRoute?: string;
    /** Meta key that marks a route as guest-only (default: 'guest') */
    guestMeta?: string;
    /** Meta key that marks a route as public (default: 'public') */
    publicMeta?: string;
}
/**
 * Install auth guard on a Vue Router instance.
 *
 * @example
 * ```ts
 * import { createAuthGuard } from '@purdia/auth'
 *
 * createAuthGuard(router, {
 *   loginRoute: 'login',
 *   homeRoute: 'dashboard',
 * })
 * ```
 */
export declare function createAuthGuard(router: Router, options?: AuthGuardOptions): void;
