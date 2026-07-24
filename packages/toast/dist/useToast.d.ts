import { type ToastOptions } from './store';
/**
 * Composable for showing toast notifications anywhere in the app.
 *
 * @example
 * ```ts
 * const toast = useToast()
 * toast.success('Data berhasil disimpan!')
 * toast.error('Gagal menyimpan data.')
 * ```
 */
export declare function useToast(): {
    add: (options: ToastOptions) => string;
    success: (message: string, options?: Partial<Omit<ToastOptions, "message" | "variant">>) => string;
    error: (message: string, options?: Partial<Omit<ToastOptions, "message" | "variant">>) => string;
    warning: (message: string, options?: Partial<Omit<ToastOptions, "message" | "variant">>) => string;
    info: (message: string, options?: Partial<Omit<ToastOptions, "message" | "variant">>) => string;
    dismiss: (id: string) => void;
    clear: () => void;
};
