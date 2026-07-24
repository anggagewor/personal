import { ref as e, watch as t } from "vue";
import { defineStore as n } from "pinia";
//#region src/store.ts
var r = {}, i = [
	{
		name: "indigo",
		label: "Indigo",
		swatch: "#6366f1"
	},
	{
		name: "blue",
		label: "Blue",
		swatch: "#3b82f6"
	},
	{
		name: "emerald",
		label: "Emerald",
		swatch: "#10b981"
	},
	{
		name: "rose",
		label: "Rose",
		swatch: "#f43f5e"
	},
	{
		name: "amber",
		label: "Amber",
		swatch: "#f59e0b"
	},
	{
		name: "teal",
		label: "Teal",
		swatch: "#14b8a6"
	},
	{
		name: "violet",
		label: "Violet",
		swatch: "#8b5cf6"
	},
	{
		name: "slate",
		label: "Slate",
		swatch: "#64748b"
	}
], a = i.map((e) => e.name), o = n("theme", () => {
	let n = e(l()), o = e(u());
	function s() {
		return r.getUserKey?.() ?? null;
	}
	function c(e) {
		let t = s();
		return t ? `${e}:${t}` : e;
	}
	function l() {
		let e = r.getUserKey?.() ?? null;
		if (e) {
			let t = localStorage.getItem(`theme:${e}`);
			if (t === "light" || t === "dark") return t;
		}
		let t = localStorage.getItem("theme");
		return t === "light" || t === "dark" ? t : typeof window < "u" && window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
	}
	function u() {
		let e = r.defaultColor ?? "indigo", t = r.getUserKey?.() ?? null;
		if (t) {
			let e = localStorage.getItem(`primary_color:${t}`);
			if (e && a.includes(e)) return e;
		}
		let n = localStorage.getItem("primary_color");
		return n && a.includes(n) ? n : e;
	}
	function d(e) {
		typeof document > "u" || (e === "dark" ? document.documentElement.classList.add("dark") : document.documentElement.classList.remove("dark"));
	}
	function f(e) {
		if (typeof document > "u") return;
		let t = document.documentElement.classList;
		t.forEach((e) => {
			e.startsWith("theme-") && t.remove(e);
		}), e !== "indigo" && t.add(`theme-${e}`);
	}
	function p() {
		n.value = n.value === "light" ? "dark" : "light";
	}
	function m(e) {
		o.value = e;
	}
	function h() {
		let e = s();
		if (!e) return;
		let t = localStorage.getItem(`theme:${e}`);
		(t === "light" || t === "dark") && (n.value = t);
		let r = localStorage.getItem(`primary_color:${e}`);
		r && a.includes(r) && (o.value = r);
	}
	return t(n, (e) => {
		let t = c("theme");
		localStorage.setItem(t, e), d(e);
	}, { immediate: !0 }), t(o, (e) => {
		let t = c("primary_color");
		localStorage.setItem(t, e), f(e);
	}, { immediate: !0 }), {
		theme: n,
		primaryColor: o,
		colorOptions: i,
		toggle: p,
		setColor: m,
		loadForUser: h
	};
});
//#endregion
export { i as colorOptions, o as useThemeStore };
