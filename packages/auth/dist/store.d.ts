/**
 * @purdia/auth — Auth Store
 *
 * Pinia store for auth state with encrypted token storage.
 * Framework for login/register — actual API calls are injected via config.
 */
export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    [key: string]: unknown;
}
export interface AuthTokens {
    token: string;
    refresh_token?: string;
}
export interface AuthConfig {
    /** Custom login implementation. Return tokens + user. */
    login?: (email: string, password: string) => Promise<{
        user: User;
        tokens: AuthTokens;
    }>;
    /** Custom register implementation. Return tokens + user. */
    register?: (name: string, email: string, password: string) => Promise<{
        user: User;
        tokens: AuthTokens;
    }>;
    /** Custom forgot password implementation. */
    forgotPassword?: (email: string) => Promise<{
        message: string;
    }>;
    /** Custom logout hook (e.g. call API to revoke token). */
    onLogout?: () => Promise<void> | void;
    /** Storage keys (defaults: auth_token, refresh_token, auth_user) */
    keys?: {
        token?: string;
        refreshToken?: string;
        user?: string;
    };
}
/**
 * Configure the auth module. Call once at app startup.
 */
export declare function configureAuth(config: AuthConfig): void;
export declare const useAuthStore: import("pinia").StoreDefinition<"auth", Pick<{
    user: import("vue").Ref<{
        [x: string]: unknown;
        id: number;
        name: string;
        email: string;
        avatar?: string | undefined;
    } | null, User | {
        [x: string]: unknown;
        id: number;
        name: string;
        email: string;
        avatar?: string | undefined;
    } | null>;
    token: import("vue").Ref<string | null, string | null>;
    isAuthenticated: import("vue").ComputedRef<boolean>;
    ready: import("vue").Ref<boolean, boolean>;
    init: () => Promise<void>;
    login: (email: string, password: string) => Promise<void>;
    register: (name: string, email: string, password: string) => Promise<void>;
    forgotPassword: (email: string) => Promise<{
        message: string;
    }>;
    logout: () => Promise<void>;
}, "token" | "user" | "ready">, Pick<{
    user: import("vue").Ref<{
        [x: string]: unknown;
        id: number;
        name: string;
        email: string;
        avatar?: string | undefined;
    } | null, User | {
        [x: string]: unknown;
        id: number;
        name: string;
        email: string;
        avatar?: string | undefined;
    } | null>;
    token: import("vue").Ref<string | null, string | null>;
    isAuthenticated: import("vue").ComputedRef<boolean>;
    ready: import("vue").Ref<boolean, boolean>;
    init: () => Promise<void>;
    login: (email: string, password: string) => Promise<void>;
    register: (name: string, email: string, password: string) => Promise<void>;
    forgotPassword: (email: string) => Promise<{
        message: string;
    }>;
    logout: () => Promise<void>;
}, "isAuthenticated">, Pick<{
    user: import("vue").Ref<{
        [x: string]: unknown;
        id: number;
        name: string;
        email: string;
        avatar?: string | undefined;
    } | null, User | {
        [x: string]: unknown;
        id: number;
        name: string;
        email: string;
        avatar?: string | undefined;
    } | null>;
    token: import("vue").Ref<string | null, string | null>;
    isAuthenticated: import("vue").ComputedRef<boolean>;
    ready: import("vue").Ref<boolean, boolean>;
    init: () => Promise<void>;
    login: (email: string, password: string) => Promise<void>;
    register: (name: string, email: string, password: string) => Promise<void>;
    forgotPassword: (email: string) => Promise<{
        message: string;
    }>;
    logout: () => Promise<void>;
}, "init" | "login" | "register" | "forgotPassword" | "logout">>;
