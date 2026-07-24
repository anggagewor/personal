//#region src/format.ts
function e(e, t = {}) {
	let { locale: n = "id-ID", currency: r = "IDR" } = t;
	return new Intl.NumberFormat(n, {
		style: "currency",
		currency: r,
		minimumFractionDigits: 0,
		maximumFractionDigits: 0
	}).format(e);
}
function t(e, t = "id-ID") {
	return new Intl.NumberFormat(t).format(e);
}
function n(e, t = {
	day: "numeric",
	month: "short",
	year: "numeric"
}, n = "id-ID") {
	let r = typeof e == "string" ? new Date(e) : e;
	return new Intl.DateTimeFormat(n, t).format(r);
}
function r(e, t = "id-ID") {
	let r = typeof e == "string" ? new Date(e) : e, i = Date.now() - r.getTime(), a = Math.round(i / 1e3), o = Math.round(a / 60), s = Math.round(o / 60), c = Math.round(s / 24), l = new Intl.RelativeTimeFormat(t, { numeric: "auto" });
	return Math.abs(a) < 60 ? l.format(-a, "second") : Math.abs(o) < 60 ? l.format(-o, "minute") : Math.abs(s) < 24 ? l.format(-s, "hour") : Math.abs(c) < 30 ? l.format(-c, "day") : n(r, {
		day: "numeric",
		month: "short",
		year: "numeric"
	}, t);
}
//#endregion
//#region src/timing.ts
function i(e, t = 300) {
	let n = null;
	return ((...r) => {
		n && clearTimeout(n), n = setTimeout(() => e(...r), t);
	});
}
function a(e, t = 300) {
	let n = 0;
	return ((...r) => {
		let i = Date.now();
		i - n >= t && (n = i, e(...r));
	});
}
function o(e) {
	return new Promise((t) => setTimeout(t, e));
}
//#endregion
//#region src/misc.ts
function s(e, t, n) {
	return Math.min(Math.max(e, t), n);
}
function c(e, t) {
	return Math.floor(Math.random() * (t - e + 1)) + e;
}
function l(e = "") {
	let t = Math.random().toString(36).slice(2, 10) + Date.now().toString(36);
	return e ? `${e}_${t}` : t;
}
//#endregion
export { s as clamp, i as debounce, e as formatCurrency, n as formatDate, t as formatNumber, r as formatRelativeTime, c as randomInt, o as sleep, a as throttle, l as uid };
