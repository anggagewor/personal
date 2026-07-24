export type ToastVariant = 'success' | 'error' | 'warning' | 'info';
export type ToastPosition = 'top-right' | 'top-center' | 'bottom-right' | 'bottom-center';
export interface Toast {
    id: string;
    variant: ToastVariant;
    title?: string;
    message: string;
    duration: number;
    dismissible: boolean;
    createdAt: number;
}
export interface ToastOptions {
    title?: string;
    message: string;
    variant?: ToastVariant;
    /** Duration in ms (0 = persistent, default 5000) */
    duration?: number;
    dismissible?: boolean;
}
export declare const useToastStore: import("pinia").StoreDefinition<"toast", Pick<{
    toasts: import("vue").Ref<{
        id: string;
        variant: ToastVariant;
        title?: string | undefined;
        message: string;
        duration: number;
        dismissible: boolean;
        createdAt: number;
    }[], Toast[] | {
        id: string;
        variant: ToastVariant;
        title?: string | undefined;
        message: string;
        duration: number;
        dismissible: boolean;
        createdAt: number;
    }[]>;
    position: import("vue").Ref<ToastPosition, ToastPosition>;
    maxVisible: import("vue").Ref<number, number>;
    add: (options: ToastOptions) => string;
    dismiss: (id: string) => void;
    clear: () => void;
    success: (message: string, options?: Partial<Omit<ToastOptions, "message" | "variant">>) => string;
    error: (message: string, options?: Partial<Omit<ToastOptions, "message" | "variant">>) => string;
    warning: (message: string, options?: Partial<Omit<ToastOptions, "message" | "variant">>) => string;
    info: (message: string, options?: Partial<Omit<ToastOptions, "message" | "variant">>) => string;
}, "toasts" | "position" | "maxVisible">, Pick<{
    toasts: import("vue").Ref<{
        id: string;
        variant: ToastVariant;
        title?: string | undefined;
        message: string;
        duration: number;
        dismissible: boolean;
        createdAt: number;
    }[], Toast[] | {
        id: string;
        variant: ToastVariant;
        title?: string | undefined;
        message: string;
        duration: number;
        dismissible: boolean;
        createdAt: number;
    }[]>;
    position: import("vue").Ref<ToastPosition, ToastPosition>;
    maxVisible: import("vue").Ref<number, number>;
    add: (options: ToastOptions) => string;
    dismiss: (id: string) => void;
    clear: () => void;
    success: (message: string, options?: Partial<Omit<ToastOptions, "message" | "variant">>) => string;
    error: (message: string, options?: Partial<Omit<ToastOptions, "message" | "variant">>) => string;
    warning: (message: string, options?: Partial<Omit<ToastOptions, "message" | "variant">>) => string;
    info: (message: string, options?: Partial<Omit<ToastOptions, "message" | "variant">>) => string;
}, never>, Pick<{
    toasts: import("vue").Ref<{
        id: string;
        variant: ToastVariant;
        title?: string | undefined;
        message: string;
        duration: number;
        dismissible: boolean;
        createdAt: number;
    }[], Toast[] | {
        id: string;
        variant: ToastVariant;
        title?: string | undefined;
        message: string;
        duration: number;
        dismissible: boolean;
        createdAt: number;
    }[]>;
    position: import("vue").Ref<ToastPosition, ToastPosition>;
    maxVisible: import("vue").Ref<number, number>;
    add: (options: ToastOptions) => string;
    dismiss: (id: string) => void;
    clear: () => void;
    success: (message: string, options?: Partial<Omit<ToastOptions, "message" | "variant">>) => string;
    error: (message: string, options?: Partial<Omit<ToastOptions, "message" | "variant">>) => string;
    warning: (message: string, options?: Partial<Omit<ToastOptions, "message" | "variant">>) => string;
    info: (message: string, options?: Partial<Omit<ToastOptions, "message" | "variant">>) => string;
}, "success" | "error" | "warning" | "info" | "add" | "dismiss" | "clear">>;
