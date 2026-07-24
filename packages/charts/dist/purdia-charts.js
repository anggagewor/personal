import { createElementBlock as e, createVNode as t, defineComponent as n, normalizeStyle as r, openBlock as i, unref as a } from "vue";
import { Bar as o, Doughnut as s, Line as c } from "vue-chartjs";
import { ArcElement as l, BarElement as u, CategoryScale as d, Chart as f, Filler as p, Legend as m, LineElement as h, LinearScale as g, PointElement as _, Title as v, Tooltip as y } from "chart.js";
//#endregion
//#region src/components/LineChart.vue
var b = /* @__PURE__ */ n({
	__name: "LineChart",
	props: {
		data: {},
		options: {},
		height: { default: 300 }
	},
	setup(n) {
		f.register(d, g, _, h, v, y, m, p);
		let o = {
			responsive: !0,
			maintainAspectRatio: !1,
			plugins: { legend: {
				position: "bottom",
				labels: {
					usePointStyle: !0,
					padding: 20,
					font: { size: 12 }
				}
			} },
			scales: {
				x: {
					grid: { display: !1 },
					ticks: { font: { size: 11 } }
				},
				y: {
					grid: { color: "#f1f5f9" },
					ticks: { font: { size: 11 } }
				}
			},
			...n.options
		};
		return (s, l) => (i(), e("div", { style: r({ height: `${n.height}px` }) }, [t(a(c), {
			data: n.data,
			options: o
		}, null, 8, ["data"])], 4));
	}
}), x = /* @__PURE__ */ n({
	__name: "BarChart",
	props: {
		data: {},
		options: {},
		height: { default: 300 }
	},
	setup(n) {
		f.register(d, g, u, v, y, m);
		let s = {
			responsive: !0,
			maintainAspectRatio: !1,
			plugins: { legend: {
				position: "bottom",
				labels: {
					usePointStyle: !0,
					padding: 20,
					font: { size: 12 }
				}
			} },
			scales: {
				x: {
					grid: { display: !1 },
					ticks: { font: { size: 11 } }
				},
				y: {
					grid: { color: "#f1f5f9" },
					ticks: { font: { size: 11 } }
				}
			},
			...n.options
		};
		return (c, l) => (i(), e("div", { style: r({ height: `${n.height}px` }) }, [t(a(o), {
			data: n.data,
			options: s
		}, null, 8, ["data"])], 4));
	}
}), S = /* @__PURE__ */ n({
	__name: "DoughnutChart",
	props: {
		data: {},
		options: {},
		height: { default: 300 }
	},
	setup(n) {
		f.register(l, y, m);
		let o = {
			responsive: !0,
			maintainAspectRatio: !1,
			plugins: { legend: {
				position: "bottom",
				labels: {
					usePointStyle: !0,
					padding: 20,
					font: { size: 12 }
				}
			} },
			cutout: "65%",
			...n.options
		};
		return (c, l) => (i(), e("div", { style: r({ height: `${n.height}px` }) }, [t(a(s), {
			data: n.data,
			options: o
		}, null, 8, ["data"])], 4));
	}
});
//#endregion
export { x as BarChart, S as DoughnutChart, b as LineChart };
