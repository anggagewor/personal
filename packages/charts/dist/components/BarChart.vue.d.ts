import type { ChartData, ChartOptions } from 'chart.js';
interface Props {
    data: ChartData<'bar'>;
    options?: ChartOptions<'bar'>;
    height?: number;
}
declare const __VLS_export: import("vue").DefineComponent<Props, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<Props> & Readonly<{}>, {
    height: number;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, false, {}, any>;
declare const _default: typeof __VLS_export;
export default _default;
