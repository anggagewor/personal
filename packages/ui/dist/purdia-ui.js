import { Fragment as e, Teleport as t, Transition as n, computed as r, createBlock as i, createCommentVNode as a, createElementBlock as o, createElementVNode as s, createTextVNode as c, createVNode as l, defineComponent as u, inject as d, nextTick as f, normalizeClass as p, normalizeStyle as m, onBeforeUnmount as h, onMounted as g, onUnmounted as _, openBlock as v, provide as y, ref as b, renderList as x, renderSlot as S, resolveComponent as C, resolveDynamicComponent as w, toDisplayString as T, unref as E, vModelText as D, vShow as O, watch as k, withCtx as A, withDirectives as j, withKeys as M, withModifiers as N } from "vue";
import { AlertCircle as P, AlignCenter as F, AlignJustify as I, AlignLeft as L, AlignRight as R, Archive as z, ArrowDown as B, ArrowUp as V, Bold as H, Calendar as U, Check as W, ChevronDown as G, ChevronLeft as K, ChevronRight as q, ChevronsLeft as ee, ChevronsRight as J, Clock as Y, Code as X, CodeXml as te, Dot as ne, File as re, FileText as ie, Film as ae, Heading1 as oe, Heading2 as se, Heading3 as Z, Highlighter as ce, Image as le, ImageIcon as ue, Italic as de, Link as fe, List as pe, ListOrdered as me, ListTodo as he, Loader2 as ge, Minus as _e, Music as ve, Palette as ye, Quote as be, Redo2 as xe, RemoveFormatting as Se, Search as Ce, Slash as we, Strikethrough as Te, Underline as Ee, Undo2 as De, Upload as Oe, X as Q } from "@lucide/vue";
import { EditorContent as ke, useEditor as Ae } from "@tiptap/vue-3";
import je from "@tiptap/starter-kit";
import Me from "@tiptap/extension-placeholder";
import Ne from "@tiptap/extension-text-align";
import Pe from "@tiptap/extension-underline";
import Fe from "@tiptap/extension-link";
import Ie from "@tiptap/extension-image";
import { TextStyle as Le } from "@tiptap/extension-text-style";
import Re from "@tiptap/extension-color";
import ze from "@tiptap/extension-highlight";
import Be from "@tiptap/extension-task-list";
import Ve from "@tiptap/extension-task-item";
//#region src/components/BaseAccordion.vue?vue&type=script&setup=true&lang.ts
var He = [
	"disabled",
	"aria-expanded",
	"onClick"
], Ue = {
	key: 0,
	class: "px-4 pb-3 text-sm text-gray-600 dark:text-gray-400"
}, We = /* @__PURE__ */ u({
	__name: "BaseAccordion",
	props: {
		items: {},
		multiple: {
			type: Boolean,
			default: !1
		},
		variant: { default: "default" }
	},
	setup(t) {
		let r = t, i = b(/* @__PURE__ */ new Set());
		function u(e) {
			r.items[e]?.disabled || (i.value.has(e) ? i.value.delete(e) : (r.multiple || i.value.clear(), i.value.add(e)), i.value = new Set(i.value));
		}
		function d(e) {
			return i.value.has(e);
		}
		let f = {
			default: {
				wrapper: "divide-y divide-gray-200 dark:divide-gray-700",
				item: ""
			},
			bordered: {
				wrapper: "border border-gray-200 rounded-lg divide-y divide-gray-200 dark:border-gray-700 dark:divide-gray-700",
				item: ""
			},
			separated: {
				wrapper: "space-y-2",
				item: "border border-gray-200 rounded-lg dark:border-gray-700"
			}
		};
		return (r, i) => (v(), o("div", { class: p(f[t.variant].wrapper) }, [(v(!0), o(e, null, x(t.items, (e, m) => (v(), o("div", {
			key: m,
			class: p(f[t.variant].item)
		}, [s("button", {
			type: "button",
			class: p(["flex w-full items-center justify-between px-4 py-3 text-left text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700/50", {
				"opacity-50 cursor-not-allowed": e.disabled,
				"cursor-pointer": !e.disabled
			}]),
			disabled: e.disabled,
			"aria-expanded": d(m),
			onClick: (e) => u(m)
		}, [s("span", null, T(e.title), 1), (v(), o("svg", {
			class: p(["h-4 w-4 shrink-0 text-gray-400 transition-transform duration-200 dark:text-gray-500", { "rotate-180": d(m) }]),
			xmlns: "http://www.w3.org/2000/svg",
			viewBox: "0 0 20 20",
			fill: "currentColor"
		}, [...i[0] ||= [s("path", {
			"fill-rule": "evenodd",
			d: "M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z",
			"clip-rule": "evenodd"
		}, null, -1)]], 2))], 10, He), l(n, {
			"enter-active-class": "transition-all duration-200 ease-out overflow-hidden",
			"leave-active-class": "transition-all duration-200 ease-in overflow-hidden",
			"enter-from-class": "max-h-0 opacity-0",
			"enter-to-class": "max-h-96 opacity-100",
			"leave-from-class": "max-h-96 opacity-100",
			"leave-to-class": "max-h-0 opacity-0"
		}, {
			default: A(() => [d(m) ? (v(), o("div", Ue, [S(r.$slots, "default", {
				item: e,
				isOpen: d(m)
			}, () => [c(T(e.content), 1)])])) : a("", !0)]),
			_: 2
		}, 1024)], 2))), 128))], 2));
	}
}), Ge = { class: "flex-1" }, Ke = {
	key: 0,
	class: "block mb-0.5"
}, qe = /* @__PURE__ */ u({
	__name: "BaseAlert",
	props: {
		variant: { default: "info" },
		title: {},
		icon: {},
		dismissible: {
			type: Boolean,
			default: !1
		}
	},
	emits: ["dismiss"],
	setup(e, { emit: t }) {
		let n = e, c = t, l = {
			info: "bg-cyan-50 border-cyan-200 text-cyan-800 dark:bg-cyan-900/20 dark:border-cyan-800 dark:text-cyan-300",
			success: "bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-300",
			warning: "bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-900/20 dark:border-amber-800 dark:text-amber-300",
			danger: "bg-red-50 border-red-200 text-red-800 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300"
		}, u = r(() => ["flex items-start gap-3 px-4 py-3 rounded-lg border text-sm", l[n.variant]]);
		return (t, n) => (v(), o("div", {
			class: p(u.value),
			role: "alert"
		}, [
			e.icon ? (v(), i(w(e.icon), {
				key: 0,
				class: "w-5 h-5 shrink-0 mt-0.5"
			})) : a("", !0),
			s("div", Ge, [e.title ? (v(), o("strong", Ke, T(e.title), 1)) : a("", !0), S(t.$slots, "default")]),
			e.dismissible ? (v(), o("button", {
				key: 1,
				class: "text-xl leading-none opacity-60 hover:opacity-100 cursor-pointer",
				onClick: n[0] ||= (e) => c("dismiss"),
				"aria-label": "Dismiss"
			}, " × ")) : a("", !0)
		], 2));
	}
}), Je = ["src", "alt"], Ye = /* @__PURE__ */ u({
	__name: "BaseAvatar",
	props: {
		src: {},
		alt: { default: "" },
		name: {},
		size: { default: "md" },
		variant: { default: "circle" }
	},
	setup(e) {
		let t = e;
		function n(e) {
			return e.split(" ").map((e) => e[0]).join("").toUpperCase().slice(0, 2);
		}
		function i(e) {
			let t = [
				"#6366f1",
				"#10b981",
				"#f59e0b",
				"#ef4444",
				"#06b6d4",
				"#8b5cf6",
				"#ec4899"
			], n = 0;
			for (let t = 0; t < e.length; t++) n = e.charCodeAt(t) + ((n << 5) - n);
			return t[Math.abs(n) % t.length];
		}
		let a = t.name ? n(t.name) : "", s = t.name ? i(t.name) : "#94a3b8", c = {
			xs: "w-6 h-6 text-[0.5rem]",
			sm: "w-8 h-8 text-[0.625rem]",
			md: "w-10 h-10 text-xs",
			lg: "w-12 h-12 text-sm",
			xl: "w-16 h-16 text-lg"
		}, l = {
			circle: "rounded-full",
			rounded: "rounded-lg",
			square: "rounded-sm"
		}, u = r(() => [
			"inline-flex items-center justify-center overflow-hidden shrink-0",
			c[t.size],
			l[t.variant]
		]);
		return (t, n) => (v(), o("div", { class: p(u.value) }, [e.src ? (v(), o("img", {
			key: 0,
			src: e.src,
			alt: e.alt || e.name || "",
			class: "w-full h-full object-cover"
		}, null, 8, Je)) : (v(), o("span", {
			key: 1,
			class: "w-full h-full flex items-center justify-center text-white font-semibold",
			style: m({ background: E(s) })
		}, T(E(a)), 5))], 2));
	}
}), Xe = {
	class: "flex items-center",
	role: "group",
	"aria-label": "Avatar group"
}, Ze = ["aria-label"], Qe = /* @__PURE__ */ u({
	__name: "BaseAvatarGroup",
	props: {
		items: {},
		max: { default: 5 },
		size: { default: "md" },
		overlap: { default: "md" }
	},
	setup(t) {
		let n = t, i = r(() => n.items.slice(0, n.max)), s = r(() => Math.max(n.items.length - n.max, 0)), c = {
			sm: "-ml-1",
			md: "-ml-2",
			lg: "-ml-3"
		}, u = {
			xs: "xs",
			sm: "sm",
			md: "md",
			lg: "lg"
		}, d = {
			xs: "w-6 h-6 text-[0.5rem]",
			sm: "w-8 h-8 text-[0.625rem]",
			md: "w-10 h-10 text-xs",
			lg: "w-12 h-12 text-sm"
		};
		return (n, r) => (v(), o("div", Xe, [(v(!0), o(e, null, x(i.value, (e, n) => (v(), o("div", {
			key: e.name,
			class: p(["relative ring-2 ring-white dark:ring-gray-900 rounded-full", n > 0 ? c[t.overlap] : ""])
		}, [l(Ye, {
			name: e.name,
			src: e.src,
			size: u[t.size],
			variant: e.variant || "circle"
		}, null, 8, [
			"name",
			"src",
			"size",
			"variant"
		])], 2))), 128)), s.value > 0 ? (v(), o("div", {
			key: 0,
			class: p(["relative flex items-center justify-center rounded-full bg-gray-200 text-gray-600 font-medium ring-2 ring-white dark:ring-gray-900 dark:bg-gray-700 dark:text-gray-300", [c[t.overlap], d[t.size]]]),
			"aria-label": `${s.value} more`
		}, " +" + T(s.value), 11, Ze)) : a("", !0)]));
	}
}), $e = {
	key: 0,
	class: "w-1.5 h-1.5 rounded-full bg-current"
}, et = /* @__PURE__ */ u({
	__name: "BaseBadge",
	props: {
		variant: { default: "primary" },
		size: { default: "md" },
		dot: {
			type: Boolean,
			default: !1
		},
		pill: {
			type: Boolean,
			default: !0
		}
	},
	setup(e) {
		let t = e, n = {
			primary: "bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-300",
			secondary: "bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300",
			success: "bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-300",
			warning: "bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-300",
			danger: "bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-300",
			info: "bg-cyan-50 text-cyan-600 dark:bg-cyan-900/30 dark:text-cyan-300"
		}, i = {
			sm: "px-2 py-0.5 text-[0.6875rem]",
			md: "px-2.5 py-0.5 text-xs",
			lg: "px-3 py-1 text-sm"
		}, s = r(() => [
			"inline-flex items-center gap-1 font-medium",
			n[t.variant],
			i[t.size],
			t.pill ? "rounded-full" : "rounded"
		]);
		return (t, n) => (v(), o("span", { class: p(s.value) }, [e.dot ? (v(), o("span", $e)) : a("", !0), S(t.$slots, "default")], 2));
	}
}), tt = { "aria-label": "Breadcrumb" }, nt = ["aria-current"], rt = /* @__PURE__ */ u({
	__name: "BaseBreadcrumb",
	props: {
		items: {},
		separator: { default: "chevron" },
		size: { default: "md" }
	},
	setup(t) {
		let n = t, c = r(() => ({
			chevron: q,
			slash: we,
			dot: ne
		})[n.separator]), l = r(() => ({
			sm: {
				text: "text-xs",
				icon: "w-3 h-3",
				gap: "gap-1"
			},
			md: {
				text: "text-sm",
				icon: "w-4 h-4",
				gap: "gap-1.5"
			},
			lg: {
				text: "text-base",
				icon: "w-5 h-5",
				gap: "gap-2"
			}
		})[n.size]);
		return (n, r) => {
			let u = C("router-link");
			return v(), o("nav", tt, [s("ol", { class: p(["flex flex-wrap items-center", l.value.gap]) }, [(v(!0), o(e, null, x(t.items, (e, n) => (v(), o("li", {
				key: n,
				class: p(["flex items-center", l.value.gap])
			}, [n > 0 ? (v(), i(w(c.value), {
				key: 0,
				class: p([l.value.icon, "text-gray-400 shrink-0"]),
				"aria-hidden": "true"
			}, null, 8, ["class"])) : a("", !0), e.to && n < t.items.length - 1 ? (v(), i(u, {
				key: 1,
				to: e.to,
				class: p(["flex items-center gap-1 text-gray-500 hover:text-primary-600 transition-colors", l.value.text])
			}, {
				default: A(() => [e.icon ? (v(), i(w(e.icon), {
					key: 0,
					class: p([l.value.icon, "shrink-0"])
				}, null, 8, ["class"])) : a("", !0), s("span", null, T(e.label), 1)]),
				_: 2
			}, 1032, ["to", "class"])) : (v(), o("span", {
				key: 2,
				class: p(["flex items-center gap-1 font-medium text-gray-900", l.value.text]),
				"aria-current": n === t.items.length - 1 ? "page" : void 0
			}, [e.icon ? (v(), i(w(e.icon), {
				key: 0,
				class: p([l.value.icon, "shrink-0"])
			}, null, 8, ["class"])) : a("", !0), s("span", null, T(e.label), 1)], 10, nt))], 2))), 128))], 2)]);
		};
	}
}), it = ["disabled"], at = {
	key: 0,
	class: "w-4 h-4 border-2 border-current border-r-transparent rounded-full animate-spin"
}, ot = /* @__PURE__ */ u({
	__name: "BaseButton",
	props: {
		variant: { default: "primary" },
		size: { default: "md" },
		disabled: {
			type: Boolean,
			default: !1
		},
		loading: {
			type: Boolean,
			default: !1
		},
		icon: {},
		iconRight: {},
		block: {
			type: Boolean,
			default: !1
		}
	},
	emits: ["click"],
	setup(e) {
		let t = e, n = {
			primary: "bg-primary-500 text-white hover:bg-primary-600 focus:ring-primary-200",
			secondary: "bg-gray-500 text-white hover:bg-gray-600 focus:ring-gray-200",
			success: "bg-emerald-500 text-white hover:bg-emerald-600 focus:ring-emerald-200",
			warning: "bg-amber-500 text-white hover:bg-amber-600 focus:ring-amber-200",
			danger: "bg-red-500 text-white hover:bg-red-600 focus:ring-red-200",
			ghost: "bg-transparent text-gray-700 hover:bg-gray-100 focus:ring-gray-200 dark:text-gray-300 dark:hover:bg-gray-700",
			outline: "bg-transparent text-primary-500 border border-primary-500 hover:bg-primary-50 focus:ring-primary-200 dark:hover:bg-primary-900/20"
		}, c = {
			sm: "px-3 py-1.5 text-xs",
			md: "px-4 py-2 text-sm",
			lg: "px-5 py-2.5 text-base"
		}, l = r(() => [
			"inline-flex items-center justify-center gap-2 font-medium rounded-md cursor-pointer transition-all duration-150 select-none focus:outline-none focus:ring-2",
			n[t.variant],
			c[t.size],
			t.block ? "w-full" : "",
			t.disabled || t.loading ? "opacity-50 cursor-not-allowed" : ""
		]);
		return (t, n) => (v(), o("button", {
			class: p(l.value),
			disabled: e.disabled || e.loading,
			onClick: n[0] ||= (e) => t.$emit("click", e)
		}, [
			e.loading ? (v(), o("span", at)) : a("", !0),
			e.icon && !e.loading ? (v(), i(w(e.icon), {
				key: 1,
				class: "w-4 h-4 shrink-0"
			})) : a("", !0),
			s("span", { class: p({ "opacity-70": e.loading }) }, [S(t.$slots, "default")], 2),
			e.iconRight ? (v(), i(w(e.iconRight), {
				key: 2,
				class: "w-4 h-4 shrink-0"
			})) : a("", !0)
		], 10, it));
	}
}), st = { class: "flex items-center justify-between mb-4" }, ct = { class: "text-sm font-semibold text-gray-900 dark:text-gray-100" }, lt = { class: "grid grid-cols-7 mb-1" }, ut = { class: "grid grid-cols-7" }, dt = ["onClick"], ft = {
	key: 0,
	class: "flex gap-0.5 mt-0.5"
}, pt = ["title", "onClick"], mt = /* @__PURE__ */ u({
	__name: "BaseCalendar",
	props: {
		modelValue: {},
		events: { default: () => [] },
		variant: { default: "default" }
	},
	emits: [
		"update:modelValue",
		"event-click",
		"date-click"
	],
	setup(t, { emit: n }) {
		let i = t, c = n, l = /* @__PURE__ */ new Date(), u = b(i.modelValue ? i.modelValue.getMonth() : l.getMonth()), d = b(i.modelValue ? i.modelValue.getFullYear() : l.getFullYear()), f = [
			"Sun",
			"Mon",
			"Tue",
			"Wed",
			"Thu",
			"Fri",
			"Sat"
		], m = r(() => new Date(d.value, u.value).toLocaleDateString("en-US", {
			month: "long",
			year: "numeric"
		})), h = r(() => {
			let e = new Date(d.value, u.value, 1), t = new Date(d.value, u.value + 1, 0), n = e.getDay(), r = [], i = new Date(d.value, u.value, 0).getDate();
			for (let e = n - 1; e >= 0; e--) {
				let t = new Date(d.value, u.value - 1, i - e);
				r.push(g(t, !1));
			}
			for (let e = 1; e <= t.getDate(); e++) {
				let t = new Date(d.value, u.value, e);
				r.push(g(t, !0));
			}
			let a = 42 - r.length;
			for (let e = 1; e <= a; e++) {
				let t = new Date(d.value, u.value + 1, e);
				r.push(g(t, !1));
			}
			return r;
		});
		function g(e, t) {
			return {
				date: e,
				day: e.getDate(),
				isCurrentMonth: t,
				isToday: _(e, l),
				isSelected: i.modelValue ? _(e, i.modelValue) : !1,
				events: y(e)
			};
		}
		function _(e, t) {
			return e.getFullYear() === t.getFullYear() && e.getMonth() === t.getMonth() && e.getDate() === t.getDate();
		}
		function y(e) {
			return i.events.filter((t) => _(new Date(t.date), e));
		}
		function S() {
			u.value === 0 ? (u.value = 11, d.value--) : u.value--;
		}
		function C() {
			u.value === 11 ? (u.value = 0, d.value++) : u.value++;
		}
		function w(e) {
			c("update:modelValue", e.date), c("date-click", e.date);
		}
		function E(e, t) {
			t.stopPropagation(), c("event-click", e);
		}
		let D = {
			primary: "bg-primary-500",
			success: "bg-emerald-500",
			warning: "bg-amber-500",
			danger: "bg-red-500",
			info: "bg-cyan-500"
		};
		return (n, r) => (v(), o("div", { class: p(["select-none", {
			"w-full": t.variant === "default",
			"w-64": t.variant === "compact"
		}]) }, [
			s("div", st, [
				s("button", {
					type: "button",
					class: "p-1.5 rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700 cursor-pointer dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200",
					"aria-label": "Previous month",
					onClick: S
				}, [...r[0] ||= [s("svg", {
					class: "w-4 h-4",
					xmlns: "http://www.w3.org/2000/svg",
					viewBox: "0 0 20 20",
					fill: "currentColor"
				}, [s("path", {
					"fill-rule": "evenodd",
					d: "M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z",
					"clip-rule": "evenodd"
				})], -1)]]),
				s("span", ct, T(m.value), 1),
				s("button", {
					type: "button",
					class: "p-1.5 rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700 cursor-pointer dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200",
					"aria-label": "Next month",
					onClick: C
				}, [...r[1] ||= [s("svg", {
					class: "w-4 h-4",
					xmlns: "http://www.w3.org/2000/svg",
					viewBox: "0 0 20 20",
					fill: "currentColor"
				}, [s("path", {
					"fill-rule": "evenodd",
					d: "M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z",
					"clip-rule": "evenodd"
				})], -1)]])
			]),
			s("div", lt, [(v(), o(e, null, x(f, (e) => s("div", {
				key: e,
				class: "text-center text-xs font-medium text-gray-500 dark:text-gray-400 py-1"
			}, T(t.variant === "compact" ? e.charAt(0) : e), 1)), 64))]),
			s("div", ut, [(v(!0), o(e, null, x(h.value, (n, r) => (v(), o("div", {
				key: r,
				class: p(["relative flex flex-col items-center justify-start py-1 cursor-pointer", {
					"min-h-[2.5rem]": t.variant === "default",
					"min-h-[2rem]": t.variant === "compact"
				}]),
				onClick: (e) => w(n)
			}, [s("span", { class: p(["flex items-center justify-center rounded-full text-xs transition-colors", [t.variant === "compact" ? "w-6 h-6" : "w-7 h-7", {
				"bg-primary-500 text-white font-semibold": n.isSelected,
				"ring-2 ring-primary-300 dark:ring-primary-600": n.isToday && !n.isSelected,
				"text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700": n.isCurrentMonth && !n.isSelected,
				"text-gray-400 dark:text-gray-600": !n.isCurrentMonth
			}]]) }, T(n.day), 3), n.events.length && t.variant === "default" ? (v(), o("div", ft, [(v(!0), o(e, null, x(n.events.slice(0, 3), (e) => (v(), o("span", {
				key: e.id,
				class: p(["w-1 h-1 rounded-full", D[e.variant || "primary"]]),
				title: e.title,
				onClick: (t) => E(e, t)
			}, null, 10, pt))), 128))])) : a("", !0)], 10, dt))), 128))])
		], 2));
	}
}), ht = {
	key: 0,
	class: "px-6 py-4 border-b border-gray-100 dark:border-gray-700"
}, gt = {
	key: 1,
	class: "px-6 py-4 border-t border-gray-100 dark:border-gray-700"
}, _t = /* @__PURE__ */ u({
	__name: "BaseCard",
	props: {
		variant: { default: "default" },
		padding: {
			type: Boolean,
			default: !0
		},
		flush: {
			type: Boolean,
			default: !1
		},
		hoverable: {
			type: Boolean,
			default: !1
		}
	},
	setup(e) {
		let t = e, n = {
			default: "bg-white border border-gray-200 shadow-sm dark:bg-gray-800 dark:border-gray-700",
			bordered: "bg-white border border-gray-200 dark:bg-gray-800 dark:border-gray-700",
			elevated: "bg-white shadow-lg dark:bg-gray-800",
			flat: "bg-gray-50 dark:bg-gray-800/50"
		}, i = r(() => [
			"rounded-xl overflow-hidden transition-all duration-200",
			n[t.variant],
			t.hoverable ? "hover:shadow-md hover:-translate-y-0.5" : ""
		]), c = r(() => t.flush ? "" : t.padding ? "p-6" : "");
		return (e, t) => (v(), o("div", { class: p(i.value) }, [
			e.$slots.header ? (v(), o("div", ht, [S(e.$slots, "header")])) : a("", !0),
			s("div", { class: p(c.value) }, [S(e.$slots, "default")], 2),
			e.$slots.footer ? (v(), o("div", gt, [S(e.$slots, "footer")])) : a("", !0)
		], 2));
	}
}), vt = [
	"checked",
	"disabled",
	"indeterminate"
], yt = {
	key: 0,
	class: "w-3 h-3 text-white",
	xmlns: "http://www.w3.org/2000/svg",
	viewBox: "0 0 20 20",
	fill: "currentColor"
}, bt = {
	key: 1,
	class: "w-3 h-3 text-white",
	xmlns: "http://www.w3.org/2000/svg",
	viewBox: "0 0 20 20",
	fill: "currentColor"
}, xt = {
	key: 0,
	class: "text-sm text-gray-700 select-none dark:text-gray-300"
}, St = /* @__PURE__ */ u({
	__name: "BaseCheckbox",
	props: {
		modelValue: { type: [Boolean, Array] },
		value: {},
		label: {},
		disabled: {
			type: Boolean,
			default: !1
		},
		indeterminate: {
			type: Boolean,
			default: !1
		},
		variant: { default: "primary" }
	},
	emits: ["update:modelValue"],
	setup(e, { emit: t }) {
		let n = e, i = t, c = r(() => Array.isArray(n.modelValue) ? n.modelValue.includes(n.value) : n.modelValue);
		function l() {
			if (Array.isArray(n.modelValue)) {
				let e = [...n.modelValue], t = e.indexOf(n.value);
				t > -1 ? e.splice(t, 1) : e.push(n.value), i("update:modelValue", e);
			} else i("update:modelValue", !n.modelValue);
		}
		let u = {
			primary: "border-primary-500 bg-primary-500",
			success: "border-emerald-500 bg-emerald-500",
			warning: "border-amber-500 bg-amber-500",
			danger: "border-red-500 bg-red-500"
		}, d = r(() => ["relative w-4 h-4 shrink-0 rounded border-2 transition-colors duration-150 flex items-center justify-center", c.value || n.indeterminate ? u[n.variant] : "border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-800"]);
		return (t, n) => (v(), o("label", { class: p(["inline-flex items-center gap-2", e.disabled ? "opacity-50 cursor-not-allowed" : "cursor-pointer"]) }, [
			s("input", {
				type: "checkbox",
				checked: c.value,
				disabled: e.disabled,
				indeterminate: e.indeterminate,
				class: "sr-only",
				onChange: l
			}, null, 40, vt),
			s("span", { class: p(d.value) }, [c.value && !e.indeterminate ? (v(), o("svg", yt, [...n[0] ||= [s("path", {
				"fill-rule": "evenodd",
				d: "M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z",
				"clip-rule": "evenodd"
			}, null, -1)]])) : a("", !0), e.indeterminate ? (v(), o("svg", bt, [...n[1] ||= [s("path", {
				"fill-rule": "evenodd",
				d: "M4 10a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1z",
				"clip-rule": "evenodd"
			}, null, -1)]])) : a("", !0)], 2),
			e.label ? (v(), o("span", xt, T(e.label), 1)) : a("", !0)
		], 2));
	}
}), Ct = [
	"title",
	"aria-label",
	"aria-checked",
	"onClick"
], wt = {
	key: 0,
	class: "w-4 h-4 mx-auto text-white drop-shadow-sm",
	xmlns: "http://www.w3.org/2000/svg",
	viewBox: "0 0 20 20",
	fill: "currentColor"
}, Tt = /* @__PURE__ */ u({
	__name: "BaseColorPicker",
	props: {
		modelValue: {},
		colors: {},
		size: { default: "md" },
		columns: { default: 6 }
	},
	emits: ["update:modelValue"],
	setup(t, { emit: n }) {
		let r = n, i = {
			sm: "w-6 h-6",
			md: "w-8 h-8",
			lg: "w-10 h-10"
		}, c = {
			sm: "ring-2 ring-offset-1",
			md: "ring-2 ring-offset-2",
			lg: "ring-3 ring-offset-2"
		};
		return (n, l) => (v(), o("div", {
			class: "grid gap-2",
			style: m({ gridTemplateColumns: `repeat(${t.columns}, minmax(0, 1fr))` }),
			role: "radiogroup",
			"aria-label": "Color picker"
		}, [(v(!0), o(e, null, x(t.colors, (e) => (v(), o("button", {
			key: e.value,
			type: "button",
			class: p(["rounded-full transition-transform duration-150 hover:scale-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500", [i[t.size], t.modelValue === e.value ? `${c[t.size]} ring-primary-500 dark:ring-offset-gray-900` : ""]]),
			style: m({ backgroundColor: e.value }),
			title: e.label || e.name,
			"aria-label": e.label || e.name,
			"aria-checked": t.modelValue === e.value,
			role: "radio",
			onClick: (t) => r("update:modelValue", e.value)
		}, [t.modelValue === e.value ? (v(), o("svg", wt, [...l[0] ||= [s("path", {
			"fill-rule": "evenodd",
			d: "M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z",
			"clip-rule": "evenodd"
		}, null, -1)]])) : a("", !0)], 14, Ct))), 128))], 4));
	}
}), Et = {
	key: 0,
	class: "fixed inset-0 z-[1000] flex items-start justify-center pt-[15vh] p-4"
}, Dt = { class: "flex items-center gap-3 px-4 py-3 border-b border-gray-100 dark:border-gray-700" }, Ot = ["placeholder"], kt = {
	key: 0,
	class: "px-2 py-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400"
}, At = [
	"data-active",
	"aria-selected",
	"onClick",
	"onMouseenter"
], jt = ["innerHTML"], Mt = {
	key: 1,
	class: "ml-auto text-[0.625rem] text-gray-400 font-mono dark:text-gray-500"
}, Nt = {
	key: 1,
	class: "px-3 py-6 text-center text-sm text-gray-500 dark:text-gray-400"
}, Pt = /*@__PURE__*/ u({
	__name: "BaseCommandPalette",
	props: {
		modelValue: { type: Boolean },
		items: {},
		placeholder: { default: "Type a command or search..." }
	},
	emits: ["update:modelValue", "select"],
	setup(c, { emit: u }) {
		let d = c, m = u, h = b(""), y = b(0), S = b(), C = b(), E = r(() => {
			if (!h.value) return d.items.filter((e) => !e.disabled);
			let e = h.value.toLowerCase();
			return d.items.filter((t) => !t.disabled && t.label.toLowerCase().includes(e));
		}), O = r(() => {
			let e = {};
			for (let t of E.value) {
				let n = t.group || "";
				e[n] || (e[n] = []), e[n].push(t);
			}
			return e;
		}), M = r(() => E.value);
		k(() => d.modelValue, (e) => {
			e && (h.value = "", y.value = 0, f(() => S.value?.focus()));
		}), k(h, () => {
			y.value = 0;
		});
		function N() {
			m("update:modelValue", !1);
		}
		function P(e) {
			m("select", e), N();
		}
		function F(e) {
			switch (e.key) {
				case "ArrowDown":
					e.preventDefault(), y.value = (y.value + 1) % M.value.length, I();
					break;
				case "ArrowUp":
					e.preventDefault(), y.value = y.value <= 0 ? M.value.length - 1 : y.value - 1, I();
					break;
				case "Enter": {
					e.preventDefault();
					let t = M.value[y.value];
					t && P(t);
					break;
				}
				case "Escape":
					e.preventDefault(), N();
					break;
			}
		}
		function I() {
			f(() => {
				(C.value?.querySelector("[data-active=\"true\"]"))?.scrollIntoView({ block: "nearest" });
			});
		}
		function L(e) {
			if (!h.value) return e;
			let t = RegExp(`(${R(h.value)})`, "gi");
			return e.replace(t, "<mark class=\"bg-yellow-200 dark:bg-yellow-800 rounded px-0.5\">$1</mark>");
		}
		function R(e) {
			return e.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
		}
		function z(e) {
			e.key === "Escape" && d.modelValue && N();
		}
		return g(() => {
			document.addEventListener("keydown", z);
		}), _(() => {
			document.removeEventListener("keydown", z);
		}), (r, u) => (v(), i(t, { to: "body" }, [l(n, { name: "command-palette" }, {
			default: A(() => [c.modelValue ? (v(), o("div", Et, [s("div", {
				class: "absolute inset-0 bg-black/50",
				onClick: N
			}), s("div", {
				class: "relative w-full max-w-lg bg-white rounded-xl shadow-2xl overflow-hidden dark:bg-gray-800",
				onKeydown: F
			}, [s("div", Dt, [
				u[1] ||= s("svg", {
					class: "w-5 h-5 shrink-0 text-gray-400 dark:text-gray-500",
					xmlns: "http://www.w3.org/2000/svg",
					viewBox: "0 0 20 20",
					fill: "currentColor"
				}, [s("path", {
					"fill-rule": "evenodd",
					d: "M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z",
					"clip-rule": "evenodd"
				})], -1),
				j(s("input", {
					ref_key: "inputRef",
					ref: S,
					"onUpdate:modelValue": u[0] ||= (e) => h.value = e,
					type: "text",
					class: "flex-1 bg-transparent text-sm text-gray-900 placeholder-gray-400 outline-none dark:text-gray-100 dark:placeholder-gray-500",
					placeholder: c.placeholder
				}, null, 8, Ot), [[D, h.value]]),
				u[2] ||= s("kbd", { class: "hidden sm:inline-flex items-center rounded border border-gray-200 px-1.5 py-0.5 text-[0.625rem] text-gray-400 font-mono dark:border-gray-600 dark:text-gray-500" }, " ESC ", -1)
			]), s("div", {
				ref_key: "listRef",
				ref: C,
				class: "max-h-72 overflow-y-auto p-2",
				role: "listbox"
			}, [M.value.length ? (v(!0), o(e, { key: 0 }, x(O.value, (t, n) => (v(), o(e, { key: n }, [n ? (v(), o("div", kt, T(n), 1)) : a("", !0), (v(!0), o(e, null, x(t, (e, t) => (v(), o("button", {
				key: e.id,
				type: "button",
				class: p(["w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-left transition-colors cursor-pointer", {
					"bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300": M.value.indexOf(e) === y.value,
					"text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/50": M.value.indexOf(e) !== y.value
				}]),
				"data-active": M.value.indexOf(e) === y.value,
				role: "option",
				"aria-selected": M.value.indexOf(e) === y.value,
				onClick: (t) => P(e),
				onMouseenter: (t) => y.value = M.value.indexOf(e)
			}, [
				e.icon ? (v(), i(w(e.icon), {
					key: 0,
					class: "w-4 h-4 shrink-0 text-gray-400 dark:text-gray-500"
				})) : a("", !0),
				s("span", {
					class: "flex-1 truncate",
					innerHTML: L(e.label)
				}, null, 8, jt),
				e.shortcut ? (v(), o("kbd", Mt, T(e.shortcut), 1)) : a("", !0)
			], 42, At))), 128))], 64))), 128)) : (v(), o("div", Nt, " No results found. "))], 512)], 32)])) : a("", !0)]),
			_: 1
		})]));
	}
}), $ = (e, t) => {
	let n = e.__vccOpts || e;
	for (let [e, r] of t) n[e] = r;
	return n;
}, Ft = /*#__PURE__*/ $(Pt, [["__scopeId", "data-v-05fd4574"]]), It = {
	key: 0,
	class: "h-px bg-gray-100 my-1 dark:bg-gray-700",
	role: "separator"
}, Lt = [
	"disabled",
	"onClick",
	"onMouseenter"
], Rt = { class: "flex-1" }, zt = {
	key: 1,
	class: "ml-auto text-[0.625rem] text-gray-400 font-mono dark:text-gray-500"
}, Bt = /* @__PURE__ */ u({
	__name: "BaseContextMenu",
	props: { items: {} },
	emits: ["select"],
	setup(r, { expose: c, emit: u }) {
		let d = r, h = u, y = b(!1), C = b({
			x: 0,
			y: 0
		}), E = b(-1), D = b(), O = b();
		function k(e) {
			e.preventDefault(), C.value = {
				x: e.clientX,
				y: e.clientY
			}, y.value = !0, E.value = -1, f(M);
		}
		function j() {
			y.value = !1, E.value = -1;
		}
		function M() {
			if (!D.value) return;
			let e = D.value.getBoundingClientRect(), { innerWidth: t, innerHeight: n } = window;
			C.value.x + e.width > t && (C.value.x = t - e.width - 8), C.value.y + e.height > n && (C.value.y = n - e.height - 8);
		}
		function N(e) {
			e.disabled || e.divider || (h("select", e), j());
		}
		function P(e) {
			D.value && !D.value.contains(e.target) && j();
		}
		function F() {
			let e = [];
			return d.items.forEach((t, n) => {
				!t.divider && !t.disabled && e.push(n);
			}), e;
		}
		function I(e) {
			if (!y.value) return;
			let t = F();
			switch (e.key) {
				case "ArrowDown": {
					e.preventDefault();
					let n = t.indexOf(E.value), r = n < t.length - 1 ? t[n + 1] : t[0];
					E.value = r ?? -1;
					break;
				}
				case "ArrowUp": {
					e.preventDefault();
					let n = t.indexOf(E.value), r = n > 0 ? t[n - 1] : t[t.length - 1];
					E.value = r ?? -1;
					break;
				}
				case "Enter": {
					e.preventDefault();
					let t = E.value >= 0 ? d.items[E.value] : void 0;
					t && N(t);
					break;
				}
				case "Escape":
					e.preventDefault(), j();
					break;
			}
		}
		return g(() => {
			document.addEventListener("click", P), document.addEventListener("keydown", I);
		}), _(() => {
			document.removeEventListener("click", P), document.removeEventListener("keydown", I);
		}), c({
			open: k,
			close: j
		}), (c, u) => (v(), o(e, null, [s("div", {
			ref_key: "triggerRef",
			ref: O,
			onContextmenu: k
		}, [S(c.$slots, "default")], 544), (v(), i(t, { to: "body" }, [l(n, {
			"enter-active-class": "transition duration-100 ease-out",
			"enter-from-class": "opacity-0 scale-95",
			"enter-to-class": "opacity-100 scale-100",
			"leave-active-class": "transition duration-75 ease-in",
			"leave-from-class": "opacity-100 scale-100",
			"leave-to-class": "opacity-0 scale-95"
		}, {
			default: A(() => [y.value ? (v(), o("div", {
				key: 0,
				ref_key: "menuRef",
				ref: D,
				class: "fixed z-[9999] min-w-[10rem] bg-white border border-gray-200 rounded-lg shadow-lg py-1 dark:bg-gray-800 dark:border-gray-700",
				style: m({
					left: `${C.value.x}px`,
					top: `${C.value.y}px`
				}),
				role: "menu"
			}, [(v(!0), o(e, null, x(r.items, (t, n) => (v(), o(e, { key: t.id }, [t.divider ? (v(), o("div", It)) : (v(), o("button", {
				key: 1,
				type: "button",
				class: p(["w-full flex items-center gap-2 px-3 py-2 text-sm text-left transition-colors", [t.disabled ? "opacity-40 cursor-not-allowed" : "cursor-pointer", E.value === n ? "bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300" : "text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/50"]]),
				disabled: t.disabled,
				role: "menuitem",
				onClick: (e) => N(t),
				onMouseenter: (e) => E.value = n
			}, [
				t.icon ? (v(), i(w(t.icon), {
					key: 0,
					class: "w-4 h-4 shrink-0 text-gray-400 dark:text-gray-500"
				})) : a("", !0),
				s("span", Rt, T(t.label), 1),
				t.shortcut ? (v(), o("kbd", zt, T(t.shortcut), 1)) : a("", !0)
			], 42, Lt))], 64))), 128))], 4)) : a("", !0)]),
			_: 1
		})]))], 64));
	}
}), Vt = {
	key: 0,
	class: "text-sm font-medium text-gray-700 dark:text-gray-300"
}, Ht = {
	key: 0,
	class: "flex-1 text-gray-800 truncate dark:text-gray-200"
}, Ut = {
	key: 1,
	class: "flex-1 text-gray-400 truncate dark:text-gray-500"
}, Wt = { class: "flex items-center justify-center gap-2 py-4" }, Gt = { class: "text-center" }, Kt = { class: "text-center" }, qt = { class: "flex items-center justify-between mb-3" }, Jt = { class: "text-sm font-semibold text-gray-800" }, Yt = {
	key: 0,
	class: "text-[0.6875rem] text-gray-400 text-center mb-2"
}, Xt = { class: "grid grid-cols-7 mb-1" }, Zt = { class: "grid grid-cols-7 gap-0.5" }, Qt = ["onClick"], $t = {
	key: 1,
	class: "mt-3 pt-3 border-t border-gray-100"
}, en = { class: "flex items-center justify-between" }, tn = { class: "flex items-center gap-1.5 text-xs text-gray-500" }, nn = { class: "flex items-center gap-1" }, rn = {
	key: 1,
	class: "text-xs text-red-500"
}, an = /* @__PURE__ */ u({
	__name: "BaseDatePicker",
	props: {
		modelValue: {},
		mode: { default: "date" },
		format: { default: "YYYY-MM-DD" },
		placeholder: { default: "" },
		label: {},
		error: {},
		disabled: {
			type: Boolean,
			default: !1
		},
		clearable: {
			type: Boolean,
			default: !0
		},
		size: { default: "md" }
	},
	emits: ["update:modelValue"],
	setup(u, { emit: d }) {
		let f = u, h = d, y = b(!1), S = b(), C = b(), w = b({}), O = b((/* @__PURE__ */ new Date()).getFullYear()), k = b((/* @__PURE__ */ new Date()).getMonth()), M = b(0), P = b(0), F = b(""), I = b(""), L = b("start"), R = [
			"Su",
			"Mo",
			"Tu",
			"We",
			"Th",
			"Fr",
			"Sa"
		], z = [
			"January",
			"February",
			"March",
			"April",
			"May",
			"June",
			"July",
			"August",
			"September",
			"October",
			"November",
			"December"
		];
		function B(e) {
			return e.toString().padStart(2, "0");
		}
		function V(e, t) {
			let n = e.getFullYear(), r = e.getMonth() + 1, i = e.getDate(), a = e.getHours(), o = e.getMinutes(), s = e.getSeconds();
			return t.replace("YYYY", n.toString()).replace("YY", n.toString().slice(-2)).replace("MM", B(r)).replace("M", r.toString()).replace("DD", B(i)).replace("D", i.toString()).replace("HH", B(a)).replace("H", a.toString()).replace("hh", B(a > 12 ? a - 12 : a || 12)).replace("mm", B(o)).replace("ss", B(s)).replace("A", a >= 12 ? "PM" : "AM").replace("a", a >= 12 ? "pm" : "am");
		}
		function H(e) {
			if (!e) return null;
			let t = new Date(e);
			return isNaN(t.getTime()) ? null : t;
		}
		function W(e, t, n) {
			return `${e}-${B(t + 1)}-${B(n)}`;
		}
		let G = r(() => {
			if (f.mode === "range") {
				let e = f.modelValue;
				return !e || !e[0] && !e[1] ? "" : `${e[0] ? V(new Date(e[0]), f.format) : ""} → ${e[1] ? V(new Date(e[1]), f.format) : ""}`;
			}
			let e = f.modelValue;
			if (!e) return "";
			let t = H(e);
			return t ? V(t, f.format) : e;
		}), ee = r(() => {
			let e = new Date(O.value, k.value, 1).getDay(), t = new Date(O.value, k.value + 1, 0).getDate(), n = new Date(O.value, k.value, 0).getDate(), r = [];
			for (let t = e - 1; t >= 0; t--) {
				let e = n - t, i = k.value - 1, a = i < 0 ? O.value - 1 : O.value;
				r.push({
					day: e,
					month: "prev",
					iso: W(a, i < 0 ? 11 : i, e)
				});
			}
			for (let e = 1; e <= t; e++) r.push({
				day: e,
				month: "current",
				iso: W(O.value, k.value, e)
			});
			let i = 42 - r.length;
			for (let e = 1; e <= i; e++) {
				let t = k.value + 1, n = t > 11 ? O.value + 1 : O.value;
				r.push({
					day: e,
					month: "next",
					iso: W(n, t > 11 ? 0 : t, e)
				});
			}
			return r;
		});
		function J(e) {
			return f.mode === "range" ? e === F.value || e === I.value : f.modelValue?.startsWith(e);
		}
		function X(e) {
			return f.mode !== "range" || !F.value || !I.value ? !1 : e > F.value && e < I.value;
		}
		function te(e) {
			let t = /* @__PURE__ */ new Date();
			return e === W(t.getFullYear(), t.getMonth(), t.getDate());
		}
		function ne(e) {
			if (f.mode === "range") {
				L.value === "start" ? (F.value = e, I.value = "", L.value = "end") : (e < F.value ? (I.value = F.value, F.value = e) : I.value = e, L.value = "start", h("update:modelValue", [F.value, I.value]), y.value = !1);
				return;
			}
			if (f.mode !== "time") if (f.mode === "datetime") {
				let t = `${e}T${B(M.value)}:${B(P.value)}:00`;
				h("update:modelValue", t);
			} else h("update:modelValue", e), y.value = !1;
		}
		function re() {
			let e = `${f.modelValue?.slice(0, 10) || W(O.value, k.value, (/* @__PURE__ */ new Date()).getDate())}T${B(M.value)}:${B(P.value)}:00`;
			h("update:modelValue", e), y.value = !1;
		}
		function ie() {
			let e = `${B(M.value)}:${B(P.value)}:00`;
			h("update:modelValue", e), y.value = !1;
		}
		function ae() {
			k.value === 0 ? (O.value--, k.value = 11) : k.value--;
		}
		function oe() {
			k.value === 11 ? (O.value++, k.value = 0) : k.value++;
		}
		function se() {
			f.mode === "range" ? (F.value = "", I.value = "", h("update:modelValue", ["", ""])) : h("update:modelValue", "");
		}
		function Z() {
			if (!C.value) return;
			let e = C.value.getBoundingClientRect();
			w.value = {
				position: "fixed",
				top: `${e.bottom + 4}px`,
				left: `${e.left}px`,
				zIndex: "9999"
			};
		}
		function ce() {
			if (f.disabled) return;
			y.value = !0, Z();
			let e = f.mode === "range" ? f.modelValue?.[0] : f.modelValue;
			if (e) {
				let t = H(e);
				t && (O.value = t.getFullYear(), k.value = t.getMonth(), (f.mode === "datetime" || f.mode === "time") && (M.value = t.getHours(), P.value = t.getMinutes()));
			}
			if (f.mode === "range") {
				let e = f.modelValue;
				F.value = e?.[0] || "", I.value = e?.[1] || "";
			}
		}
		function le(e) {
			if (S.value && !S.value.contains(e.target)) {
				let t = document.querySelector("[data-datepicker-portal]");
				if (t && t.contains(e.target)) return;
				y.value = !1;
			}
		}
		g(() => {
			document.addEventListener("click", le), window.addEventListener("scroll", Z, !0);
		}), _(() => {
			document.removeEventListener("click", le), window.removeEventListener("scroll", Z, !0);
		});
		let ue = {
			sm: "min-h-[1.875rem] px-2 py-1 text-xs",
			md: "min-h-[2.375rem] px-2.5 py-1.5 text-sm",
			lg: "min-h-[2.875rem] px-3 py-2 text-base"
		};
		return (r, d) => (v(), o("div", {
			ref_key: "containerRef",
			ref: S,
			class: "flex flex-col gap-1"
		}, [
			u.label ? (v(), o("label", Vt, T(u.label), 1)) : a("", !0),
			s("div", {
				ref_key: "triggerRef",
				ref: C,
				class: p(["flex items-center gap-2 border border-gray-300 rounded-md bg-white cursor-pointer transition-all duration-150 focus-within:border-primary-500 focus-within:ring-2 focus-within:ring-primary-100 dark:bg-gray-800 dark:border-gray-600 dark:focus-within:ring-primary-900/30", [
					ue[u.size],
					u.disabled ? "opacity-50 cursor-not-allowed" : "",
					u.error ? "border-red-500!" : ""
				]]),
				onClick: ce
			}, [
				l(E(U), { class: "w-4 h-4 text-gray-400 shrink-0" }),
				G.value ? (v(), o("span", Ht, T(G.value), 1)) : (v(), o("span", Ut, T(u.placeholder || (u.mode === "time" ? "Select time" : u.mode === "range" ? "Select range" : "Select date")), 1)),
				u.clearable && G.value && !u.disabled ? (v(), o("button", {
					key: 2,
					class: "text-gray-400 hover:text-gray-600 cursor-pointer",
					onClick: N(se, ["stop"])
				}, [l(E(Q), { class: "w-3.5 h-3.5" })])) : a("", !0)
			], 2),
			(v(), i(t, { to: "body" }, [l(n, {
				"enter-active-class": "transition duration-150 ease-out",
				"enter-from-class": "opacity-0 scale-95 -translate-y-1",
				"enter-to-class": "opacity-100 scale-100 translate-y-0",
				"leave-active-class": "transition duration-100 ease-in",
				"leave-from-class": "opacity-100 scale-100 translate-y-0",
				"leave-to-class": "opacity-0 scale-95 -translate-y-1"
			}, {
				default: A(() => [y.value ? (v(), o("div", {
					key: 0,
					"data-datepicker-portal": "",
					style: m(w.value),
					class: "bg-white border border-gray-200 rounded-xl shadow-lg p-4 w-[18rem] dark:bg-gray-800 dark:border-gray-700"
				}, [u.mode === "time" ? (v(), o(e, { key: 0 }, [s("div", Wt, [
					s("div", Gt, [
						s("button", {
							class: "block mx-auto text-gray-400 hover:text-gray-700 cursor-pointer",
							onClick: d[0] ||= (e) => M.value = (M.value + 1) % 24
						}, [l(E(K), { class: "w-4 h-4 rotate-90" })]),
						j(s("input", {
							"onUpdate:modelValue": d[1] ||= (e) => M.value = e,
							type: "number",
							min: "0",
							max: "23",
							class: "w-12 text-center text-2xl font-semibold border border-gray-200 rounded-md py-1 outline-none focus:border-primary-500"
						}, null, 512), [[
							D,
							M.value,
							void 0,
							{ number: !0 }
						]]),
						s("button", {
							class: "block mx-auto text-gray-400 hover:text-gray-700 cursor-pointer",
							onClick: d[2] ||= (e) => M.value = (M.value - 1 + 24) % 24
						}, [l(E(K), { class: "w-4 h-4 -rotate-90" })])
					]),
					d[8] ||= s("span", { class: "text-2xl font-bold text-gray-400" }, ":", -1),
					s("div", Kt, [
						s("button", {
							class: "block mx-auto text-gray-400 hover:text-gray-700 cursor-pointer",
							onClick: d[3] ||= (e) => P.value = (P.value + 1) % 60
						}, [l(E(K), { class: "w-4 h-4 rotate-90" })]),
						j(s("input", {
							"onUpdate:modelValue": d[4] ||= (e) => P.value = e,
							type: "number",
							min: "0",
							max: "59",
							class: "w-12 text-center text-2xl font-semibold border border-gray-200 rounded-md py-1 outline-none focus:border-primary-500"
						}, null, 512), [[
							D,
							P.value,
							void 0,
							{ number: !0 }
						]]),
						s("button", {
							class: "block mx-auto text-gray-400 hover:text-gray-700 cursor-pointer",
							onClick: d[5] ||= (e) => P.value = (P.value - 1 + 60) % 60
						}, [l(E(K), { class: "w-4 h-4 -rotate-90" })])
					])
				]), s("button", {
					class: "w-full py-2 text-sm font-medium text-white bg-primary-500 rounded-md hover:bg-primary-600 cursor-pointer",
					onClick: ie
				}, " Confirm ")], 64)) : (v(), o(e, { key: 1 }, [
					s("div", qt, [
						s("button", {
							class: "p-1 rounded hover:bg-gray-100 cursor-pointer",
							onClick: ae
						}, [l(E(K), { class: "w-4 h-4 text-gray-600" })]),
						s("span", Jt, T(z[k.value]) + " " + T(O.value), 1),
						s("button", {
							class: "p-1 rounded hover:bg-gray-100 cursor-pointer",
							onClick: oe
						}, [l(E(q), { class: "w-4 h-4 text-gray-600" })])
					]),
					u.mode === "range" ? (v(), o("p", Yt, T(L.value === "start" ? "Select start date" : "Select end date"), 1)) : a("", !0),
					s("div", Xt, [(v(), o(e, null, x(R, (e) => s("span", {
						key: e,
						class: "text-center text-[0.625rem] font-medium text-gray-400 py-1"
					}, T(e), 1)), 64))]),
					s("div", Zt, [(v(!0), o(e, null, x(ee.value, (e, t) => (v(), o("button", {
						key: t,
						class: p(["w-full aspect-square flex items-center justify-center text-xs rounded-md cursor-pointer transition-colors", [
							e.month === "current" ? "text-gray-700 hover:bg-gray-100" : "text-gray-300",
							J(e.iso) ? "bg-primary-500! text-white! hover:bg-primary-600!" : "",
							X(e.iso) ? "bg-primary-50 text-primary-700" : "",
							te(e.iso) && !J(e.iso) ? "ring-1 ring-primary-300" : ""
						]]),
						onClick: (t) => ne(e.iso)
					}, T(e.day), 11, Qt))), 128))]),
					u.mode === "datetime" ? (v(), o("div", $t, [s("div", en, [s("div", tn, [l(E(Y), { class: "w-3.5 h-3.5" }), d[9] ||= c(" Time ", -1)]), s("div", nn, [
						j(s("input", {
							"onUpdate:modelValue": d[6] ||= (e) => M.value = e,
							type: "number",
							min: "0",
							max: "23",
							class: "w-10 text-center text-sm border border-gray-200 rounded py-0.5 outline-none focus:border-primary-500"
						}, null, 512), [[
							D,
							M.value,
							void 0,
							{ number: !0 }
						]]),
						d[10] ||= s("span", { class: "text-gray-400 font-bold" }, ":", -1),
						j(s("input", {
							"onUpdate:modelValue": d[7] ||= (e) => P.value = e,
							type: "number",
							min: "0",
							max: "59",
							class: "w-10 text-center text-sm border border-gray-200 rounded py-0.5 outline-none focus:border-primary-500"
						}, null, 512), [[
							D,
							P.value,
							void 0,
							{ number: !0 }
						]])
					])]), s("button", {
						class: "w-full mt-3 py-1.5 text-xs font-medium text-white bg-primary-500 rounded-md hover:bg-primary-600 cursor-pointer",
						onClick: re
					}, " Confirm ")])) : a("", !0)
				], 64))], 4)) : a("", !0)]),
				_: 1
			})])),
			u.error ? (v(), o("span", rn, T(u.error), 1)) : a("", !0)
		], 512));
	}
}), on = {
	key: 1,
	class: "flex items-center w-full",
	role: "separator",
	"aria-orientation": "horizontal"
}, sn = { class: "px-3 text-xs font-medium text-gray-400 dark:text-gray-500 whitespace-nowrap" }, cn = /* @__PURE__ */ u({
	__name: "BaseDivider",
	props: {
		label: {},
		orientation: { default: "horizontal" },
		variant: { default: "solid" },
		position: { default: "center" }
	},
	setup(e) {
		let t = {
			solid: "border-solid",
			dashed: "border-dashed",
			dotted: "border-dotted"
		};
		return (n, r) => e.orientation === "vertical" ? (v(), o("div", {
			key: 0,
			class: p(["inline-flex self-stretch border-l border-gray-200 dark:border-gray-700", t[e.variant]]),
			role: "separator",
			"aria-orientation": "vertical"
		}, null, 2)) : e.label ? (v(), o("div", on, [
			s("div", { class: p(["border-t border-gray-200 dark:border-gray-700", [t[e.variant], e.position === "left" ? "w-[10%]" : (e.position, "flex-1")]]) }, null, 2),
			s("span", sn, T(e.label), 1),
			s("div", { class: p(["border-t border-gray-200 dark:border-gray-700", [t[e.variant], e.position === "right" ? "w-[10%]" : (e.position, "flex-1")]]) }, null, 2)
		])) : (v(), o("hr", {
			key: 2,
			class: p(["border-t border-gray-200 dark:border-gray-700 w-full", t[e.variant]]),
			role: "separator",
			"aria-orientation": "horizontal"
		}, null, 2));
	}
}), ln = { class: "flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700" }, un = {
	key: 0,
	class: "text-lg font-semibold text-gray-900 dark:text-gray-100"
}, dn = { class: "flex-1 overflow-y-auto p-6 dark:text-gray-300" }, fn = {
	key: 0,
	class: "px-6 py-4 border-t border-gray-100 flex gap-2 justify-end dark:border-gray-700"
}, pn = /*#__PURE__*/ $(/* @__PURE__ */ u({
	__name: "BaseDrawer",
	props: {
		modelValue: { type: Boolean },
		position: { default: "right" },
		size: { default: "md" },
		title: {},
		overlay: {
			type: Boolean,
			default: !0
		}
	},
	emits: ["update:modelValue"],
	setup(e, { emit: c }) {
		let u = e, d = c;
		function f() {
			d("update:modelValue", !1);
		}
		let m = {
			sm: "w-72",
			md: "w-96",
			lg: "w-[32rem]",
			xl: "w-[42rem]",
			full: "w-screen"
		}, h = {
			left: "left-0 top-0 h-full",
			right: "right-0 top-0 h-full"
		}, g = r(() => [
			"fixed z-[1001] flex flex-col bg-white shadow-xl dark:bg-gray-800",
			m[u.size],
			h[u.position]
		]);
		return r(() => u.position === "left" ? "-translate-x-full" : "translate-x-full"), (r, c) => (v(), i(t, { to: "body" }, [l(n, { name: "drawer-overlay" }, {
			default: A(() => [e.modelValue && e.overlay ? (v(), o("div", {
				key: 0,
				class: "fixed inset-0 bg-black/50 z-[1000]",
				onClick: f
			})) : a("", !0)]),
			_: 1
		}), l(n, { name: `drawer-${e.position}` }, {
			default: A(() => [e.modelValue ? (v(), o("div", {
				key: 0,
				class: p(g.value),
				role: "dialog",
				"aria-modal": "true"
			}, [
				s("div", ln, [S(r.$slots, "header", {}, () => [e.title ? (v(), o("h3", un, T(e.title), 1)) : a("", !0)], !0), s("button", {
					class: "text-2xl text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded p-1 leading-none cursor-pointer dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700",
					"aria-label": "Close drawer",
					onClick: f
				}, " × ")]),
				s("div", dn, [S(r.$slots, "default", {}, void 0, !0)]),
				r.$slots.footer ? (v(), o("div", fn, [S(r.$slots, "footer", {}, void 0, !0)])) : a("", !0)
			], 2)) : a("", !0)]),
			_: 3
		}, 8, ["name"])]));
	}
}), [["__scopeId", "data-v-ef4dc025"]]), mn = { class: "flex flex-col gap-1" }, hn = {
	key: 0,
	class: "text-sm font-medium text-gray-700 dark:text-gray-300"
}, gn = {
	key: 0,
	class: "flex flex-wrap items-center gap-0.5 p-2 bg-gray-50 border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700"
}, _n = {
	key: 5,
	class: "w-px h-5 bg-gray-300 mx-1 dark:bg-gray-600"
}, vn = {
	key: 7,
	class: "w-px h-5 bg-gray-300 mx-1 dark:bg-gray-600"
}, yn = ["disabled"], bn = ["disabled"], xn = {
	key: 1,
	class: "text-xs text-red-500"
}, Sn = {
	key: 2,
	class: "text-xs text-gray-500 dark:text-gray-400"
}, Cn = /* @__PURE__ */ u({
	__name: "BaseEditor",
	props: {
		modelValue: { default: "" },
		variant: { default: "default" },
		size: { default: "md" },
		placeholder: { default: "Tulis sesuatu..." },
		disabled: {
			type: Boolean,
			default: !1
		},
		label: {},
		hint: {},
		error: {},
		maxHeight: { default: "400px" }
	},
	emits: ["update:modelValue"],
	setup(t, { emit: n }) {
		let i = t, c = n, u = Ae({
			content: i.modelValue,
			editable: !i.disabled,
			extensions: [
				je.configure({ heading: { levels: [
					1,
					2,
					3
				] } }),
				Me.configure({ placeholder: i.placeholder }),
				Ne.configure({ types: ["heading", "paragraph"] }),
				Pe,
				Fe.configure({ openOnClick: !1 }),
				Ie,
				Le,
				Re,
				ze.configure({ multicolor: !0 }),
				Be,
				Ve.configure({ nested: !0 })
			],
			onUpdate: () => {
				c("update:modelValue", u.value?.getHTML() ?? "");
			}
		});
		k(() => i.modelValue, (e) => {
			u.value && u.value.getHTML() !== e && u.value.commands.setContent(e, { emitUpdate: !1 });
		}), k(() => i.disabled, (e) => {
			u.value?.setEditable(!e);
		}), h(() => {
			u.value?.destroy();
		});
		function d() {
			let e = u.value?.getAttributes("link").href, t = window.prompt("URL", e);
			if (t !== null) {
				if (t === "") {
					u.value?.chain().focus().extendMarkRange("link").unsetLink().run();
					return;
				}
				u.value?.chain().focus().extendMarkRange("link").setLink({ href: t }).run();
			}
		}
		function f() {
			let e = window.prompt("Image URL");
			e && u.value?.chain().focus().setImage({ src: e }).run();
		}
		function g() {
			let e = window.prompt("Warna (hex)", "#ff0000");
			e && u.value?.chain().focus().setColor(e).run();
		}
		function _() {
			let e = window.prompt("Highlight color (hex)", "#fef08a");
			e && u.value?.chain().focus().toggleHighlight({ color: e }).run();
		}
		let y = r(() => i.variant !== "minimal"), b = r(() => i.variant === "full"), x = r(() => i.variant === "full"), S = r(() => ({
			sm: {
				minHeight: "min-h-[120px]",
				text: "text-sm"
			},
			md: {
				minHeight: "min-h-[200px]",
				text: "text-sm"
			},
			lg: {
				minHeight: "min-h-[300px]",
				text: "text-base"
			}
		})[i.size]);
		return (n, r) => (v(), o("div", mn, [
			t.label ? (v(), o("label", hn, T(t.label), 1)) : a("", !0),
			s("div", { class: p(["border rounded-lg overflow-hidden transition-all", [t.error ? "border-red-500" : "border-gray-300 focus-within:border-primary-500 focus-within:ring-2 focus-within:ring-primary-100 dark:border-gray-600 dark:focus-within:ring-primary-900/30", t.disabled ? "opacity-50" : ""]]) }, [E(u) ? (v(), o("div", gn, [
				s("button", {
					type: "button",
					class: p(["p-1.5 rounded hover:bg-gray-200 transition-colors dark:hover:bg-gray-700", E(u).isActive("bold") ? "bg-gray-200 text-primary-600 dark:bg-gray-700" : "text-gray-600 dark:text-gray-400"]),
					title: "Bold",
					onClick: r[0] ||= (e) => E(u).chain().focus().toggleBold().run()
				}, [l(E(H), { class: "w-4 h-4" })], 2),
				s("button", {
					type: "button",
					class: p(["p-1.5 rounded hover:bg-gray-200 transition-colors dark:hover:bg-gray-700", E(u).isActive("italic") ? "bg-gray-200 text-primary-600 dark:bg-gray-700" : "text-gray-600 dark:text-gray-400"]),
					title: "Italic",
					onClick: r[1] ||= (e) => E(u).chain().focus().toggleItalic().run()
				}, [l(E(de), { class: "w-4 h-4" })], 2),
				s("button", {
					type: "button",
					class: p(["p-1.5 rounded hover:bg-gray-200 transition-colors dark:hover:bg-gray-700", E(u).isActive("underline") ? "bg-gray-200 text-primary-600 dark:bg-gray-700" : "text-gray-600 dark:text-gray-400"]),
					title: "Underline",
					onClick: r[2] ||= (e) => E(u).chain().focus().toggleUnderline().run()
				}, [l(E(Ee), { class: "w-4 h-4" })], 2),
				y.value ? (v(), o("button", {
					key: 0,
					type: "button",
					class: p(["p-1.5 rounded hover:bg-gray-200 transition-colors dark:hover:bg-gray-700", E(u).isActive("strike") ? "bg-gray-200 text-primary-600 dark:bg-gray-700" : "text-gray-600 dark:text-gray-400"]),
					title: "Strikethrough",
					onClick: r[3] ||= (e) => E(u).chain().focus().toggleStrike().run()
				}, [l(E(Te), { class: "w-4 h-4" })], 2)) : a("", !0),
				y.value ? (v(), o("button", {
					key: 1,
					type: "button",
					class: p(["p-1.5 rounded hover:bg-gray-200 transition-colors dark:hover:bg-gray-700", E(u).isActive("code") ? "bg-gray-200 text-primary-600 dark:bg-gray-700" : "text-gray-600 dark:text-gray-400"]),
					title: "Inline Code",
					onClick: r[4] ||= (e) => E(u).chain().focus().toggleCode().run()
				}, [l(E(X), { class: "w-4 h-4" })], 2)) : a("", !0),
				r[23] ||= s("div", { class: "w-px h-5 bg-gray-300 mx-1 dark:bg-gray-600" }, null, -1),
				y.value ? (v(), o("button", {
					key: 2,
					type: "button",
					class: p(["p-1.5 rounded hover:bg-gray-200 transition-colors dark:hover:bg-gray-700", E(u).isActive("heading", { level: 1 }) ? "bg-gray-200 text-primary-600 dark:bg-gray-700" : "text-gray-600 dark:text-gray-400"]),
					title: "Heading 1",
					onClick: r[5] ||= (e) => E(u).chain().focus().toggleHeading({ level: 1 }).run()
				}, [l(E(oe), { class: "w-4 h-4" })], 2)) : a("", !0),
				y.value ? (v(), o("button", {
					key: 3,
					type: "button",
					class: p(["p-1.5 rounded hover:bg-gray-200 transition-colors dark:hover:bg-gray-700", E(u).isActive("heading", { level: 2 }) ? "bg-gray-200 text-primary-600 dark:bg-gray-700" : "text-gray-600 dark:text-gray-400"]),
					title: "Heading 2",
					onClick: r[6] ||= (e) => E(u).chain().focus().toggleHeading({ level: 2 }).run()
				}, [l(E(se), { class: "w-4 h-4" })], 2)) : a("", !0),
				y.value ? (v(), o("button", {
					key: 4,
					type: "button",
					class: p(["p-1.5 rounded hover:bg-gray-200 transition-colors dark:hover:bg-gray-700", E(u).isActive("heading", { level: 3 }) ? "bg-gray-200 text-primary-600 dark:bg-gray-700" : "text-gray-600 dark:text-gray-400"]),
					title: "Heading 3",
					onClick: r[7] ||= (e) => E(u).chain().focus().toggleHeading({ level: 3 }).run()
				}, [l(E(Z), { class: "w-4 h-4" })], 2)) : a("", !0),
				y.value ? (v(), o("div", _n)) : a("", !0),
				s("button", {
					type: "button",
					class: p(["p-1.5 rounded hover:bg-gray-200 transition-colors dark:hover:bg-gray-700", E(u).isActive("bulletList") ? "bg-gray-200 text-primary-600 dark:bg-gray-700" : "text-gray-600 dark:text-gray-400"]),
					title: "Bullet List",
					onClick: r[8] ||= (e) => E(u).chain().focus().toggleBulletList().run()
				}, [l(E(pe), { class: "w-4 h-4" })], 2),
				s("button", {
					type: "button",
					class: p(["p-1.5 rounded hover:bg-gray-200 transition-colors dark:hover:bg-gray-700", E(u).isActive("orderedList") ? "bg-gray-200 text-primary-600 dark:bg-gray-700" : "text-gray-600 dark:text-gray-400"]),
					title: "Ordered List",
					onClick: r[9] ||= (e) => E(u).chain().focus().toggleOrderedList().run()
				}, [l(E(me), { class: "w-4 h-4" })], 2),
				y.value ? (v(), o("button", {
					key: 6,
					type: "button",
					class: p(["p-1.5 rounded hover:bg-gray-200 transition-colors dark:hover:bg-gray-700", E(u).isActive("taskList") ? "bg-gray-200 text-primary-600 dark:bg-gray-700" : "text-gray-600 dark:text-gray-400"]),
					title: "Task List",
					onClick: r[10] ||= (e) => E(u).chain().focus().toggleTaskList().run()
				}, [l(E(he), { class: "w-4 h-4" })], 2)) : a("", !0),
				y.value ? (v(), o("div", vn)) : a("", !0),
				y.value ? (v(), o("button", {
					key: 8,
					type: "button",
					class: p(["p-1.5 rounded hover:bg-gray-200 transition-colors dark:hover:bg-gray-700", E(u).isActive("blockquote") ? "bg-gray-200 text-primary-600 dark:bg-gray-700" : "text-gray-600 dark:text-gray-400"]),
					title: "Blockquote",
					onClick: r[11] ||= (e) => E(u).chain().focus().toggleBlockquote().run()
				}, [l(E(be), { class: "w-4 h-4" })], 2)) : a("", !0),
				y.value ? (v(), o("button", {
					key: 9,
					type: "button",
					class: p(["p-1.5 rounded hover:bg-gray-200 transition-colors dark:hover:bg-gray-700", E(u).isActive("codeBlock") ? "bg-gray-200 text-primary-600 dark:bg-gray-700" : "text-gray-600 dark:text-gray-400"]),
					title: "Code Block",
					onClick: r[12] ||= (e) => E(u).chain().focus().toggleCodeBlock().run()
				}, [l(E(te), { class: "w-4 h-4" })], 2)) : a("", !0),
				y.value ? (v(), o("button", {
					key: 10,
					type: "button",
					class: "p-1.5 rounded hover:bg-gray-200 text-gray-600 transition-colors dark:hover:bg-gray-700 dark:text-gray-400",
					title: "Horizontal Rule",
					onClick: r[13] ||= (e) => E(u).chain().focus().setHorizontalRule().run()
				}, [l(E(_e), { class: "w-4 h-4" })])) : a("", !0),
				b.value ? (v(), o(e, { key: 11 }, [
					r[21] ||= s("div", { class: "w-px h-5 bg-gray-300 mx-1 dark:bg-gray-600" }, null, -1),
					s("button", {
						type: "button",
						class: p(["p-1.5 rounded hover:bg-gray-200 transition-colors dark:hover:bg-gray-700", E(u).isActive({ textAlign: "left" }) ? "bg-gray-200 text-primary-600 dark:bg-gray-700" : "text-gray-600 dark:text-gray-400"]),
						title: "Align Left",
						onClick: r[14] ||= (e) => E(u).chain().focus().setTextAlign("left").run()
					}, [l(E(L), { class: "w-4 h-4" })], 2),
					s("button", {
						type: "button",
						class: p(["p-1.5 rounded hover:bg-gray-200 transition-colors dark:hover:bg-gray-700", E(u).isActive({ textAlign: "center" }) ? "bg-gray-200 text-primary-600 dark:bg-gray-700" : "text-gray-600 dark:text-gray-400"]),
						title: "Align Center",
						onClick: r[15] ||= (e) => E(u).chain().focus().setTextAlign("center").run()
					}, [l(E(F), { class: "w-4 h-4" })], 2),
					s("button", {
						type: "button",
						class: p(["p-1.5 rounded hover:bg-gray-200 transition-colors dark:hover:bg-gray-700", E(u).isActive({ textAlign: "right" }) ? "bg-gray-200 text-primary-600 dark:bg-gray-700" : "text-gray-600 dark:text-gray-400"]),
						title: "Align Right",
						onClick: r[16] ||= (e) => E(u).chain().focus().setTextAlign("right").run()
					}, [l(E(R), { class: "w-4 h-4" })], 2),
					s("button", {
						type: "button",
						class: p(["p-1.5 rounded hover:bg-gray-200 transition-colors dark:hover:bg-gray-700", E(u).isActive({ textAlign: "justify" }) ? "bg-gray-200 text-primary-600 dark:bg-gray-700" : "text-gray-600 dark:text-gray-400"]),
						title: "Justify",
						onClick: r[17] ||= (e) => E(u).chain().focus().setTextAlign("justify").run()
					}, [l(E(I), { class: "w-4 h-4" })], 2)
				], 64)) : a("", !0),
				x.value ? (v(), o(e, { key: 12 }, [
					r[22] ||= s("div", { class: "w-px h-5 bg-gray-300 mx-1 dark:bg-gray-600" }, null, -1),
					s("button", {
						type: "button",
						class: p(["p-1.5 rounded hover:bg-gray-200 transition-colors text-gray-600 dark:hover:bg-gray-700 dark:text-gray-400", E(u).isActive("link") ? "bg-gray-200 text-primary-600 dark:bg-gray-700" : ""]),
						title: "Link",
						onClick: d
					}, [l(E(fe), { class: "w-4 h-4" })], 2),
					s("button", {
						type: "button",
						class: "p-1.5 rounded hover:bg-gray-200 text-gray-600 transition-colors dark:hover:bg-gray-700 dark:text-gray-400",
						title: "Image",
						onClick: f
					}, [l(E(ue), { class: "w-4 h-4" })]),
					s("button", {
						type: "button",
						class: "p-1.5 rounded hover:bg-gray-200 text-gray-600 transition-colors dark:hover:bg-gray-700 dark:text-gray-400",
						title: "Text Color",
						onClick: g
					}, [l(E(ye), { class: "w-4 h-4" })]),
					s("button", {
						type: "button",
						class: p(["p-1.5 rounded hover:bg-gray-200 transition-colors dark:hover:bg-gray-700", E(u).isActive("highlight") ? "bg-gray-200 text-primary-600 dark:bg-gray-700" : "text-gray-600 dark:text-gray-400"]),
						title: "Highlight",
						onClick: _
					}, [l(E(ce), { class: "w-4 h-4" })], 2)
				], 64)) : a("", !0),
				r[24] ||= s("div", { class: "w-px h-5 bg-gray-300 mx-1 dark:bg-gray-600" }, null, -1),
				x.value ? (v(), o("button", {
					key: 13,
					type: "button",
					class: "p-1.5 rounded hover:bg-gray-200 text-gray-600 transition-colors dark:hover:bg-gray-700 dark:text-gray-400",
					title: "Clear Formatting",
					onClick: r[18] ||= (e) => E(u).chain().focus().clearNodes().unsetAllMarks().run()
				}, [l(E(Se), { class: "w-4 h-4" })])) : a("", !0),
				s("button", {
					type: "button",
					class: "p-1.5 rounded hover:bg-gray-200 text-gray-600 transition-colors dark:hover:bg-gray-700 dark:text-gray-400 disabled:opacity-30",
					title: "Undo",
					disabled: !E(u).can().undo(),
					onClick: r[19] ||= (e) => E(u).chain().focus().undo().run()
				}, [l(E(De), { class: "w-4 h-4" })], 8, yn),
				s("button", {
					type: "button",
					class: "p-1.5 rounded hover:bg-gray-200 text-gray-600 transition-colors dark:hover:bg-gray-700 dark:text-gray-400 disabled:opacity-30",
					title: "Redo",
					disabled: !E(u).can().redo(),
					onClick: r[20] ||= (e) => E(u).chain().focus().redo().run()
				}, [l(E(xe), { class: "w-4 h-4" })], 8, bn)
			])) : a("", !0), l(E(ke), {
				editor: E(u),
				class: p(["editor-content bg-white dark:bg-gray-800", [S.value.minHeight, S.value.text]]),
				style: m({
					maxHeight: t.maxHeight,
					overflowY: "auto"
				})
			}, null, 8, [
				"editor",
				"class",
				"style"
			])], 2),
			t.error ? (v(), o("span", xn, T(t.error), 1)) : t.hint ? (v(), o("span", Sn, T(t.hint), 1)) : a("", !0)
		]));
	}
}), wn = {
	key: 1,
	class: "w-1/2 h-1/2",
	xmlns: "http://www.w3.org/2000/svg",
	fill: "none",
	viewBox: "0 0 24 24",
	"stroke-width": "1.5",
	stroke: "currentColor"
}, Tn = {
	key: 2,
	class: "mt-4"
}, En = /* @__PURE__ */ u({
	__name: "BaseEmptyState",
	props: {
		title: {},
		description: {},
		icon: {},
		size: { default: "md" }
	},
	setup(e) {
		let t = {
			sm: {
				wrapper: "py-6 px-4",
				icon: "w-10 h-10",
				title: "text-sm",
				description: "text-xs"
			},
			md: {
				wrapper: "py-10 px-6",
				icon: "w-14 h-14",
				title: "text-base",
				description: "text-sm"
			},
			lg: {
				wrapper: "py-16 px-8",
				icon: "w-20 h-20",
				title: "text-lg",
				description: "text-base"
			}
		};
		return (n, r) => (v(), o("div", {
			class: p(["flex flex-col items-center justify-center text-center", t[e.size].wrapper]),
			role: "status"
		}, [
			s("div", { class: p(["flex items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500 mb-4", t[e.size].icon]) }, [S(n.$slots, "icon", {}, () => [e.icon ? (v(), i(w(e.icon), {
				key: 0,
				class: "w-1/2 h-1/2"
			})) : (v(), o("svg", wn, [...r[0] ||= [s("path", {
				"stroke-linecap": "round",
				"stroke-linejoin": "round",
				d: "M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"
			}, null, -1)]]))])], 2),
			e.title ? (v(), o("h3", {
				key: 0,
				class: p(["font-medium text-gray-900 dark:text-gray-100", t[e.size].title])
			}, T(e.title), 3)) : a("", !0),
			e.description ? (v(), o("p", {
				key: 1,
				class: p(["mt-1 text-gray-500 dark:text-gray-400 max-w-sm", t[e.size].description])
			}, T(e.description), 3)) : a("", !0),
			n.$slots.default ? (v(), o("div", Tn, [S(n.$slots, "default")])) : a("", !0)
		], 2));
	}
}), Dn = { class: "flex flex-col gap-2" }, On = {
	key: 0,
	class: "text-sm font-medium text-gray-700 dark:text-gray-300"
}, kn = [
	"accept",
	"multiple",
	"disabled"
], An = {
	key: 1,
	class: "flex flex-col gap-2"
}, jn = { class: "flex flex-col items-center gap-2 text-center" }, Mn = { class: "text-xs text-gray-500 mt-1 dark:text-gray-400" }, Nn = { key: 0 }, Pn = {
	key: 3,
	class: "flex items-center gap-3"
}, Fn = ["disabled"], In = { class: "text-sm text-gray-500 dark:text-gray-400" }, Ln = {
	key: 4,
	class: "space-y-2 mt-2"
}, Rn = { class: "w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center shrink-0 dark:bg-gray-700 dark:border-gray-600" }, zn = { class: "flex-1 min-w-0" }, Bn = { class: "flex items-center gap-2" }, Vn = { class: "text-sm font-medium text-gray-700 truncate dark:text-gray-300" }, Hn = { class: "flex items-center gap-2 mt-0.5" }, Un = { class: "text-xs text-gray-500 dark:text-gray-400" }, Wn = {
	key: 0,
	class: "text-xs text-primary-600"
}, Gn = {
	key: 1,
	class: "text-xs text-red-500"
}, Kn = {
	key: 0,
	class: "mt-1.5 h-1.5 bg-gray-200 rounded-full overflow-hidden dark:bg-gray-600"
}, qn = { class: "flex items-center gap-1 shrink-0" }, Jn = ["onClick"], Yn = ["onClick"], Xn = ["onClick"], Zn = {
	key: 5,
	class: "text-xs text-red-500"
}, Qn = {
	key: 6,
	class: "text-xs text-gray-500 dark:text-gray-400"
}, $n = /* @__PURE__ */ u({
	__name: "BaseFileUpload",
	props: {
		variant: { default: "dropzone" },
		size: { default: "md" },
		accept: { default: "" },
		multiple: {
			type: Boolean,
			default: !1
		},
		maxSize: { default: 10 },
		maxFiles: { default: 5 },
		disabled: {
			type: Boolean,
			default: !1
		},
		label: {},
		hint: {},
		error: {},
		showPreview: {
			type: Boolean,
			default: !0
		}
	},
	emits: [
		"upload",
		"remove",
		"cancel"
	],
	setup(t, { expose: n, emit: u }) {
		let d = t, f = u, h = b([]), g = b(!1), _ = b(null);
		function y() {
			return Math.random().toString(36).substring(2, 10);
		}
		function S(e) {
			if (e === 0) return "0 B";
			let t = 1024, n = [
				"B",
				"KB",
				"MB",
				"GB"
			], r = Math.floor(Math.log(e) / Math.log(t));
			return parseFloat((e / t ** r).toFixed(1)) + " " + n[r];
		}
		function C(e) {
			return e.startsWith("image/") ? le : e.startsWith("video/") ? ae : e.startsWith("audio/") ? ve : e.includes("pdf") || e.includes("document") || e.includes("text") ? ie : e.includes("zip") || e.includes("rar") || e.includes("archive") ? z : re;
		}
		function D(e) {
			return d.maxSize && e.size > d.maxSize * 1024 * 1024 ? `File terlalu besar (max ${d.maxSize}MB)` : d.accept && !d.accept.split(",").map((e) => e.trim()).some((t) => t.startsWith(".") ? e.name.toLowerCase().endsWith(t.toLowerCase()) : t.endsWith("/*") ? e.type.startsWith(t.replace("/*", "/")) : e.type === t) ? "Tipe file tidak didukung" : null;
		}
		function O(e) {
			if (!e || d.disabled) return;
			let t = Array.from(e);
			if (!d.multiple && t.length > 0 && (h.value = []), !(d.multiple && h.value.length + t.length > d.maxFiles)) {
				for (let e of t) {
					let t = D(e), n = {
						id: y(),
						file: e,
						name: e.name,
						size: e.size,
						type: e.type,
						progress: 0,
						status: t ? "error" : "pending",
						error: t ?? void 0
					};
					h.value.push(n), t || k(n);
				}
				f("upload", h.value), _.value && (_.value.value = "");
			}
		}
		function k(e) {
			e.status = "uploading";
			let t = setInterval(() => {
				if (e.status !== "uploading") {
					clearInterval(t);
					return;
				}
				e.progress += Math.random() * 15 + 5, e.progress >= 100 && (e.progress = 100, e.status = "success", clearInterval(t));
			}, 300);
		}
		function A(e) {
			h.value = h.value.filter((t) => t.id !== e.id), f("remove", e);
		}
		function j(e) {
			e.status = "error", e.error = "Dibatalkan", e.progress = 0, f("cancel", e);
		}
		function M(e) {
			e.status = "pending", e.error = void 0, e.progress = 0, k(e);
		}
		function F() {
			d.disabled || _.value?.click();
		}
		function I(e) {
			e.preventDefault(), d.disabled || (g.value = !0);
		}
		function L(e) {
			e.preventDefault(), g.value = !1;
		}
		function R(e) {
			e.preventDefault(), g.value = !1, d.disabled || O(e.dataTransfer?.files ?? null);
		}
		let B = r(() => ({
			sm: {
				text: "text-xs",
				padding: "p-4",
				icon: "w-8 h-8"
			},
			md: {
				text: "text-sm",
				padding: "p-6",
				icon: "w-10 h-10"
			},
			lg: {
				text: "text-base",
				padding: "p-8",
				icon: "w-12 h-12"
			}
		})[d.size]);
		return n({
			files: h,
			addFiles: O,
			removeFile: A,
			cancelFile: j,
			retryFile: M
		}), (n, r) => (v(), o("div", Dn, [
			t.label ? (v(), o("label", On, T(t.label), 1)) : a("", !0),
			s("input", {
				ref_key: "inputRef",
				ref: _,
				type: "file",
				class: "hidden",
				accept: t.accept,
				multiple: t.multiple,
				disabled: t.disabled,
				onChange: r[0] ||= (e) => O(e.target.files)
			}, null, 40, kn),
			t.variant === "input" ? (v(), o("div", An, [s("div", {
				class: p(["flex items-center gap-2 border border-gray-300 rounded-md bg-white transition-all cursor-pointer hover:border-primary-400 dark:bg-gray-800 dark:border-gray-600", [
					B.value.padding,
					t.disabled ? "opacity-50 cursor-not-allowed" : "",
					t.error ? "border-red-500!" : ""
				]]),
				onClick: F
			}, [l(E(Oe), { class: "w-4 h-4 text-gray-400 shrink-0" }), s("span", { class: p(["text-gray-500 dark:text-gray-400", B.value.text]) }, T(h.value.length > 0 ? `${h.value.length} file dipilih` : "Pilih file..."), 3)], 2)])) : t.variant === "dropzone" ? (v(), o("div", {
				key: 2,
				class: p(["border-2 border-dashed rounded-lg transition-all cursor-pointer", [
					B.value.padding,
					g.value ? "border-primary-500 bg-primary-50 dark:bg-primary-900/20" : "border-gray-300 hover:border-primary-400 dark:border-gray-600",
					t.disabled ? "opacity-50 cursor-not-allowed" : "",
					t.error ? "border-red-500!" : ""
				]]),
				onClick: F,
				onDragenter: I,
				onDragover: r[1] ||= N(() => {}, ["prevent"]),
				onDragleave: L,
				onDrop: R
			}, [s("div", jn, [s("div", { class: p(["rounded-full bg-gray-100 p-3 dark:bg-gray-700", g.value ? "bg-primary-100 dark:bg-primary-900/30" : ""]) }, [l(E(Oe), { class: p([B.value.icon, g.value ? "text-primary-500" : "text-gray-400 dark:text-gray-500"]) }, null, 8, ["class"])], 2), s("div", null, [s("p", { class: p(["font-medium text-gray-700 dark:text-gray-300", B.value.text]) }, [...r[2] ||= [s("span", { class: "text-primary-600" }, "Klik untuk upload", -1), c(" atau drag & drop ", -1)]], 2), s("p", Mn, [c(T(t.accept || "Semua format") + " · Max " + T(t.maxSize) + "MB ", 1), t.multiple ? (v(), o("span", Nn, " · Max " + T(t.maxFiles) + " files", 1)) : a("", !0)])])])], 34)) : t.variant === "compact" ? (v(), o("div", Pn, [s("button", {
				type: "button",
				class: p(["inline-flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700", t.disabled ? "opacity-50 cursor-not-allowed" : "cursor-pointer"]),
				disabled: t.disabled,
				onClick: F
			}, [l(E(Oe), { class: "w-4 h-4" }), r[3] ||= c(" Choose File ", -1)], 10, Fn), s("span", In, T(h.value.length > 0 ? `${h.value.length} file dipilih` : "Belum ada file"), 1)])) : a("", !0),
			h.value.length > 0 ? (v(), o("div", Ln, [(v(!0), o(e, null, x(h.value, (e) => (v(), o("div", {
				key: e.id,
				class: "flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700"
			}, [
				s("div", Rn, [(v(), i(w(C(e.type)), { class: "w-4 h-4 text-gray-500 dark:text-gray-400" }))]),
				s("div", zn, [
					s("div", Bn, [
						s("p", Vn, T(e.name), 1),
						e.status === "success" ? (v(), i(E(W), {
							key: 0,
							class: "w-4 h-4 text-emerald-500 shrink-0"
						})) : a("", !0),
						e.status === "error" ? (v(), i(E(P), {
							key: 1,
							class: "w-4 h-4 text-red-500 shrink-0"
						})) : a("", !0)
					]),
					s("div", Hn, [
						s("span", Un, T(S(e.size)), 1),
						e.status === "uploading" ? (v(), o("span", Wn, T(Math.round(e.progress)) + "% ", 1)) : a("", !0),
						e.status === "error" ? (v(), o("span", Gn, T(e.error), 1)) : a("", !0)
					]),
					e.status === "uploading" ? (v(), o("div", Kn, [s("div", {
						class: "h-full bg-primary-500 rounded-full transition-[width] duration-200",
						style: m({ width: `${e.progress}%` })
					}, null, 4)])) : a("", !0)
				]),
				s("div", qn, [
					e.status === "uploading" ? (v(), o("button", {
						key: 0,
						type: "button",
						class: "p-1 text-gray-400 hover:text-red-500 rounded transition-colors cursor-pointer",
						title: "Cancel",
						onClick: N((t) => j(e), ["stop"])
					}, [l(E(Q), { class: "w-4 h-4" })], 8, Jn)) : a("", !0),
					e.status === "error" ? (v(), o("button", {
						key: 1,
						type: "button",
						class: "px-2 py-0.5 text-xs text-primary-600 hover:bg-primary-50 rounded transition-colors cursor-pointer dark:hover:bg-primary-900/30",
						onClick: N((t) => M(e), ["stop"])
					}, " Retry ", 8, Yn)) : a("", !0),
					e.status === "uploading" ? a("", !0) : (v(), o("button", {
						key: 2,
						type: "button",
						class: "p-1 text-gray-400 hover:text-red-500 rounded transition-colors cursor-pointer",
						title: "Remove",
						onClick: N((t) => A(e), ["stop"])
					}, [l(E(Q), { class: "w-4 h-4" })], 8, Xn))
				])
			]))), 128))])) : a("", !0),
			t.error ? (v(), o("span", Zn, T(t.error), 1)) : t.hint ? (v(), o("span", Qn, T(t.hint), 1)) : a("", !0)
		]));
	}
}), er = { class: "flex flex-col gap-1" }, tr = {
	key: 0,
	class: "text-sm font-medium text-gray-700 dark:text-gray-300"
}, nr = [
	"type",
	"value",
	"placeholder",
	"disabled"
], rr = {
	key: 1,
	class: "text-xs text-red-500"
}, ir = {
	key: 2,
	class: "text-xs text-gray-500 dark:text-gray-400"
}, ar = /* @__PURE__ */ u({
	__name: "BaseInput",
	props: {
		modelValue: { default: "" },
		variant: { default: "default" },
		size: { default: "md" },
		type: { default: "text" },
		placeholder: { default: "" },
		disabled: {
			type: Boolean,
			default: !1
		},
		error: {},
		label: {},
		hint: {},
		icon: {},
		iconRight: {}
	},
	emits: ["update:modelValue"],
	setup(e) {
		let t = e, n = r(() => [
			"flex items-center gap-2 transition-all duration-150",
			{
				default: "border border-gray-300 rounded-md bg-white focus-within:border-primary-500 focus-within:ring-2 focus-within:ring-primary-100 dark:bg-gray-800 dark:border-gray-600 dark:focus-within:ring-primary-900/30",
				filled: "border border-transparent rounded-md bg-gray-100 focus-within:bg-white focus-within:border-primary-500 dark:bg-gray-700 dark:focus-within:bg-gray-800",
				underlined: "border-b-2 border-gray-300 rounded-none bg-transparent focus-within:border-primary-500 dark:border-gray-600"
			}[t.variant],
			{
				sm: "px-2 py-1",
				md: "px-3 py-2",
				lg: "px-4 py-3"
			}[t.size],
			t.error ? "border-red-500! focus-within:ring-red-100!" : "",
			t.disabled ? "opacity-50 cursor-not-allowed" : ""
		]);
		return (t, r) => (v(), o("div", er, [
			e.label ? (v(), o("label", tr, T(e.label), 1)) : a("", !0),
			s("div", { class: p(n.value) }, [
				e.icon ? (v(), i(w(e.icon), {
					key: 0,
					class: "w-4 h-4 text-gray-400 shrink-0 dark:text-gray-500"
				})) : a("", !0),
				s("input", {
					type: e.type,
					value: e.modelValue,
					placeholder: e.placeholder,
					disabled: e.disabled,
					class: p(["flex-1 border-none outline-none bg-transparent text-gray-800 font-sans placeholder:text-gray-400 dark:text-gray-200 dark:placeholder:text-gray-500", {
						"text-xs": e.size === "sm",
						"text-sm": e.size === "md",
						"text-base": e.size === "lg"
					}]),
					onInput: r[0] ||= (e) => t.$emit("update:modelValue", e.target.value)
				}, null, 42, nr),
				e.iconRight ? (v(), i(w(e.iconRight), {
					key: 1,
					class: "w-4 h-4 text-gray-400 shrink-0 dark:text-gray-500"
				})) : a("", !0)
			], 2),
			e.error ? (v(), o("span", rr, T(e.error), 1)) : e.hint ? (v(), o("span", ir, T(e.hint), 1)) : a("", !0)
		]));
	}
}), or = {
	class: "inline-flex items-center gap-1",
	"aria-label": "Keyboard shortcut"
}, sr = { class: "inline-flex items-center justify-center min-w-[1.5rem] px-1.5 py-0.5 text-xs font-medium font-mono text-gray-700 bg-gray-100 border border-gray-300 rounded shadow-sm dark:text-gray-300 dark:bg-gray-800 dark:border-gray-600" }, cr = {
	key: 0,
	class: "text-xs text-gray-400 dark:text-gray-500"
}, lr = /* @__PURE__ */ u({
	__name: "BaseKbd",
	props: { keys: {} },
	setup(t) {
		let n = t, i = r(() => Array.isArray(n.keys) ? n.keys : [n.keys]);
		return (t, n) => (v(), o("span", or, [(v(!0), o(e, null, x(i.value, (t, n) => (v(), o(e, { key: n }, [s("kbd", sr, T(t), 1), n < i.value.length - 1 ? (v(), o("span", cr, " + ")) : a("", !0)], 64))), 128))]));
	}
}), ur = {
	key: 0,
	class: "flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700"
}, dr = {
	key: 0,
	class: "text-lg font-semibold dark:text-gray-100"
}, fr = { class: "p-6 overflow-y-auto dark:text-gray-300" }, pr = {
	key: 1,
	class: "px-6 py-4 border-t border-gray-100 flex gap-2 justify-end dark:border-gray-700"
}, mr = /*#__PURE__*/ $(/* @__PURE__ */ u({
	__name: "BaseModal",
	props: {
		modelValue: { type: Boolean },
		size: { default: "md" },
		title: {},
		closable: {
			type: Boolean,
			default: !0
		},
		persistent: {
			type: Boolean,
			default: !1
		}
	},
	emits: ["update:modelValue"],
	setup(e, { emit: c }) {
		let u = e, d = c;
		function f() {
			d("update:modelValue", !1);
		}
		let m = {
			sm: "max-w-sm",
			md: "max-w-lg",
			lg: "max-w-2xl",
			xl: "max-w-4xl",
			full: "max-w-[90vw]"
		}, h = r(() => ["bg-white rounded-xl shadow-xl max-h-[85vh] flex flex-col w-full dark:bg-gray-800", m[u.size]]);
		return (r, c) => (v(), i(t, { to: "body" }, [l(n, { name: "modal" }, {
			default: A(() => [e.modelValue ? (v(), o("div", {
				key: 0,
				class: "fixed inset-0 bg-black/50 flex items-center justify-center z-[1000] p-4",
				onClick: c[0] ||= N((t) => e.persistent ? void 0 : f(), ["self"])
			}, [s("div", { class: p(h.value) }, [
				e.title || e.closable ? (v(), o("div", ur, [e.title ? (v(), o("h3", dr, T(e.title), 1)) : a("", !0), e.closable ? (v(), o("button", {
					key: 1,
					class: "text-2xl text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded p-1 leading-none cursor-pointer dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700",
					onClick: f,
					"aria-label": "Close"
				}, " × ")) : a("", !0)])) : a("", !0),
				s("div", fr, [S(r.$slots, "default", {}, void 0, !0)]),
				r.$slots.footer ? (v(), o("div", pr, [S(r.$slots, "footer", {}, void 0, !0)])) : a("", !0)
			], 2)])) : a("", !0)]),
			_: 3
		})]));
	}
}), [["__scopeId", "data-v-ad28a034"]]), hr = { class: "flex flex-col" }, gr = {
	key: 0,
	class: "flex items-center justify-end px-4 py-2 border-b border-gray-100 dark:border-gray-700"
}, _r = { class: "overflow-y-auto max-h-96" }, vr = ["onClick"], yr = {
	key: 0,
	class: "absolute top-4 left-1.5 w-1.5 h-1.5 rounded-full bg-primary-500 dark:bg-primary-400"
}, br = { class: "flex-1 min-w-0" }, xr = {
	key: 0,
	class: "text-xs text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-2"
}, Sr = {
	key: 1,
	class: "text-[0.625rem] text-gray-400 dark:text-gray-500 mt-1 inline-block"
}, Cr = ["onClick"], wr = {
	key: 1,
	class: "flex flex-col items-center justify-center py-10 px-4"
}, Tr = { class: "text-sm text-gray-500 dark:text-gray-400" }, Er = /* @__PURE__ */ u({
	__name: "BaseNotificationList",
	props: {
		items: {},
		emptyText: { default: "No notifications" }
	},
	emits: [
		"click",
		"dismiss",
		"mark-all-read"
	],
	setup(t, { emit: n }) {
		let r = n;
		function c(e) {
			r("click", e);
		}
		function l(e, t) {
			t.stopPropagation(), r("dismiss", e);
		}
		function u() {
			r("mark-all-read");
		}
		let d = {
			primary: "text-primary-500 bg-primary-50 dark:text-primary-300 dark:bg-primary-900/30",
			success: "text-emerald-500 bg-emerald-50 dark:text-emerald-300 dark:bg-emerald-900/30",
			warning: "text-amber-500 bg-amber-50 dark:text-amber-300 dark:bg-amber-900/30",
			danger: "text-red-500 bg-red-50 dark:text-red-300 dark:bg-red-900/30",
			info: "text-cyan-500 bg-cyan-50 dark:text-cyan-300 dark:bg-cyan-900/30"
		};
		return (n, r) => (v(), o("div", hr, [t.items.some((e) => !e.read) ? (v(), o("div", gr, [s("button", {
			type: "button",
			class: "text-xs text-primary-600 hover:text-primary-700 font-medium cursor-pointer dark:text-primary-400 dark:hover:text-primary-300",
			onClick: u
		}, " Mark all as read ")])) : a("", !0), s("div", _r, [t.items.length ? (v(!0), o(e, { key: 0 }, x(t.items, (e) => (v(), o("div", {
			key: e.id,
			class: p(["relative flex items-start gap-3 px-4 py-3 transition-colors cursor-pointer border-b border-gray-50 last:border-b-0 dark:border-gray-700/50", {
				"bg-white dark:bg-gray-800": e.read,
				"bg-primary-50/30 dark:bg-primary-900/10": !e.read
			}]),
			onClick: (t) => c(e)
		}, [
			e.read ? a("", !0) : (v(), o("span", yr)),
			e.icon ? (v(), o("div", {
				key: 1,
				class: p(["flex items-center justify-center w-8 h-8 rounded-full shrink-0", d[e.variant || "primary"]])
			}, [(v(), i(w(e.icon), { class: "w-4 h-4" }))], 2)) : a("", !0),
			s("div", br, [
				s("p", { class: p(["text-sm truncate", {
					"font-semibold text-gray-900 dark:text-gray-100": !e.read,
					"font-medium text-gray-700 dark:text-gray-300": e.read
				}]) }, T(e.title), 3),
				e.description ? (v(), o("p", xr, T(e.description), 1)) : a("", !0),
				e.time ? (v(), o("span", Sr, T(e.time), 1)) : a("", !0)
			]),
			s("button", {
				type: "button",
				class: "shrink-0 p-1 rounded text-gray-400 hover:text-gray-600 hover:bg-gray-100 cursor-pointer dark:text-gray-500 dark:hover:text-gray-300 dark:hover:bg-gray-700",
				"aria-label": "Dismiss notification",
				onClick: (t) => l(e, t)
			}, [...r[0] ||= [s("svg", {
				class: "w-3.5 h-3.5",
				xmlns: "http://www.w3.org/2000/svg",
				viewBox: "0 0 20 20",
				fill: "currentColor"
			}, [s("path", { d: "M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" })], -1)]], 8, Cr)
		], 10, vr))), 128)) : (v(), o("div", wr, [r[1] ||= s("svg", {
			class: "w-10 h-10 text-gray-300 dark:text-gray-600 mb-3",
			xmlns: "http://www.w3.org/2000/svg",
			fill: "none",
			viewBox: "0 0 24 24",
			"stroke-width": "1.5",
			stroke: "currentColor"
		}, [s("path", {
			"stroke-linecap": "round",
			"stroke-linejoin": "round",
			d: "M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"
		})], -1), s("p", Tr, T(t.emptyText), 1)]))])]));
	}
}), Dr = {
	key: 0,
	class: "block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
}, Or = ["disabled"], kr = [
	"value",
	"min",
	"max",
	"step",
	"disabled",
	"aria-label"
], Ar = ["disabled"], jr = /* @__PURE__ */ u({
	__name: "BaseNumberInput",
	props: {
		modelValue: {},
		min: {},
		max: {},
		step: { default: 1 },
		size: { default: "md" },
		disabled: {
			type: Boolean,
			default: !1
		},
		label: {},
		variant: { default: "default" }
	},
	emits: ["update:modelValue"],
	setup(e, { emit: t }) {
		let n = e, i = t;
		function c(e) {
			let t = e;
			return n.min !== void 0 && (t = Math.max(t, n.min)), n.max !== void 0 && (t = Math.min(t, n.max)), t;
		}
		function l() {
			n.disabled || i("update:modelValue", c(n.modelValue + n.step));
		}
		function u() {
			n.disabled || i("update:modelValue", c(n.modelValue - n.step));
		}
		function d(e) {
			let t = e.target, n = Number(t.value);
			isNaN(n) || i("update:modelValue", c(n));
		}
		let f = r(() => n.min === void 0 || n.modelValue > n.min), m = r(() => n.max === void 0 || n.modelValue < n.max), h = {
			sm: {
				wrapper: "h-8",
				input: "text-xs px-2",
				button: "w-7 text-sm"
			},
			md: {
				wrapper: "h-10",
				input: "text-sm px-3",
				button: "w-9 text-base"
			},
			lg: {
				wrapper: "h-12",
				input: "text-base px-4",
				button: "w-11 text-lg"
			}
		}, g = {
			default: "border border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-900",
			filled: "border border-transparent bg-gray-100 dark:bg-gray-800"
		};
		return (t, n) => (v(), o("div", null, [e.label ? (v(), o("label", Dr, T(e.label), 1)) : a("", !0), s("div", { class: p(["inline-flex items-center rounded-lg overflow-hidden", [
			h[e.size].wrapper,
			g[e.variant],
			{ "opacity-50": e.disabled }
		]]) }, [
			s("button", {
				type: "button",
				class: p(["flex items-center justify-center h-full border-r border-gray-200 text-gray-500 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700/50 disabled:opacity-40 disabled:cursor-not-allowed", h[e.size].button]),
				disabled: e.disabled || !f.value,
				"aria-label": "Decrease value",
				onClick: u
			}, " − ", 10, Or),
			s("input", {
				type: "number",
				value: e.modelValue,
				min: e.min,
				max: e.max,
				step: e.step,
				disabled: e.disabled,
				class: p(["w-16 h-full text-center bg-transparent border-0 focus:outline-none focus:ring-0 text-gray-900 dark:text-gray-100 tabular-nums [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none", h[e.size].input]),
				"aria-label": e.label || "Number input",
				onInput: d
			}, null, 42, kr),
			s("button", {
				type: "button",
				class: p(["flex items-center justify-center h-full border-l border-gray-200 text-gray-500 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700/50 disabled:opacity-40 disabled:cursor-not-allowed", h[e.size].button]),
				disabled: e.disabled || !m.value,
				"aria-label": "Increase value",
				onClick: l
			}, " + ", 10, Ar)
		], 2)]));
	}
}), Mr = { class: "flex items-center justify-between gap-4 flex-wrap" }, Nr = { key: 1 }, Pr = {
	class: "flex items-center gap-1",
	"aria-label": "Pagination"
}, Fr = ["disabled"], Ir = ["onClick", "aria-current"], Lr = ["disabled"], Rr = ["disabled"], zr = ["disabled"], Br = ["disabled"], Vr = ["disabled"], Hr = ["disabled"], Ur = ["disabled"], Wr = ["disabled"], Gr = ["disabled"], Kr = ["max", "placeholder"], qr = /* @__PURE__ */ u({
	__name: "BasePagination",
	props: {
		currentPage: {},
		totalPages: {},
		totalItems: {},
		perPage: { default: 10 },
		variant: { default: "default" },
		size: { default: "md" },
		showInfo: {
			type: Boolean,
			default: !0
		},
		siblings: { default: 1 }
	},
	emits: ["update:currentPage"],
	setup(t, { emit: n }) {
		let i = t, u = n;
		function d(e) {
			e < 1 || e > i.totalPages || e === i.currentPage || u("update:currentPage", e);
		}
		let f = b("");
		function m() {
			let e = parseInt(f.value, 10);
			isNaN(e) || d(e), f.value = "";
		}
		let h = r(() => {
			let e = i.totalPages, t = i.currentPage, n = i.siblings, r = [], a = t - n > 2, o = t + n < e - 1;
			if (e <= n * 2 + 5) for (let t = 1; t <= e; t++) r.push(t);
			else {
				if (r.push(1), a) r.push("dots");
				else for (let e = 2; e <= t - n; e++) r.push(e);
				let i = Math.max(2, t - n), s = Math.min(e - 1, t + n);
				for (let e = i; e <= s; e++) r.push(e);
				if (o) r.push("dots");
				else for (let i = t + n + 1; i < e; i++) r.push(i);
				r.push(e);
			}
			return r;
		}), g = r(() => i.totalItems ? `${(i.currentPage - 1) * i.perPage + 1}–${Math.min(i.currentPage * i.perPage, i.totalItems)} of ${i.totalItems}` : ""), _ = r(() => ({
			sm: {
				btn: "w-7 h-7 text-xs",
				text: "text-xs"
			},
			md: {
				btn: "w-8 h-8 text-sm",
				text: "text-sm"
			},
			lg: {
				btn: "w-10 h-10 text-base",
				text: "text-sm"
			}
		})[i.size]);
		return (n, r) => (v(), o("div", Mr, [t.showInfo && t.totalItems ? (v(), o("p", {
			key: 0,
			class: p(["text-gray-500", _.value.text])
		}, T(g.value), 3)) : (v(), o("div", Nr)), s("nav", Pr, [
			t.variant === "default" ? (v(), o(e, { key: 0 }, [
				s("button", {
					disabled: t.currentPage === 1,
					class: p(["inline-flex items-center justify-center rounded-md border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors", _.value.btn]),
					onClick: r[0] ||= (e) => d(t.currentPage - 1),
					"aria-label": "Previous page"
				}, [l(E(K), { class: "w-4 h-4" })], 10, Fr),
				(v(!0), o(e, null, x(h.value, (n, r) => (v(), o(e, { key: r }, [n === "dots" ? (v(), o("span", {
					key: 0,
					class: p(["inline-flex items-center justify-center text-gray-400", _.value.btn])
				}, " … ", 2)) : (v(), o("button", {
					key: 1,
					class: p(["inline-flex items-center justify-center rounded-md font-medium cursor-pointer transition-colors", [_.value.btn, n === t.currentPage ? "bg-primary-500 text-white" : "text-gray-700 hover:bg-gray-100 border border-gray-200"]]),
					onClick: (e) => d(n),
					"aria-current": n === t.currentPage ? "page" : void 0
				}, T(n), 11, Ir))], 64))), 128)),
				s("button", {
					disabled: t.currentPage === t.totalPages,
					class: p(["inline-flex items-center justify-center rounded-md border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors", _.value.btn]),
					onClick: r[1] ||= (e) => d(t.currentPage + 1),
					"aria-label": "Next page"
				}, [l(E(q), { class: "w-4 h-4" })], 10, Lr)
			], 64)) : a("", !0),
			t.variant === "simple" ? (v(), o(e, { key: 1 }, [
				s("button", {
					disabled: t.currentPage === 1,
					class: p(["inline-flex items-center gap-1 px-3 py-1.5 rounded-md border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors", _.value.text]),
					onClick: r[2] ||= (e) => d(t.currentPage - 1)
				}, [l(E(K), { class: "w-4 h-4" }), r[11] ||= c(" Previous ", -1)], 10, Rr),
				s("span", { class: p(["px-3 text-gray-600 font-medium", _.value.text]) }, T(t.currentPage) + " / " + T(t.totalPages), 3),
				s("button", {
					disabled: t.currentPage === t.totalPages,
					class: p(["inline-flex items-center gap-1 px-3 py-1.5 rounded-md border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors", _.value.text]),
					onClick: r[3] ||= (e) => d(t.currentPage + 1)
				}, [r[12] ||= c(" Next ", -1), l(E(q), { class: "w-4 h-4" })], 10, zr)
			], 64)) : a("", !0),
			t.variant === "minimal" ? (v(), o(e, { key: 2 }, [
				s("button", {
					disabled: t.currentPage === 1,
					class: p(["inline-flex items-center justify-center rounded-md text-gray-600 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors", _.value.btn]),
					onClick: r[4] ||= (e) => d(1),
					"aria-label": "First page"
				}, [l(E(ee), { class: "w-4 h-4" })], 10, Br),
				s("button", {
					disabled: t.currentPage === 1,
					class: p(["inline-flex items-center justify-center rounded-md text-gray-600 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors", _.value.btn]),
					onClick: r[5] ||= (e) => d(t.currentPage - 1),
					"aria-label": "Previous page"
				}, [l(E(K), { class: "w-4 h-4" })], 10, Vr),
				s("span", { class: p(["px-2 text-gray-700 font-medium", _.value.text]) }, T(t.currentPage) + " of " + T(t.totalPages), 3),
				s("button", {
					disabled: t.currentPage === t.totalPages,
					class: p(["inline-flex items-center justify-center rounded-md text-gray-600 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors", _.value.btn]),
					onClick: r[6] ||= (e) => d(t.currentPage + 1),
					"aria-label": "Next page"
				}, [l(E(q), { class: "w-4 h-4" })], 10, Hr),
				s("button", {
					disabled: t.currentPage === t.totalPages,
					class: p(["inline-flex items-center justify-center rounded-md text-gray-600 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors", _.value.btn]),
					onClick: r[7] ||= (e) => d(t.totalPages),
					"aria-label": "Last page"
				}, [l(E(J), { class: "w-4 h-4" })], 10, Ur)
			], 64)) : a("", !0),
			t.variant === "jumper" ? (v(), o(e, { key: 3 }, [
				s("button", {
					disabled: t.currentPage === 1,
					class: p(["inline-flex items-center justify-center rounded-md border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors", _.value.btn]),
					onClick: r[8] ||= (e) => d(t.currentPage - 1),
					"aria-label": "Previous page"
				}, [l(E(K), { class: "w-4 h-4" })], 10, Wr),
				s("span", { class: p(["px-2 text-gray-700 font-medium", _.value.text]) }, " Page " + T(t.currentPage) + " of " + T(t.totalPages), 3),
				s("button", {
					disabled: t.currentPage === t.totalPages,
					class: p(["inline-flex items-center justify-center rounded-md border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors", _.value.btn]),
					onClick: r[9] ||= (e) => d(t.currentPage + 1),
					"aria-label": "Next page"
				}, [l(E(q), { class: "w-4 h-4" })], 10, Gr),
				r[13] ||= s("span", { class: "mx-2 text-gray-400" }, "|", -1),
				s("form", {
					class: "inline-flex items-center gap-1.5",
					onSubmit: N(m, ["prevent"])
				}, [
					s("label", { class: p(["text-gray-500", _.value.text]) }, "Go to", 2),
					j(s("input", {
						"onUpdate:modelValue": r[10] ||= (e) => f.value = e,
						type: "number",
						min: "1",
						max: t.totalPages,
						placeholder: String(t.currentPage),
						class: p(["w-14 px-2 py-1 border border-gray-200 rounded-md text-center outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-200", _.value.text]),
						onKeydown: M(m, ["enter"])
					}, null, 42, Kr), [[D, f.value]]),
					s("button", {
						type: "submit",
						class: p(["px-2 py-1 rounded-md bg-primary-500 text-white font-medium hover:bg-primary-600 cursor-pointer transition-colors", _.value.text])
					}, " Go ", 2)
				], 32)
			], 64)) : a("", !0)
		])]));
	}
}), Jr = /*#__PURE__*/ $(/* @__PURE__ */ u({
	__name: "BasePopover",
	props: {
		position: { default: "bottom" },
		align: { default: "center" },
		trigger: { default: "click" }
	},
	setup(e) {
		let t = e, r = b(!1), i = b(null);
		function c() {
			t.trigger === "click" && (r.value = !r.value);
		}
		function u() {
			t.trigger === "hover" && (r.value = !0);
		}
		function d() {
			t.trigger === "hover" && (r.value = !1);
		}
		function f(e) {
			i.value && !i.value.contains(e.target) && (r.value = !1);
		}
		g(() => {
			document.addEventListener("click", f);
		}), h(() => {
			document.removeEventListener("click", f);
		});
		let m = {
			top: {
				start: "bottom-full left-0 mb-2",
				center: "bottom-full left-1/2 -translate-x-1/2 mb-2",
				end: "bottom-full right-0 mb-2"
			},
			bottom: {
				start: "top-full left-0 mt-2",
				center: "top-full left-1/2 -translate-x-1/2 mt-2",
				end: "top-full right-0 mt-2"
			},
			left: {
				start: "right-full top-0 mr-2",
				center: "right-full top-1/2 -translate-y-1/2 mr-2",
				end: "right-full bottom-0 mr-2"
			},
			right: {
				start: "left-full top-0 ml-2",
				center: "left-full top-1/2 -translate-y-1/2 ml-2",
				end: "left-full bottom-0 ml-2"
			}
		};
		return (t, f) => (v(), o("div", {
			ref_key: "popoverRef",
			ref: i,
			class: "relative inline-block",
			onMouseenter: u,
			onMouseleave: d
		}, [s("div", { onClick: c }, [S(t.$slots, "default", {}, void 0, !0)]), l(n, { name: "popover" }, {
			default: A(() => [r.value ? (v(), o("div", {
				key: 0,
				class: p(["absolute z-50 min-w-[12rem] rounded-lg bg-white shadow-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700", m[e.position][e.align]]),
				role: "dialog"
			}, [S(t.$slots, "content", {}, void 0, !0)], 2)) : a("", !0)]),
			_: 3
		})], 544));
	}
}), [["__scopeId", "data-v-64113c81"]]), Yr = { class: "flex items-center gap-2" }, Xr = ["aria-valuenow", "aria-valuemax"], Zr = {
	key: 0,
	class: "text-xs font-medium text-gray-600 min-w-[2.5rem] text-right dark:text-gray-400"
}, Qr = /*#__PURE__*/ $(/* @__PURE__ */ u({
	__name: "BaseProgress",
	props: {
		value: {},
		max: { default: 100 },
		variant: { default: "primary" },
		size: { default: "md" },
		showLabel: {
			type: Boolean,
			default: !1
		},
		striped: {
			type: Boolean,
			default: !1
		},
		animated: {
			type: Boolean,
			default: !1
		}
	},
	setup(e) {
		let t = e, n = r(() => Math.min(Math.round(t.value / t.max * 100), 100)), i = {
			primary: "bg-primary-500",
			success: "bg-emerald-500",
			warning: "bg-amber-500",
			danger: "bg-red-500",
			info: "bg-cyan-500"
		}, c = {
			sm: "h-1",
			md: "h-2",
			lg: "h-3"
		};
		return (t, r) => (v(), o("div", Yr, [s("div", { class: p(["flex-1 bg-gray-200 rounded-full overflow-hidden dark:bg-gray-700", c[e.size]]) }, [s("div", {
			class: p(["h-full rounded-full transition-[width] duration-300", [
				i[e.variant],
				e.striped ? "progress-striped" : "",
				e.animated ? "progress-animated" : ""
			]]),
			style: m({ width: `${n.value}%` }),
			role: "progressbar",
			"aria-valuenow": e.value,
			"aria-valuemin": 0,
			"aria-valuemax": e.max
		}, null, 14, Xr)], 2), e.showLabel ? (v(), o("span", Zr, T(n.value) + "% ", 1)) : a("", !0)]));
	}
}), [["__scopeId", "data-v-7ef83fbe"]]), $r = [
	"checked",
	"value",
	"disabled"
], ei = /*#__PURE__*/ $(/* @__PURE__ */ u({
	__name: "BaseRadio",
	props: {
		modelValue: {},
		value: {},
		label: {},
		disabled: {
			type: Boolean,
			default: !1
		},
		variant: { default: "primary" },
		size: { default: "md" }
	},
	emits: ["update:modelValue"],
	setup(e, { emit: t }) {
		let i = e, c = t, u = r(() => i.modelValue === i.value);
		function d() {
			c("update:modelValue", i.value);
		}
		let f = {
			primary: "border-primary-500",
			success: "border-emerald-500",
			warning: "border-amber-500",
			danger: "border-red-500"
		}, m = {
			primary: "bg-primary-500",
			success: "bg-emerald-500",
			warning: "bg-amber-500",
			danger: "bg-red-500"
		}, h = {
			sm: {
				outer: "w-3.5 h-3.5",
				dot: "w-1.5 h-1.5",
				label: "text-xs"
			},
			md: {
				outer: "w-4 h-4",
				dot: "w-2 h-2",
				label: "text-sm"
			},
			lg: {
				outer: "w-5 h-5",
				dot: "w-2.5 h-2.5",
				label: "text-base"
			}
		}, g = r(() => [
			"relative shrink-0 rounded-full border-2 transition-colors duration-150 flex items-center justify-center",
			h[i.size].outer,
			u.value ? f[i.variant] : "border-gray-300 dark:border-gray-600"
		]);
		return (t, r) => (v(), o("label", { class: p(["inline-flex items-center gap-2", e.disabled ? "opacity-50 cursor-not-allowed" : "cursor-pointer"]) }, [
			s("input", {
				type: "radio",
				checked: u.value,
				value: e.value,
				disabled: e.disabled,
				class: "sr-only",
				onChange: d
			}, null, 40, $r),
			s("span", { class: p(g.value) }, [l(n, { name: "radio-dot" }, {
				default: A(() => [u.value ? (v(), o("span", {
					key: 0,
					class: p(["rounded-full", [h[e.size].dot, m[e.variant]]])
				}, null, 2)) : a("", !0)]),
				_: 1
			})], 2),
			e.label ? (v(), o("span", {
				key: 0,
				class: p(["text-gray-700 select-none dark:text-gray-300", h[e.size].label])
			}, T(e.label), 3)) : a("", !0)
		], 2));
	}
}), [["__scopeId", "data-v-ce82b26e"]]), ti = [
	"aria-valuenow",
	"aria-valuemax",
	"aria-label"
], ni = ["onMousemove"], ri = /* @__PURE__ */ u({
	__name: "BaseRating",
	props: {
		modelValue: {},
		max: { default: 5 },
		size: { default: "md" },
		readonly: {
			type: Boolean,
			default: !1
		},
		variant: { default: "star" }
	},
	emits: ["update:modelValue"],
	setup(t, { emit: n }) {
		let i = t, a = n, c = b(0), l = r(() => c.value > 0 ? c.value : i.modelValue);
		function u(e, t) {
			if (i.readonly) return;
			let n = t.target.getBoundingClientRect(), r = t.clientX - n.left < n.width / 2;
			c.value = r ? e + .5 : e + 1;
		}
		function d() {
			i.readonly || (c.value = 0);
		}
		function f() {
			i.readonly || a("update:modelValue", c.value);
		}
		let h = {
			sm: "w-4 h-4",
			md: "w-6 h-6",
			lg: "w-8 h-8"
		};
		return (n, r) => (v(), o("div", {
			class: p(["inline-flex items-center gap-0.5", {
				"cursor-pointer": !t.readonly,
				"cursor-default": t.readonly
			}]),
			role: "slider",
			"aria-valuenow": t.modelValue,
			"aria-valuemin": 0,
			"aria-valuemax": t.max,
			"aria-label": `Rating: ${t.modelValue} out of ${t.max}`,
			onMouseleave: d
		}, [(v(!0), o(e, null, x(t.max, (n) => (v(), o("span", {
			key: n,
			class: p(["relative inline-flex", h[t.size]]),
			onMousemove: (e) => u(n - 1, e),
			onClick: f
		}, [t.variant === "star" ? (v(), o(e, { key: 0 }, [(v(), o("svg", {
			class: p(["absolute inset-0 text-gray-300 dark:text-gray-600", h[t.size]]),
			xmlns: "http://www.w3.org/2000/svg",
			viewBox: "0 0 24 24",
			fill: "currentColor"
		}, [...r[0] ||= [s("path", {
			"fill-rule": "evenodd",
			d: "M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z",
			"clip-rule": "evenodd"
		}, null, -1)]], 2)), (v(), o("svg", {
			class: p(["absolute inset-0 text-amber-400", h[t.size]]),
			style: m({ clipPath: `inset(0 ${100 - Math.min((l.value - (n - 1)) * 100, 100)}% 0 0)` }),
			xmlns: "http://www.w3.org/2000/svg",
			viewBox: "0 0 24 24",
			fill: "currentColor"
		}, [...r[1] ||= [s("path", {
			"fill-rule": "evenodd",
			d: "M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z",
			"clip-rule": "evenodd"
		}, null, -1)]], 6))], 64)) : (v(), o(e, { key: 1 }, [(v(), o("svg", {
			class: p(["absolute inset-0 text-gray-300 dark:text-gray-600", h[t.size]]),
			xmlns: "http://www.w3.org/2000/svg",
			viewBox: "0 0 24 24",
			fill: "currentColor"
		}, [...r[2] ||= [s("path", { d: "M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" }, null, -1)]], 2)), (v(), o("svg", {
			class: p(["absolute inset-0 text-red-500", h[t.size]]),
			style: m({ clipPath: `inset(0 ${100 - Math.min((l.value - (n - 1)) * 100, 100)}% 0 0)` }),
			xmlns: "http://www.w3.org/2000/svg",
			viewBox: "0 0 24 24",
			fill: "currentColor"
		}, [...r[3] ||= [s("path", { d: "M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" }, null, -1)]], 6))], 64))], 42, ni))), 128))], 42, ti));
	}
}), ii = {
	key: 0,
	class: "text-sm font-medium text-gray-700 dark:text-gray-300"
}, ai = ["onClick"], oi = {
	key: 1,
	class: "text-sm text-gray-800 truncate dark:text-gray-200"
}, si = {
	key: 2,
	class: "text-sm text-gray-400 dark:text-gray-500"
}, ci = { class: "ml-auto flex items-center gap-1 shrink-0 pl-2" }, li = {
	key: 0,
	class: "flex items-center gap-2 px-3 py-2 border-b border-gray-100 dark:border-gray-700"
}, ui = ["placeholder"], di = { class: "max-h-60 overflow-y-auto py-1" }, fi = {
	key: 0,
	class: "px-3 py-3 text-sm text-gray-400 text-center"
}, pi = {
	key: 1,
	class: "px-3 py-3 text-sm text-gray-400 text-center"
}, mi = ["disabled", "onClick"], hi = {
	key: 0,
	class: "w-3 h-3 text-white",
	viewBox: "0 0 12 12",
	fill: "none"
}, gi = { class: "truncate" }, _i = {
	key: 1,
	class: "text-xs text-red-500"
}, vi = /* @__PURE__ */ u({
	__name: "BaseSelect",
	props: {
		modelValue: {},
		options: { default: () => [] },
		variant: { default: "default" },
		size: { default: "md" },
		placeholder: { default: "Select..." },
		disabled: {
			type: Boolean,
			default: !1
		},
		label: {},
		error: {},
		multiple: {
			type: Boolean,
			default: !1
		},
		searchable: {
			type: Boolean,
			default: !0
		},
		clearable: {
			type: Boolean,
			default: !0
		},
		remote: {
			type: Boolean,
			default: !1
		},
		remoteMethod: {},
		loadingText: { default: "Loading..." },
		noResultsText: { default: "No results found" }
	},
	emits: ["update:modelValue"],
	setup(u, { emit: d }) {
		let h = u, y = d, S = b(!1), C = b(""), w = b(!1), O = b([]), M = b(), P = b(), F = b(), I = b({});
		function L() {
			if (!P.value) return;
			let e = P.value.getBoundingClientRect();
			I.value = {
				position: "fixed",
				top: `${e.bottom + 4}px`,
				left: `${e.left}px`,
				width: `${e.width}px`,
				zIndex: "9999"
			};
		}
		let R = r(() => h.remote ? O.value : h.options), z = r(() => {
			if (h.remote || !C.value.trim()) return R.value;
			let e = C.value.toLowerCase();
			return R.value.filter((t) => t.label.toLowerCase().includes(e));
		}), B = r(() => h.modelValue == null ? [] : Array.isArray(h.modelValue) ? h.modelValue : h.modelValue === "" ? [] : [h.modelValue]), V = r(() => {
			let e = [...h.options, ...O.value];
			return B.value.map((t) => e.find((e) => e.value === t)).filter(Boolean);
		}), H = r(() => V.value.length === 0 || h.multiple ? "" : V.value[0]?.label || "");
		function U(e) {
			return B.value.includes(e.value);
		}
		function W(e) {
			if (!e.disabled) if (h.multiple) {
				let t = [...B.value], n = t.indexOf(e.value);
				n > -1 ? t.splice(n, 1) : t.push(e.value), y("update:modelValue", t);
			} else y("update:modelValue", e.value), S.value = !1, C.value = "";
		}
		function K(e) {
			if (h.multiple) {
				let t = B.value.filter((t) => t !== e);
				y("update:modelValue", t);
			}
		}
		function q() {
			y("update:modelValue", h.multiple ? [] : ""), C.value = "";
		}
		function ee() {
			h.disabled || (S.value = !0, L(), f(() => F.value?.focus()));
		}
		function J(e) {
			if (M.value && !M.value.contains(e.target)) {
				let t = document.getElementById("select-dropdown-portal");
				if (t && t.contains(e.target)) return;
				S.value = !1, C.value = "";
			}
		}
		function Y() {
			S.value && L();
		}
		let X;
		k(C, (e) => {
			if (!(!h.remote || !h.remoteMethod)) {
				if (clearTimeout(X), !e.trim()) {
					O.value = [];
					return;
				}
				X = setTimeout(async () => {
					w.value = !0;
					try {
						O.value = await h.remoteMethod(e);
					} catch {
						O.value = [];
					} finally {
						w.value = !1;
					}
				}, 300);
			}
		}), g(() => {
			document.addEventListener("click", J), window.addEventListener("scroll", Y, !0);
		}), _(() => {
			document.removeEventListener("click", J), window.removeEventListener("scroll", Y, !0);
		});
		let te = r(() => [
			"flex flex-wrap items-center gap-1 cursor-pointer transition-all duration-150",
			{
				default: "border border-gray-300 rounded-md bg-white focus-within:border-primary-500 focus-within:ring-2 focus-within:ring-primary-100 dark:bg-gray-800 dark:border-gray-600 dark:focus-within:ring-primary-900/30",
				filled: "border border-transparent rounded-md bg-gray-100 focus-within:bg-white focus-within:border-primary-500 dark:bg-gray-700 dark:focus-within:bg-gray-800",
				underlined: "border-b-2 border-gray-300 rounded-none focus-within:border-primary-500 dark:border-gray-600"
			}[h.variant],
			{
				sm: "min-h-[1.875rem]",
				md: "min-h-[2.375rem]",
				lg: "min-h-[2.875rem]"
			}[h.size],
			h.size === "sm" ? "px-2 py-1" : h.size === "lg" ? "px-3 py-2" : "px-2.5 py-1.5",
			h.error ? "border-red-500! focus-within:ring-red-100!" : "",
			h.disabled ? "opacity-50 cursor-not-allowed" : ""
		]);
		return (r, d) => (v(), o("div", {
			ref_key: "containerRef",
			ref: M,
			class: "flex flex-col gap-1"
		}, [
			u.label ? (v(), o("label", ii, T(u.label), 1)) : a("", !0),
			s("div", {
				ref_key: "triggerRef",
				ref: P,
				class: p(te.value),
				onClick: ee
			}, [
				u.multiple ? (v(!0), o(e, { key: 0 }, x(V.value, (e) => (v(), o("span", {
					key: e.value,
					class: "inline-flex items-center gap-1 bg-primary-50 text-primary-700 rounded px-1.5 py-0.5 text-xs font-medium dark:bg-primary-900/30 dark:text-primary-300"
				}, [c(T(e.label) + " ", 1), s("button", {
					class: "hover:text-primary-900 cursor-pointer dark:hover:text-primary-100",
					onClick: N((t) => K(e.value), ["stop"]),
					"aria-label": "Remove"
				}, [l(E(Q), { class: "w-3 h-3" })], 8, ai)]))), 128)) : a("", !0),
				!u.multiple && H.value ? (v(), o("span", oi, T(H.value), 1)) : a("", !0),
				B.value.length === 0 ? (v(), o("span", si, T(u.placeholder), 1)) : a("", !0),
				s("div", ci, [u.clearable && B.value.length > 0 && !u.disabled ? (v(), o("button", {
					key: 0,
					class: "text-gray-400 hover:text-gray-600 cursor-pointer dark:hover:text-gray-300",
					onClick: N(q, ["stop"]),
					"aria-label": "Clear"
				}, [l(E(Q), { class: "w-3.5 h-3.5" })])) : a("", !0), l(E(G), { class: p(["w-4 h-4 text-gray-400 transition-transform duration-150", { "rotate-180": S.value }]) }, null, 8, ["class"])])
			], 2),
			(v(), i(t, { to: "body" }, [l(n, {
				"enter-active-class": "transition duration-150 ease-out",
				"enter-from-class": "opacity-0 scale-95 -translate-y-1",
				"enter-to-class": "opacity-100 scale-100 translate-y-0",
				"leave-active-class": "transition duration-100 ease-in",
				"leave-from-class": "opacity-100 scale-100 translate-y-0",
				"leave-to-class": "opacity-0 scale-95 -translate-y-1"
			}, {
				default: A(() => [S.value ? (v(), o("div", {
					key: 0,
					id: "select-dropdown-portal",
					style: m(I.value),
					class: "bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden dark:bg-gray-800 dark:border-gray-700"
				}, [u.searchable ? (v(), o("div", li, [
					l(E(Ce), { class: "w-4 h-4 text-gray-400 shrink-0 dark:text-gray-500" }),
					j(s("input", {
						ref_key: "searchInputRef",
						ref: F,
						"onUpdate:modelValue": d[0] ||= (e) => C.value = e,
						type: "text",
						class: "flex-1 text-sm border-none outline-none bg-transparent placeholder:text-gray-400 dark:text-gray-200 dark:placeholder:text-gray-500",
						placeholder: u.remote ? "Type to search..." : "Search...",
						onClick: d[1] ||= N(() => {}, ["stop"])
					}, null, 8, ui), [[D, C.value]]),
					w.value ? (v(), i(E(ge), {
						key: 0,
						class: "w-4 h-4 text-gray-400 animate-spin shrink-0"
					})) : a("", !0)
				])) : a("", !0), s("div", di, [w.value && z.value.length === 0 ? (v(), o("div", fi, T(u.loadingText), 1)) : z.value.length === 0 ? (v(), o("div", pi, T(u.remote && !C.value ? "Type to search..." : u.noResultsText), 1)) : a("", !0), (v(!0), o(e, null, x(z.value, (e) => (v(), o("button", {
					key: e.value,
					class: p(["w-full flex items-center gap-2 px-3 py-2 text-sm text-left transition-colors duration-75", [e.disabled ? "opacity-40 cursor-not-allowed" : "cursor-pointer", U(e) ? "bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300" : "text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700"]]),
					disabled: e.disabled,
					onClick: N((t) => W(e), ["stop"])
				}, [u.multiple ? (v(), o("span", {
					key: 0,
					class: p(["w-4 h-4 border rounded flex items-center justify-center shrink-0 transition-colors", U(e) ? "bg-primary-500 border-primary-500" : "border-gray-300 dark:border-gray-600"])
				}, [U(e) ? (v(), o("svg", hi, [...d[2] ||= [s("path", {
					d: "M2 6l3 3 5-5",
					stroke: "currentColor",
					"stroke-width": "2",
					"stroke-linecap": "round",
					"stroke-linejoin": "round"
				}, null, -1)]])) : a("", !0)], 2)) : a("", !0), s("span", gi, T(e.label), 1)], 10, mi))), 128))])], 4)) : a("", !0)]),
				_: 1
			})])),
			u.error ? (v(), o("span", _i, T(u.error), 1)) : a("", !0)
		], 512));
	}
}), yi = {
	key: 1,
	class: "space-y-2"
}, bi = /* @__PURE__ */ u({
	__name: "BaseSkeleton",
	props: {
		variant: { default: "text" },
		width: {},
		height: {},
		rounded: {
			type: Boolean,
			default: !0
		},
		count: { default: 1 }
	},
	setup(t) {
		let n = t, i = r(() => ["animate-pulse bg-gray-200 dark:bg-gray-700", {
			text: `h-4 ${n.rounded ? "rounded" : ""} w-full`,
			circle: "rounded-full w-10 h-10",
			rect: `${n.rounded ? "rounded-lg" : ""} w-full h-24`,
			button: "rounded-md h-9 w-24",
			avatar: "rounded-full w-10 h-10",
			input: "rounded-md h-10 w-full",
			badge: "rounded-full h-5 w-16"
		}[n.variant]]), a = r(() => {
			let e = {};
			return n.width && (e.width = n.width), n.height && (e.height = n.height), e;
		});
		return (n, r) => t.count === 1 ? (v(), o("div", {
			key: 0,
			class: p(i.value),
			style: m(a.value)
		}, null, 6)) : (v(), o("div", yi, [(v(!0), o(e, null, x(t.count, (e) => (v(), o("div", {
			key: e,
			class: p(i.value),
			style: m(a.value)
		}, null, 6))), 128))]));
	}
}), xi = { class: "flex items-center gap-3" }, Si = { class: "relative flex-1" }, Ci = { class: "absolute inset-y-0 flex items-center w-full pointer-events-none" }, wi = { class: "w-full h-2 rounded-full bg-gray-200 dark:bg-gray-700" }, Ti = [
	"value",
	"min",
	"max",
	"step",
	"disabled",
	"aria-valuenow",
	"aria-valuemin",
	"aria-valuemax"
], Ei = {
	key: 0,
	class: "text-sm font-medium text-gray-600 min-w-[2.5rem] text-right tabular-nums dark:text-gray-400"
}, Di = /* @__PURE__ */ u({
	__name: "BaseSlider",
	props: {
		modelValue: {},
		min: { default: 0 },
		max: { default: 100 },
		step: { default: 1 },
		disabled: {
			type: Boolean,
			default: !1
		},
		showValue: {
			type: Boolean,
			default: !1
		},
		variant: { default: "primary" }
	},
	emits: ["update:modelValue"],
	setup(e, { emit: t }) {
		let n = e, i = t, c = r(() => (n.modelValue - n.min) / (n.max - n.min) * 100), l = {
			primary: "text-primary-500",
			success: "text-emerald-500",
			warning: "text-amber-500",
			danger: "text-red-500"
		}, u = {
			primary: "[&::-webkit-slider-thumb]:bg-primary-500 [&::-moz-range-thumb]:bg-primary-500",
			success: "[&::-webkit-slider-thumb]:bg-emerald-500 [&::-moz-range-thumb]:bg-emerald-500",
			warning: "[&::-webkit-slider-thumb]:bg-amber-500 [&::-moz-range-thumb]:bg-amber-500",
			danger: "[&::-webkit-slider-thumb]:bg-red-500 [&::-moz-range-thumb]:bg-red-500"
		};
		function d(e) {
			let t = e.target;
			i("update:modelValue", Number(t.value));
		}
		return (t, n) => (v(), o("div", xi, [s("div", Si, [s("div", Ci, [s("div", wi, [s("div", {
			class: p(["h-full rounded-full transition-[width] duration-100", l[e.variant].replace("text-", "bg-")]),
			style: m({ width: `${c.value}%` })
		}, null, 6)])]), s("input", {
			type: "range",
			value: e.modelValue,
			min: e.min,
			max: e.max,
			step: e.step,
			disabled: e.disabled,
			class: p(["relative w-full h-6 appearance-none bg-transparent cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-5 [&::-webkit-slider-thumb]:h-5 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:shadow-md [&::-webkit-slider-thumb]:ring-2 [&::-webkit-slider-thumb]:ring-white [&::-webkit-slider-thumb]:dark:ring-gray-800 [&::-webkit-slider-thumb]:transition-transform [&::-webkit-slider-thumb]:hover:scale-110 [&::-moz-range-thumb]:w-5 [&::-moz-range-thumb]:h-5 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:border-0 [&::-moz-range-thumb]:shadow-md [&::-moz-range-thumb]:ring-2 [&::-moz-range-thumb]:ring-white [&::-moz-range-thumb]:dark:ring-gray-800 [&::-moz-range-track]:bg-transparent [&::-webkit-slider-runnable-track]:bg-transparent", u[e.variant]]),
			"aria-valuenow": e.modelValue,
			"aria-valuemin": e.min,
			"aria-valuemax": e.max,
			"aria-label": "Slider",
			onInput: d
		}, null, 42, Ti)]), e.showValue ? (v(), o("span", Ei, T(e.modelValue), 1)) : a("", !0)]));
	}
}), Oi = ["aria-label"], ki = { class: "sr-only" }, Ai = /* @__PURE__ */ u({
	__name: "BaseSpinner",
	props: {
		size: { default: "md" },
		variant: { default: "primary" },
		label: { default: "Loading" }
	},
	setup(e) {
		let t = {
			xs: "w-3 h-3",
			sm: "w-4 h-4",
			md: "w-6 h-6",
			lg: "w-8 h-8",
			xl: "w-12 h-12"
		}, n = {
			primary: "text-primary-500",
			white: "text-white",
			gray: "text-gray-400 dark:text-gray-500"
		};
		return (r, i) => (v(), o("div", {
			role: "status",
			"aria-label": e.label
		}, [(v(), o("svg", {
			class: p(["animate-spin", [t[e.size], n[e.variant]]]),
			xmlns: "http://www.w3.org/2000/svg",
			fill: "none",
			viewBox: "0 0 24 24"
		}, [...i[0] ||= [s("circle", {
			class: "opacity-25",
			cx: "12",
			cy: "12",
			r: "10",
			stroke: "currentColor",
			"stroke-width": "4"
		}, null, -1), s("path", {
			class: "opacity-75",
			fill: "currentColor",
			d: "M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
		}, null, -1)]], 2)), s("span", ki, T(e.label), 1)], 8, Oi));
	}
}), ji = {
	"aria-label": "Steps",
	class: "w-full"
}, Mi = { class: "flex items-center w-full" }, Ni = [
	"disabled",
	"aria-current",
	"aria-label",
	"onClick"
], Pi = {
	key: 0,
	class: "w-4 h-4",
	xmlns: "http://www.w3.org/2000/svg",
	viewBox: "0 0 20 20",
	fill: "currentColor"
}, Fi = { key: 1 }, Ii = {
	key: 2,
	class: "text-[0.625rem] text-gray-400 dark:text-gray-500 text-center"
}, Li = /* @__PURE__ */ u({
	__name: "BaseSteps",
	props: {
		steps: {},
		current: {},
		variant: { default: "default" },
		clickable: {
			type: Boolean,
			default: !1
		}
	},
	emits: ["step-click"],
	setup(t, { emit: n }) {
		let r = n;
		function i(e, t) {
			t && r("step-click", e);
		}
		return (n, r) => (v(), o("nav", ji, [s("ol", Mi, [(v(!0), o(e, null, x(t.steps, (e, n) => (v(), o("li", {
			key: n,
			class: p(["flex items-center", { "flex-1": n < t.steps.length - 1 }])
		}, [s("button", {
			type: "button",
			class: p(["flex flex-col items-center gap-1", {
				"cursor-pointer": t.clickable,
				"cursor-default": !t.clickable
			}]),
			disabled: !t.clickable,
			"aria-current": n === t.current ? "step" : void 0,
			"aria-label": `Step ${n + 1}: ${e.title}`,
			onClick: (e) => i(n, t.clickable)
		}, [
			t.variant === "dots" ? (v(), o("span", {
				key: 0,
				class: p(["w-3 h-3 rounded-full transition-colors", {
					"bg-primary-500 dark:bg-primary-400": n <= t.current,
					"bg-gray-300 dark:bg-gray-600": n > t.current
				}])
			}, null, 2)) : (v(), o("span", {
				key: 1,
				class: p(["flex items-center justify-center w-8 h-8 rounded-full text-sm font-medium transition-colors shrink-0", {
					"bg-primary-500 text-white dark:bg-primary-400": n < t.current,
					"border-2 border-primary-500 text-primary-500 dark:border-primary-400 dark:text-primary-400": n === t.current,
					"border-2 border-gray-300 text-gray-400 dark:border-gray-600 dark:text-gray-500": n > t.current
				}])
			}, [n < t.current && t.variant !== "numbered" ? (v(), o("svg", Pi, [...r[0] ||= [s("path", {
				"fill-rule": "evenodd",
				d: "M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z",
				"clip-rule": "evenodd"
			}, null, -1)]])) : (v(), o("span", Fi, T(n + 1), 1))], 2)),
			s("span", { class: p(["text-xs font-medium text-center whitespace-nowrap", {
				"text-primary-600 dark:text-primary-400": n <= t.current,
				"text-gray-500 dark:text-gray-400": n > t.current
			}]) }, T(e.title), 3),
			e.description && t.variant !== "dots" ? (v(), o("span", Ii, T(e.description), 1)) : a("", !0)
		], 10, Ni), n < t.steps.length - 1 ? (v(), o("div", {
			key: 0,
			class: p(["flex-1 h-px mx-3", {
				"bg-primary-500 dark:bg-primary-400": n < t.current,
				"bg-gray-200 dark:bg-gray-700": n >= t.current
			}])
		}, null, 2)) : a("", !0)], 2))), 128))])]));
	}
}), Ri = {
	key: 0,
	class: "flex flex-wrap items-center justify-between gap-3 px-4 py-3 border-b border-gray-200 dark:border-gray-700"
}, zi = {
	key: 0,
	class: "relative"
}, Bi = ["placeholder"], Vi = {
	key: 0,
	class: "absolute right-2 top-1/2 -translate-y-1/2 text-xs text-gray-400"
}, Hi = {
	key: 1,
	class: "flex items-center gap-2"
}, Ui = { class: "overflow-x-auto" }, Wi = { class: "w-full text-sm" }, Gi = { class: "bg-gray-50 dark:bg-gray-800" }, Ki = ["onClick"], qi = { class: "inline-flex items-center gap-1" }, Ji = {
	key: 2,
	class: "w-3 h-3 opacity-0 group-hover:opacity-30"
}, Yi = ["onClick"], Xi = { key: 0 }, Zi = ["colspan"], Qi = { class: "px-4 py-3" }, $i = { class: "text-xs text-gray-600 dark:text-gray-400" }, ea = { key: 0 }, ta = ["colspan"], na = /* @__PURE__ */ u({
	__name: "BaseTable",
	props: {
		columns: {},
		data: {},
		variant: { default: "default" },
		hoverable: {
			type: Boolean,
			default: !0
		},
		compact: {
			type: Boolean,
			default: !1
		},
		searchable: {
			type: Boolean,
			default: !1
		},
		searchPlaceholder: { default: "Search..." },
		expandable: {
			type: Boolean,
			default: !1
		},
		sortColumn: {},
		sortDirection: { default: null }
	},
	emits: ["sort"],
	setup(t, { emit: n }) {
		let l = t, u = n, d = b(""), f = b(/* @__PURE__ */ new Set()), h = r(() => l.compact ? "px-3 py-2" : "px-4 py-3"), g = b(l.sortColumn || null), _ = b(l.sortDirection), y = r(() => l.sortColumn ?? g.value), C = r(() => l.sortDirection ?? _.value);
		function O(e) {
			if (!e.sortable) return;
			let t;
			t = y.value === e.key ? C.value === "asc" ? "desc" : C.value === "desc" ? null : "asc" : "asc", g.value = t ? e.key : null, _.value = t, u("sort", e.key, t);
		}
		let k = r(() => {
			let e = [...l.data];
			if (d.value.trim()) {
				let t = d.value.toLowerCase();
				e = e.filter((e) => l.columns.some((n) => {
					let r = e[n.key];
					return r != null && String(r).toLowerCase().includes(t);
				}));
			}
			if (y.value && C.value) {
				let t = y.value, n = C.value === "asc" ? 1 : -1;
				e.sort((e, r) => {
					let i = e[t] ?? "", a = r[t] ?? "";
					return typeof i == "number" && typeof a == "number" ? (i - a) * n : String(i).localeCompare(String(a)) * n;
				});
			}
			return e;
		});
		function A(e) {
			f.value.has(e) ? f.value.delete(e) : f.value.add(e);
		}
		function M(e) {
			return f.value.has(e);
		}
		return (n, r) => (v(), o("div", null, [t.searchable || n.$slots.toolbar ? (v(), o("div", Ri, [t.searchable ? (v(), o("div", zi, [j(s("input", {
			"onUpdate:modelValue": r[0] ||= (e) => d.value = e,
			type: "text",
			placeholder: t.searchPlaceholder,
			class: "w-64 px-3 py-2 text-sm border border-gray-300 rounded-md bg-white outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 placeholder:text-gray-400 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:placeholder:text-gray-500 dark:focus:ring-primary-900/30"
		}, null, 8, Bi), [[D, d.value]]), d.value ? (v(), o("span", Vi, T(k.value.length) + " result" + T(k.value.length === 1 ? "" : "s"), 1)) : a("", !0)])) : a("", !0), n.$slots.toolbar ? (v(), o("div", Hi, [S(n.$slots, "toolbar")])) : a("", !0)])) : a("", !0), s("div", Ui, [s("table", Wi, [s("thead", Gi, [s("tr", null, [t.expandable ? (v(), o("th", {
			key: 0,
			class: p([h.value, "w-10 border-b border-gray-200 dark:border-gray-700"])
		}, null, 2)) : a("", !0), (v(!0), o(e, null, x(t.columns, (t) => (v(), o("th", {
			key: t.key,
			style: m({ width: t.width }),
			class: p([
				h.value,
				"font-semibold text-gray-600 uppercase text-[0.6875rem] tracking-wide border-b border-gray-200 dark:border-gray-700 dark:text-gray-400",
				t.align === "center" ? "text-center" : t.align === "right" ? "text-right" : "text-left",
				t.sortable ? "cursor-pointer select-none hover:text-gray-900 transition-colors dark:hover:text-gray-200" : ""
			]),
			onClick: (e) => O(t)
		}, [s("div", qi, [c(T(t.label) + " ", 1), t.sortable ? (v(), o(e, { key: 0 }, [y.value === t.key && C.value === "asc" ? (v(), i(E(V), {
			key: 0,
			class: "w-3 h-3 text-primary-500"
		})) : y.value === t.key && C.value === "desc" ? (v(), i(E(B), {
			key: 1,
			class: "w-3 h-3 text-primary-500"
		})) : (v(), o("span", Ji, "↕"))], 64)) : a("", !0)])], 14, Ki))), 128))])]), s("tbody", null, [(v(!0), o(e, null, x(k.value, (r, l) => (v(), o(e, { key: l }, [s("tr", {
			class: p([
				t.hoverable ? "hover:bg-gray-50 dark:hover:bg-gray-700/50" : "",
				t.variant === "striped" && l % 2 == 1 ? "bg-gray-50 dark:bg-gray-800/50" : "",
				t.expandable ? "cursor-pointer" : ""
			]),
			onClick: (e) => t.expandable ? A(l) : void 0
		}, [t.expandable ? (v(), o("td", {
			key: 0,
			class: p([h.value, "border-b border-gray-100 w-10 text-gray-400 dark:border-gray-700"])
		}, [(v(), i(w(M(l) ? E(G) : E(q)), { class: "w-4 h-4 transition-transform duration-150" }))], 2)) : a("", !0), (v(!0), o(e, null, x(t.columns, (e) => (v(), o("td", {
			key: e.key,
			class: p([
				h.value,
				"border-b border-gray-100 text-gray-700 dark:border-gray-700 dark:text-gray-300",
				t.variant === "bordered" ? "border border-gray-200 dark:border-gray-700" : "",
				e.align === "center" ? "text-center" : e.align === "right" ? "text-right" : "text-left"
			])
		}, [S(n.$slots, `cell-${e.key}`, {
			row: r,
			value: r[e.key],
			index: l
		}, () => [c(T(r[e.key]), 1)])], 2))), 128))], 10, Yi), t.expandable && M(l) ? (v(), o("tr", Xi, [s("td", {
			colspan: t.columns.length + 1,
			class: "bg-gray-50 border-b border-gray-100 dark:bg-gray-800/50 dark:border-gray-700"
		}, [s("div", Qi, [S(n.$slots, "expanded", {
			row: r,
			index: l
		}, () => [s("pre", $i, T(JSON.stringify(r, null, 2)), 1)])])], 8, Zi)])) : a("", !0)], 64))), 128)), k.value.length === 0 ? (v(), o("tr", ea, [s("td", {
			colspan: t.columns.length + +!!t.expandable,
			class: "px-4 py-8 text-center text-gray-400 dark:text-gray-500"
		}, [S(n.$slots, "empty", {}, () => [c(T(d.value ? "No results found." : "No data available."), 1)])], 8, ta)])) : a("", !0)])])])]));
	}
}), ra = Symbol("tab-active"), ia = [
	"disabled",
	"aria-selected",
	"onClick"
], aa = /* @__PURE__ */ u({
	__name: "BaseTabs",
	props: {
		modelValue: {},
		tabs: {},
		variant: { default: "default" },
		placement: { default: "top" },
		size: { default: "md" },
		fullWidth: {
			type: Boolean,
			default: !1
		}
	},
	emits: ["update:modelValue"],
	setup(t, { emit: n }) {
		let c = t, l = n, u = r({
			get: () => c.modelValue || c.tabs[0]?.key || "",
			set: (e) => l("update:modelValue", e)
		});
		function d(e) {
			e.disabled || (u.value = e.key);
		}
		y(ra, u);
		let f = r(() => c.placement === "left" || c.placement === "right"), m = r(() => f.value ? c.placement === "left" ? "flex flex-row" : "flex flex-row-reverse" : c.placement === "bottom" ? "flex flex-col-reverse" : "flex flex-col"), h = r(() => {
			let e = "flex shrink-0";
			return f.value ? [
				e,
				"flex-col",
				c.placement === "left" ? "border-r border-gray-200 pr-0" : "border-l border-gray-200 pl-0"
			] : [
				e,
				"flex-row",
				(c.fullWidth, "")
			];
		}), g = r(() => (e) => {
			let t = u.value === e.key, n = {
				sm: "px-3 py-1.5 text-xs",
				md: "px-4 py-2 text-sm",
				lg: "px-5 py-2.5 text-base"
			}, r = {
				default: {
					active: "text-primary-600 border-primary-500 dark:text-primary-400",
					inactive: "text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200"
				},
				pills: {
					active: "bg-primary-500 text-white rounded-md",
					inactive: "text-gray-600 hover:bg-gray-100 rounded-md dark:text-gray-400 dark:hover:bg-gray-700"
				},
				underline: {
					active: "text-primary-600 border-primary-500 dark:text-primary-400",
					inactive: "text-gray-500 border-transparent hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
				},
				bordered: {
					active: "bg-white text-primary-600 border border-gray-200 border-b-white rounded-t-md -mb-px dark:bg-gray-800 dark:border-gray-700 dark:border-b-gray-800",
					inactive: "text-gray-500 border border-transparent hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
				}
			}, i = "";
			(c.variant === "default" || c.variant === "underline") && (i = f.value ? c.placement === "left" ? "border-r-2" : "border-l-2" : c.placement === "bottom" ? "border-t-2" : "border-b-2");
			let a = e.disabled ? "opacity-40 cursor-not-allowed!" : "";
			return [
				"inline-flex items-center gap-2 font-medium transition-all duration-150 whitespace-nowrap cursor-pointer select-none",
				n[c.size],
				i,
				t ? r[c.variant].active : r[c.variant].inactive,
				c.fullWidth && !f.value ? "flex-1 justify-center" : "",
				a
			];
		}), _ = r(() => c.variant === "pills" ? "" : c.variant === "bordered" ? f.value ? "" : "border-b border-gray-200 dark:border-gray-700" : f.value ? "" : c.placement === "bottom" ? "border-t border-gray-200 dark:border-gray-700" : "border-b border-gray-200 dark:border-gray-700");
		return (n, r) => (v(), o("div", { class: p(m.value) }, [s("nav", {
			class: p([h.value, _.value]),
			role: "tablist"
		}, [(v(!0), o(e, null, x(t.tabs, (e) => (v(), o("button", {
			key: e.key,
			class: p(g.value(e)),
			disabled: e.disabled,
			role: "tab",
			"aria-selected": u.value === e.key,
			onClick: (t) => d(e)
		}, [e.icon ? (v(), i(w(e.icon), {
			key: 0,
			class: "w-4 h-4 shrink-0"
		})) : a("", !0), s("span", null, T(e.label), 1)], 10, ia))), 128))], 2), s("div", { class: p(["flex-1 min-w-0", f.value ? t.placement === "left" ? "pl-4" : "pr-4" : "pt-4"]) }, [S(n.$slots, "default")], 2)], 2));
	}
}), oa = /* @__PURE__ */ u({
	__name: "BaseTag",
	props: {
		label: {},
		variant: { default: "primary" },
		size: { default: "md" },
		removable: {
			type: Boolean,
			default: !1
		},
		rounded: {
			type: Boolean,
			default: !0
		}
	},
	emits: ["remove"],
	setup(e) {
		let t = e, n = {
			primary: "bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-300",
			secondary: "bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300",
			success: "bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-300",
			warning: "bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-300",
			danger: "bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-300",
			info: "bg-cyan-50 text-cyan-600 dark:bg-cyan-900/30 dark:text-cyan-300"
		}, i = {
			sm: "px-2 py-0.5 text-[0.6875rem]",
			md: "px-2.5 py-0.5 text-xs",
			lg: "px-3 py-1 text-sm"
		}, l = r(() => [
			"inline-flex items-center gap-1 font-medium",
			n[t.variant],
			i[t.size],
			t.rounded ? "rounded-full" : "rounded"
		]);
		return (t, n) => (v(), o("span", { class: p(l.value) }, [c(T(e.label) + " ", 1), e.removable ? (v(), o("button", {
			key: 0,
			type: "button",
			class: "inline-flex items-center justify-center w-3.5 h-3.5 rounded-full hover:bg-current/10 transition-colors cursor-pointer",
			"aria-label": "Remove tag",
			onClick: n[0] ||= (e) => t.$emit("remove")
		}, [...n[1] ||= [s("svg", {
			class: "w-2.5 h-2.5",
			xmlns: "http://www.w3.org/2000/svg",
			viewBox: "0 0 20 20",
			fill: "currentColor"
		}, [s("path", { d: "M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" })], -1)]])) : a("", !0)], 2));
	}
}), sa = { class: "flex flex-col gap-1" }, ca = {
	key: 0,
	class: "text-sm font-medium text-gray-700 dark:text-gray-300"
}, la = [
	"value",
	"placeholder",
	"rows",
	"disabled",
	"maxlength",
	"aria-invalid"
], ua = { class: "flex items-center justify-between" }, da = {
	key: 0,
	class: "text-xs text-red-500"
}, fa = {
	key: 1,
	class: "text-xs text-gray-500 dark:text-gray-400"
}, pa = { key: 2 }, ma = {
	key: 3,
	class: "text-xs text-gray-400 dark:text-gray-500"
}, ha = /* @__PURE__ */ u({
	__name: "BaseTextarea",
	props: {
		modelValue: {},
		label: {},
		placeholder: {},
		rows: { default: 3 },
		variant: { default: "default" },
		size: { default: "md" },
		disabled: {
			type: Boolean,
			default: !1
		},
		error: {},
		hint: {},
		autosize: {
			type: Boolean,
			default: !1
		},
		maxlength: {}
	},
	emits: ["update:modelValue"],
	setup(e, { emit: t }) {
		let n = e, i = t, c = b(null), l = {
			default: "border border-gray-300 rounded-md bg-white focus-within:border-primary-500 focus-within:ring-2 focus-within:ring-primary-100 dark:bg-gray-800 dark:border-gray-600 dark:focus-within:ring-primary-900/30",
			filled: "border border-transparent rounded-md bg-gray-100 focus-within:bg-white focus-within:border-primary-500 dark:bg-gray-700 dark:focus-within:bg-gray-800",
			underlined: "border-b-2 border-gray-300 rounded-none bg-transparent focus-within:border-primary-500 dark:border-gray-600"
		}, u = {
			sm: "px-2 py-1 text-xs",
			md: "px-3 py-2 text-sm",
			lg: "px-4 py-3 text-base"
		}, d = r(() => [
			"transition-all duration-150",
			l[n.variant],
			u[n.size],
			n.error ? "border-red-500! focus-within:ring-red-100!" : "",
			n.disabled ? "opacity-50 cursor-not-allowed" : ""
		]);
		function m(e) {
			let t = e.target.value;
			i("update:modelValue", t);
		}
		function h() {
			!n.autosize || !c.value || (c.value.style.height = "auto", c.value.style.height = `${c.value.scrollHeight}px`);
		}
		return k(() => n.modelValue, () => {
			n.autosize && f(h);
		}), (t, n) => (v(), o("div", sa, [
			e.label ? (v(), o("label", ca, T(e.label), 1)) : a("", !0),
			s("div", { class: p(d.value) }, [s("textarea", {
				ref_key: "textareaRef",
				ref: c,
				value: e.modelValue,
				placeholder: e.placeholder,
				rows: e.rows,
				disabled: e.disabled,
				maxlength: e.maxlength,
				class: p(["w-full border-none outline-none bg-transparent text-gray-800 font-sans placeholder:text-gray-400 resize-y dark:text-gray-200 dark:placeholder:text-gray-500", { "resize-none": e.autosize }]),
				"aria-invalid": e.error ? "true" : void 0,
				onInput: m,
				onFocus: h
			}, null, 42, la)], 2),
			s("div", ua, [e.error ? (v(), o("span", da, T(e.error), 1)) : e.hint ? (v(), o("span", fa, T(e.hint), 1)) : (v(), o("span", pa)), e.maxlength ? (v(), o("span", ma, T(e.modelValue.length) + "/" + T(e.maxlength), 1)) : a("", !0)])
		]));
	}
}), ga = { class: "text-sm font-medium text-gray-900 dark:text-gray-100" }, _a = {
	key: 0,
	class: "inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500 dark:bg-gray-800 dark:text-gray-400"
}, va = {
	key: 0,
	class: "mt-1 text-sm text-gray-500 dark:text-gray-400"
}, ya = /* @__PURE__ */ u({
	__name: "BaseTimeline",
	props: {
		items: {},
		variant: { default: "default" },
		lineVariant: { default: "solid" }
	},
	setup(t) {
		let n = {
			primary: "bg-primary-500 dark:bg-primary-400",
			success: "bg-emerald-500 dark:bg-emerald-400",
			warning: "bg-amber-500 dark:bg-amber-400",
			danger: "bg-red-500 dark:bg-red-400",
			info: "bg-cyan-500 dark:bg-cyan-400"
		}, r = {
			primary: "text-primary-500 bg-primary-50 dark:text-primary-300 dark:bg-primary-900/30",
			success: "text-emerald-500 bg-emerald-50 dark:text-emerald-300 dark:bg-emerald-900/30",
			warning: "text-amber-500 bg-amber-50 dark:text-amber-300 dark:bg-amber-900/30",
			danger: "text-red-500 bg-red-50 dark:text-red-300 dark:bg-red-900/30",
			info: "text-cyan-500 bg-cyan-50 dark:text-cyan-300 dark:bg-cyan-900/30"
		};
		return (c, l) => (v(), o("div", {
			class: p(["relative", {
				"space-y-6": t.variant !== "compact",
				"space-y-3": t.variant === "compact"
			}]),
			role: "list",
			"aria-label": "Timeline"
		}, [(v(!0), o(e, null, x(t.items, (e, c) => (v(), o("div", {
			key: c,
			class: p(["relative flex", { "flex-row-reverse text-right": t.variant === "alternate" && c % 2 != 0 }]),
			role: "listitem"
		}, [
			c < t.items.length - 1 ? (v(), o("div", {
				key: 0,
				class: p(["absolute top-6 w-px h-full", [t.variant === "alternate" ? "left-1/2 -translate-x-1/2" : "left-3", t.lineVariant === "dashed" ? "border-l-2 border-dashed border-gray-200 dark:border-gray-700" : "bg-gray-200 dark:bg-gray-700"]])
			}, null, 2)) : a("", !0),
			s("div", { class: p(["relative z-10 flex shrink-0 items-center justify-center", [t.variant === "alternate" ? "mx-auto" : "", e.icon ? "w-8 h-8 rounded-full" : "w-6 h-6 rounded-full"]]) }, [e.icon ? (v(), o("div", {
				key: 0,
				class: p(["flex items-center justify-center w-8 h-8 rounded-full", r[e.variant || "primary"]])
			}, [(v(), i(w(e.icon), { class: "w-4 h-4" }))], 2)) : (v(), o("div", {
				key: 1,
				class: p(["w-3 h-3 rounded-full ring-4 ring-white dark:ring-gray-900", n[e.variant || "primary"]])
			}, null, 2))], 2),
			s("div", { class: p(["flex-1 pb-2", [t.variant === "alternate" && c % 2 != 0 ? "pr-6" : "pl-4", t.variant === "compact" ? "pt-0" : "pt-0.5"]]) }, [s("div", { class: p(["flex items-center gap-2", { "flex-row-reverse": t.variant === "alternate" && c % 2 != 0 }]) }, [s("h3", ga, T(e.title), 1), e.date ? (v(), o("span", _a, T(e.date), 1)) : a("", !0)], 2), e.description ? (v(), o("p", va, T(e.description), 1)) : a("", !0)], 2)
		], 2))), 128))], 2));
	}
}), ba = ["checked", "disabled"], xa = {
	key: 0,
	class: "text-sm text-gray-700 select-none dark:text-gray-300"
}, Sa = /* @__PURE__ */ u({
	__name: "BaseToggle",
	props: {
		modelValue: { type: Boolean },
		size: { default: "md" },
		disabled: {
			type: Boolean,
			default: !1
		},
		label: {}
	},
	emits: ["update:modelValue"],
	setup(e) {
		let t = {
			sm: "w-8 h-[1.125rem]",
			md: "w-10 h-[1.375rem]",
			lg: "w-12 h-[1.625rem]"
		}, n = {
			sm: "w-3.5 h-3.5",
			md: "w-[1.05rem] h-[1.05rem]",
			lg: "w-[1.3rem] h-[1.3rem]"
		}, r = {
			sm: "translate-x-3.5",
			md: "translate-x-[1.1rem]",
			lg: "translate-x-[1.35rem]"
		};
		return (i, c) => (v(), o("label", { class: p(["inline-flex items-center gap-2", e.disabled ? "opacity-50 cursor-not-allowed" : "cursor-pointer"]) }, [
			s("input", {
				type: "checkbox",
				checked: e.modelValue,
				disabled: e.disabled,
				class: "sr-only",
				onChange: c[0] ||= (t) => i.$emit("update:modelValue", !e.modelValue)
			}, null, 40, ba),
			s("span", { class: p(["relative rounded-full transition-colors duration-150", [t[e.size], e.modelValue ? "bg-primary-500" : "bg-gray-300"]]) }, [s("span", { class: p(["absolute top-[2px] left-[2px] bg-white rounded-full shadow-sm transition-transform duration-150", [n[e.size], e.modelValue ? r[e.size] : ""]]) }, null, 2)], 2),
			e.label ? (v(), o("span", xa, T(e.label), 1)) : a("", !0)
		], 2));
	}
}), Ca = /*#__PURE__*/ $(/* @__PURE__ */ u({
	__name: "BaseTooltip",
	props: {
		text: {},
		position: { default: "top" },
		variant: { default: "dark" },
		delay: { default: 200 }
	},
	setup(e) {
		let t = e, r = b(!1), i = null;
		function u() {
			i = setTimeout(() => {
				r.value = !0;
			}, t.delay);
		}
		function d() {
			i &&= (clearTimeout(i), null), r.value = !1;
		}
		let f = {
			top: "bottom-full left-1/2 -translate-x-1/2 mb-2",
			bottom: "top-full left-1/2 -translate-x-1/2 mt-2",
			left: "right-full top-1/2 -translate-y-1/2 mr-2",
			right: "left-full top-1/2 -translate-y-1/2 ml-2"
		}, m = {
			top: "top-full left-1/2 -translate-x-1/2 border-t-current border-x-transparent border-b-transparent",
			bottom: "bottom-full left-1/2 -translate-x-1/2 border-b-current border-x-transparent border-t-transparent",
			left: "left-full top-1/2 -translate-y-1/2 border-l-current border-y-transparent border-r-transparent",
			right: "right-full top-1/2 -translate-y-1/2 border-r-current border-y-transparent border-l-transparent"
		}, h = {
			dark: "bg-gray-900 text-white dark:bg-gray-700",
			light: "bg-white text-gray-700 shadow-lg border border-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600"
		}, g = {
			dark: "text-gray-900 dark:text-gray-700",
			light: "text-white dark:text-gray-800"
		};
		return (t, i) => (v(), o("div", {
			class: "relative inline-block",
			onMouseenter: u,
			onMouseleave: d
		}, [S(t.$slots, "default", {}, void 0, !0), l(n, { name: "tooltip" }, {
			default: A(() => [r.value ? (v(), o("div", {
				key: 0,
				role: "tooltip",
				class: p(["absolute z-50 px-2 py-1 text-xs font-medium rounded whitespace-nowrap pointer-events-none", [f[e.position], h[e.variant]]])
			}, [c(T(e.text) + " ", 1), s("span", { class: p(["absolute w-0 h-0 border-4", [m[e.position], g[e.variant]]]) }, null, 2)], 2)) : a("", !0)]),
			_: 1
		})], 32));
	}
}), [["__scopeId", "data-v-ad2eab58"]]), wa = {
	role: "tree",
	class: "text-sm"
}, Ta = ["aria-expanded"], Ea = ["onClick"], Da = ["aria-label", "onClick"], Oa = {
	key: 1,
	class: "w-5 shrink-0"
}, ka = { class: "truncate text-gray-700 dark:text-gray-200" }, Aa = {
	key: 0,
	class: "pl-4",
	role: "group"
}, ja = /* @__PURE__ */ u({
	name: "BaseTreeView",
	__name: "BaseTreeView",
	props: {
		items: {},
		expandAll: {
			type: Boolean,
			default: !1
		},
		selectable: {
			type: Boolean,
			default: !1
		},
		modelValue: {}
	},
	emits: ["update:modelValue", "node-click"],
	setup(t, { emit: n }) {
		let r = t, c = n, u = b(/* @__PURE__ */ new Set());
		function d(e) {
			for (let t of e) t.children?.length && (u.value.add(t.id), d(t.children));
		}
		k(() => r.expandAll, (e) => {
			e ? (d(r.items), u.value = new Set(u.value)) : u.value = /* @__PURE__ */ new Set();
		}, { immediate: !0 });
		function f(e) {
			u.value.has(e.id) ? u.value.delete(e.id) : u.value.add(e.id), u.value = new Set(u.value);
		}
		function m(e) {
			return u.value.has(e.id);
		}
		function h(e) {
			return r.selectable ? Array.isArray(r.modelValue) ? r.modelValue.includes(e.id) : r.modelValue === e.id : !1;
		}
		function g(e) {
			if (!e.disabled && (c("node-click", e), r.selectable)) if (Array.isArray(r.modelValue)) {
				let t = r.modelValue.includes(e.id) ? r.modelValue.filter((t) => t !== e.id) : [...r.modelValue, e.id];
				c("update:modelValue", t);
			} else c("update:modelValue", e.id);
		}
		return (n, r) => {
			let u = C("BaseTreeView", !0);
			return v(), o("div", wa, [(v(!0), o(e, null, x(t.items, (e) => (v(), o("div", {
				key: e.id,
				role: "treeitem",
				"aria-expanded": e.children?.length ? m(e) : void 0
			}, [s("div", {
				class: p(["flex items-center gap-1 rounded px-2 py-1.5 transition-colors", {
					"bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300": h(e),
					"hover:bg-gray-50 dark:hover:bg-gray-700/50": !h(e) && !e.disabled,
					"opacity-50 cursor-not-allowed": e.disabled,
					"cursor-pointer": !e.disabled
				}]),
				onClick: (t) => g(e)
			}, [
				e.children?.length ? (v(), o("button", {
					key: 0,
					type: "button",
					class: "flex items-center justify-center w-5 h-5 shrink-0 rounded hover:bg-gray-200 dark:hover:bg-gray-600 cursor-pointer",
					"aria-label": m(e) ? "Collapse" : "Expand",
					onClick: N((t) => f(e), ["stop"])
				}, [(v(), o("svg", {
					class: p(["w-3.5 h-3.5 text-gray-400 transition-transform duration-150 dark:text-gray-500", { "rotate-90": m(e) }]),
					xmlns: "http://www.w3.org/2000/svg",
					viewBox: "0 0 20 20",
					fill: "currentColor"
				}, [...r[2] ||= [s("path", {
					"fill-rule": "evenodd",
					d: "M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z",
					"clip-rule": "evenodd"
				}, null, -1)]], 2))], 8, Da)) : (v(), o("span", Oa)),
				e.icon ? (v(), i(w(e.icon), {
					key: 2,
					class: "w-4 h-4 shrink-0 text-gray-400 dark:text-gray-500"
				})) : a("", !0),
				s("span", ka, T(e.label), 1)
			], 10, Ea), e.children?.length && m(e) ? (v(), o("div", Aa, [l(u, {
				items: e.children,
				"expand-all": t.expandAll,
				selectable: t.selectable,
				"model-value": t.modelValue,
				"onUpdate:modelValue": r[0] ||= (e) => c("update:modelValue", e),
				onNodeClick: r[1] ||= (e) => c("node-click", e)
			}, null, 8, [
				"items",
				"expand-all",
				"selectable",
				"model-value"
			])])) : a("", !0)], 8, Ta))), 128))]);
		};
	}
}), Ma = /*#__PURE__*/ $(/* @__PURE__ */ u({
	__name: "ButtonGroup",
	props: { attached: {
		type: Boolean,
		default: !0
	} },
	setup(e) {
		return (t, n) => (v(), o("div", {
			class: p(["inline-flex", e.attached ? "button-group-attached" : "gap-1"]),
			role: "group"
		}, [S(t.$slots, "default", {}, void 0, !0)], 2));
	}
}), [["__scopeId", "data-v-6ea34ef7"]]), Na = ["disabled"], Pa = {
	key: 0,
	class: "h-px bg-gray-100 my-1 dark:bg-gray-700"
}, Fa = ["disabled", "onClick"], Ia = /* @__PURE__ */ u({
	__name: "DropdownButton",
	props: {
		items: {},
		variant: { default: "primary" },
		size: { default: "md" },
		align: { default: "left" },
		disabled: {
			type: Boolean,
			default: !1
		},
		icon: {}
	},
	emits: ["select"],
	setup(r, { emit: c }) {
		let u = r, d = c, h = b(!1), y = b(), C = b(), D = b({});
		function O() {
			if (!C.value) return;
			let e = C.value.getBoundingClientRect(), t = window.innerHeight - e.bottom, n = t < 200 ? `${e.top - 4}px` : `${e.bottom + 4}px`, r = t < 200 ? "translateY(-100%)" : "";
			u.align === "right" ? D.value = {
				position: "fixed",
				top: n,
				right: `${window.innerWidth - e.right}px`,
				transform: r,
				zIndex: "9999"
			} : D.value = {
				position: "fixed",
				top: n,
				left: `${e.left}px`,
				transform: r,
				zIndex: "9999"
			};
		}
		function k() {
			u.disabled || (h.value = !h.value, h.value && f(O));
		}
		function j(e) {
			!e.disabled && !e.divider && (d("select", e), h.value = !1);
		}
		function M(e) {
			if (y.value && !y.value.contains(e.target)) {
				let t = document.querySelector("[data-dropdown-portal]");
				if (t && t.contains(e.target)) return;
				h.value = !1;
			}
		}
		function N() {
			h.value && O();
		}
		g(() => {
			document.addEventListener("click", M), window.addEventListener("scroll", N, !0);
		}), _(() => {
			document.removeEventListener("click", M), window.removeEventListener("scroll", N, !0);
		});
		let P = {
			primary: "bg-primary-500 text-white hover:bg-primary-600 focus:ring-primary-200",
			secondary: "bg-gray-500 text-white hover:bg-gray-600 focus:ring-gray-200",
			success: "bg-emerald-500 text-white hover:bg-emerald-600 focus:ring-emerald-200",
			warning: "bg-amber-500 text-white hover:bg-amber-600 focus:ring-amber-200",
			danger: "bg-red-500 text-white hover:bg-red-600 focus:ring-red-200",
			ghost: "bg-transparent text-gray-700 hover:bg-gray-100 focus:ring-gray-200 dark:text-gray-300 dark:hover:bg-gray-700",
			outline: "bg-transparent text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-gray-200 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
		}, F = {
			sm: "px-3 py-1.5 text-xs",
			md: "px-4 py-2 text-sm",
			lg: "px-5 py-2.5 text-base"
		};
		return (c, u) => (v(), o("div", {
			ref_key: "dropdownRef",
			ref: y,
			class: "relative inline-block"
		}, [s("button", {
			ref_key: "triggerRef",
			ref: C,
			class: p(["inline-flex items-center justify-center gap-2 font-medium rounded-md cursor-pointer transition-all duration-150 select-none focus:outline-none focus:ring-2", [
				P[r.variant],
				F[r.size],
				r.disabled ? "opacity-50 cursor-not-allowed" : ""
			]]),
			disabled: r.disabled,
			onClick: k
		}, [
			r.icon ? (v(), i(w(r.icon), {
				key: 0,
				class: "w-4 h-4 shrink-0"
			})) : a("", !0),
			S(c.$slots, "trigger", {}, () => [s("span", null, [S(c.$slots, "default")])]),
			l(E(G), { class: p(["w-4 h-4 shrink-0 transition-transform duration-150", { "rotate-180": h.value }]) }, null, 8, ["class"])
		], 10, Na), (v(), i(t, { to: "body" }, [l(n, {
			"enter-active-class": "transition duration-150 ease-out",
			"enter-from-class": "opacity-0 scale-95",
			"enter-to-class": "opacity-100 scale-100",
			"leave-active-class": "transition duration-100 ease-in",
			"leave-from-class": "opacity-100 scale-100",
			"leave-to-class": "opacity-0 scale-95"
		}, {
			default: A(() => [h.value ? (v(), o("div", {
				key: 0,
				"data-dropdown-portal": "",
				style: m(D.value),
				class: "min-w-[10rem] bg-white border border-gray-200 rounded-lg shadow-lg py-1 overflow-hidden dark:bg-gray-800 dark:border-gray-700"
			}, [(v(!0), o(e, null, x(r.items, (t, n) => (v(), o(e, { key: n }, [t.divider ? (v(), o("div", Pa)) : (v(), o("button", {
				key: 1,
				class: p(["w-full flex items-center gap-2 px-3 py-2 text-sm text-left transition-colors duration-100", [t.disabled ? "opacity-40 cursor-not-allowed" : "cursor-pointer", t.danger ? "text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20" : "text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700"]]),
				disabled: t.disabled,
				onClick: (e) => j(t)
			}, [t.icon ? (v(), i(w(t.icon), {
				key: 0,
				class: "w-4 h-4 shrink-0"
			})) : a("", !0), s("span", null, T(t.label), 1)], 10, Fa))], 64))), 128))], 4)) : a("", !0)]),
			_: 1
		})]))], 512));
	}
}), La = { class: "bg-white border border-gray-200 rounded-xl p-5 shadow-sm dark:bg-gray-800 dark:border-gray-700" }, Ra = { class: "flex justify-between items-start" }, za = { class: "text-sm text-gray-500 mb-1 dark:text-gray-400" }, Ba = { class: "text-2xl font-bold text-gray-900 dark:text-gray-100" }, Va = {
	key: 0,
	class: "w-10 h-10 flex items-center justify-center bg-primary-50 rounded-lg text-primary-500 dark:bg-primary-900/30 dark:text-primary-400"
}, Ha = {
	key: 0,
	class: "mt-3 flex items-center gap-2 text-xs"
}, Ua = { key: 0 }, Wa = { key: 1 }, Ga = {
	key: 1,
	class: "text-gray-500 dark:text-gray-400"
}, Ka = /* @__PURE__ */ u({
	__name: "StatCard",
	props: {
		title: {},
		value: {},
		subtitle: {},
		icon: {},
		trend: { default: "neutral" },
		trendValue: {}
	},
	setup(e) {
		let t = {
			up: "text-emerald-500",
			down: "text-red-500",
			neutral: "text-gray-500 dark:text-gray-400"
		};
		return (n, r) => (v(), o("div", La, [s("div", Ra, [s("div", null, [s("p", za, T(e.title), 1), s("h3", Ba, T(e.value), 1)]), e.icon ? (v(), o("div", Va, [(v(), i(w(e.icon), { class: "w-5 h-5" }))])) : a("", !0)]), e.trendValue || e.subtitle ? (v(), o("div", Ha, [e.trendValue ? (v(), o("span", {
			key: 0,
			class: p(["font-semibold", t[e.trend]])
		}, [e.trend === "up" ? (v(), o("span", Ua, "↑")) : e.trend === "down" ? (v(), o("span", Wa, "↓")) : a("", !0), c(" " + T(e.trendValue), 1)], 2)) : a("", !0), e.subtitle ? (v(), o("span", Ga, T(e.subtitle), 1)) : a("", !0)])) : a("", !0)]));
	}
}), qa = { role: "tabpanel" }, Ja = /* @__PURE__ */ u({
	__name: "TabPanel",
	props: { name: {} },
	setup(e) {
		let t = e, n = d(ra), i = r(() => n?.value === t.name);
		return (e, t) => j((v(), o("div", qa, [S(e.$slots, "default")], 512)), [[O, i.value]]);
	}
});
//#endregion
export { We as BaseAccordion, qe as BaseAlert, Ye as BaseAvatar, Qe as BaseAvatarGroup, et as BaseBadge, rt as BaseBreadcrumb, ot as BaseButton, mt as BaseCalendar, _t as BaseCard, St as BaseCheckbox, Tt as BaseColorPicker, Ft as BaseCommandPalette, Bt as BaseContextMenu, an as BaseDatePicker, cn as BaseDivider, pn as BaseDrawer, Cn as BaseEditor, En as BaseEmptyState, $n as BaseFileUpload, ar as BaseInput, lr as BaseKbd, mr as BaseModal, Er as BaseNotificationList, jr as BaseNumberInput, qr as BasePagination, Jr as BasePopover, Qr as BaseProgress, ei as BaseRadio, ri as BaseRating, vi as BaseSelect, bi as BaseSkeleton, Di as BaseSlider, Ai as BaseSpinner, Li as BaseSteps, na as BaseTable, aa as BaseTabs, oa as BaseTag, ha as BaseTextarea, ya as BaseTimeline, Sa as BaseToggle, Ca as BaseTooltip, ja as BaseTreeView, Ma as ButtonGroup, Ia as DropdownButton, Ka as StatCard, ra as TAB_ACTIVE_KEY, Ja as TabPanel };
