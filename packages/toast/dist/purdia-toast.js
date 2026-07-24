import { Fragment as e, Teleport as t, TransitionGroup as n, computed as r, createBlock as i, createCommentVNode as a, createElementBlock as o, createElementVNode as s, createVNode as c, defineComponent as l, normalizeClass as u, normalizeStyle as d, openBlock as f, ref as p, renderList as m, resolveDynamicComponent as h, toDisplayString as g, unref as _, withCtx as v } from "vue";
import { defineStore as y } from "pinia";
import { AlertTriangle as b, CheckCircle2 as x, Info as S, X as C, XCircle as w } from "@lucide/vue";
//#region src/store.ts
var T = y("toast", () => {
	let e = p([]), t = p("top-right"), n = p(5), r = 0;
	function i(t) {
		let i = `toast-${++r}-${Date.now()}`, o = {
			id: i,
			variant: t.variant ?? "info",
			title: t.title,
			message: t.message,
			duration: t.duration ?? 5e3,
			dismissible: t.dismissible ?? !0,
			createdAt: Date.now()
		};
		return e.value.push(o), e.value.length > n.value && e.value.splice(0, e.value.length - n.value), o.duration > 0 && setTimeout(() => a(i), o.duration), i;
	}
	function a(t) {
		let n = e.value.findIndex((e) => e.id === t);
		n !== -1 && e.value.splice(n, 1);
	}
	function o() {
		e.value = [];
	}
	function s(e, t) {
		return i({
			message: e,
			variant: "success",
			...t
		});
	}
	function c(e, t) {
		return i({
			message: e,
			variant: "error",
			duration: 8e3,
			...t
		});
	}
	function l(e, t) {
		return i({
			message: e,
			variant: "warning",
			...t
		});
	}
	function u(e, t) {
		return i({
			message: e,
			variant: "info",
			...t
		});
	}
	return {
		toasts: e,
		position: t,
		maxVisible: n,
		add: i,
		dismiss: a,
		clear: o,
		success: s,
		error: c,
		warning: l,
		info: u
	};
});
//#endregion
//#region src/useToast.ts
function E() {
	let e = T();
	return {
		add: (t) => e.add(t),
		success: e.success,
		error: e.error,
		warning: e.warning,
		info: e.info,
		dismiss: e.dismiss,
		clear: e.clear
	};
}
//#endregion
//#region src/ToastContainer.vue?vue&type=script&setup=true&lang.ts
var D = { class: "flex-1 min-w-0" }, O = {
	key: 0,
	class: "text-sm font-semibold text-gray-900 dark:text-gray-100"
}, k = { class: "text-sm text-gray-600 dark:text-gray-300 break-words" }, A = ["onClick"], j = /* @__PURE__ */ l({
	__name: "ToastContainer",
	setup(l) {
		let p = T(), y = r(() => {
			let e = {
				"top-right": "top-4 right-4",
				"top-center": "top-4 left-1/2 -translate-x-1/2",
				"bottom-right": "bottom-4 right-4",
				"bottom-center": "bottom-4 left-1/2 -translate-x-1/2"
			};
			return e[p.position] ?? e["top-right"];
		}), E = {
			success: {
				icon: x,
				bg: "bg-white dark:bg-gray-800",
				border: "border-emerald-200 dark:border-emerald-700",
				iconColor: "text-emerald-500",
				progress: "bg-emerald-500"
			},
			error: {
				icon: w,
				bg: "bg-white dark:bg-gray-800",
				border: "border-red-200 dark:border-red-700",
				iconColor: "text-red-500",
				progress: "bg-red-500"
			},
			warning: {
				icon: b,
				bg: "bg-white dark:bg-gray-800",
				border: "border-amber-200 dark:border-amber-700",
				iconColor: "text-amber-500",
				progress: "bg-amber-500"
			},
			info: {
				icon: S,
				bg: "bg-white dark:bg-gray-800",
				border: "border-cyan-200 dark:border-cyan-700",
				iconColor: "text-cyan-500",
				progress: "bg-cyan-500"
			}
		};
		return (r, l) => (f(), i(t, { to: "body" }, [s("div", {
			class: u(["fixed z-[9999] flex flex-col gap-2 pointer-events-none w-full max-w-sm", y.value]),
			"aria-live": "polite",
			"aria-atomic": "true"
		}, [c(n, {
			"enter-active-class": "transition-all duration-300 ease-out",
			"leave-active-class": "transition-all duration-200 ease-in",
			"enter-from-class": "opacity-0 translate-x-4 scale-95",
			"enter-to-class": "opacity-100 translate-x-0 scale-100",
			"leave-from-class": "opacity-100 translate-x-0 scale-100",
			"leave-to-class": "opacity-0 translate-x-4 scale-95",
			"move-class": "transition-all duration-300"
		}, {
			default: v(() => [(f(!0), o(e, null, m(_(p).toasts, (e) => (f(), o("div", {
				key: e.id,
				class: u([
					"pointer-events-auto relative flex items-start gap-3 px-4 py-3 rounded-lg border shadow-lg overflow-hidden",
					E[e.variant].bg,
					E[e.variant].border
				]),
				role: "alert"
			}, [
				(f(), i(h(E[e.variant].icon), { class: u(["w-5 h-5 shrink-0 mt-0.5", E[e.variant].iconColor]) }, null, 8, ["class"])),
				s("div", D, [e.title ? (f(), o("p", O, g(e.title), 1)) : a("", !0), s("p", k, g(e.message), 1)]),
				e.dismissible ? (f(), o("button", {
					key: 0,
					class: "shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer p-0.5 rounded transition-colors",
					"aria-label": "Tutup notifikasi",
					onClick: (t) => _(p).dismiss(e.id)
				}, [c(_(C), { class: "w-4 h-4" })], 8, A)) : a("", !0),
				e.duration > 0 ? (f(), o("div", {
					key: 1,
					class: u(["absolute bottom-0 left-0 h-0.5", E[e.variant].progress]),
					style: d({ animation: `toast-progress ${e.duration}ms linear forwards` })
				}, null, 6)) : a("", !0)
			], 2))), 128))]),
			_: 1
		})], 2)]));
	}
});
//#endregion
export { j as ToastContainer, E as useToast, T as useToastStore };
