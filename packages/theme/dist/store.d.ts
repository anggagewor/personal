/**
 * @purdia/theme — Theme Store
 *
 * Dark/light mode toggle + primary color switching.
 * Supports per-user persistence via a configurable user key.
 */
export type Theme = 'light' | 'dark';
export type PrimaryColor = 'indigo' | 'blue' | 'emerald' | 'rose' | 'amber' | 'teal' | 'violet' | 'slate';
export interface ColorOption {
    name: PrimaryColor;
    label: string;
    swatch: string;
}
export interface ThemeConfig {
    /** Default color (default: 'indigo') */
    defaultColor?: PrimaryColor;
    /** Function that returns current user identifier for per-user prefs */
    getUserKey?: () => string | null;
}
export declare function configureTheme(config: ThemeConfig): void;
export declare const colorOptions: ColorOption[];
export declare const useThemeStore: import("pinia").StoreDefinition<"theme", Pick<{
    theme: import("vue").Ref<Theme, Theme>;
    primaryColor: import("vue").Ref<PrimaryColor, PrimaryColor>;
    colorOptions: ColorOption[];
    toggle: () => void;
    setColor: (color: PrimaryColor) => void;
    loadForUser: () => void;
}, "theme" | "primaryColor" | "colorOptions">, Pick<{
    theme: import("vue").Ref<Theme, Theme>;
    primaryColor: import("vue").Ref<PrimaryColor, PrimaryColor>;
    colorOptions: ColorOption[];
    toggle: () => void;
    setColor: (color: PrimaryColor) => void;
    loadForUser: () => void;
}, never>, Pick<{
    theme: import("vue").Ref<Theme, Theme>;
    primaryColor: import("vue").Ref<PrimaryColor, PrimaryColor>;
    colorOptions: ColorOption[];
    toggle: () => void;
    setColor: (color: PrimaryColor) => void;
    loadForUser: () => void;
}, "toggle" | "setColor" | "loadForUser">>;
