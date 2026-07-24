/**
 * Clamp a number between min and max.
 */
export declare function clamp(value: number, min: number, max: number): number;
/**
 * Generate a random integer between min (inclusive) and max (inclusive).
 */
export declare function randomInt(min: number, max: number): number;
/**
 * Generate a short unique ID (collision-safe for UI purposes).
 */
export declare function uid(prefix?: string): string;
