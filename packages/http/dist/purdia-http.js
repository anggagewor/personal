import e, { AxiosError as t } from "axios";
import { secureGet as n, secureRemove as r, secureSet as i } from "@purdia/crypto";
//#region src/index.ts
var a = { services: {
	main: {
		baseURL: "/api",
		timeout: 3e4
	},
	auth: {
		baseURL: "/api/auth",
		timeout: 15e3
	}
} }, o = /* @__PURE__ */ new Map(), s = !1, c = [];
function l(e) {
	c.push(e);
}
function u(e) {
	c.forEach((t) => t(e)), c = [];
}
function d() {
	c = [];
}
function f(e) {
	return e === "access" ? a.tokenKeys?.access ?? "auth_token" : a.tokenKeys?.refresh ?? "refresh_token";
}
async function p() {
	let t = await n(f("refresh"));
	if (!t) return null;
	try {
		let n = a.services.auth ?? a.services.main, { token: r, refresh_token: o } = (await e.post(`${n.baseURL}/refresh`, { refresh_token: t }, {
			headers: {
				"Content-Type": "application/json",
				Accept: "application/json"
			},
			timeout: n.timeout ?? 15e3
		})).data.data;
		return await i(f("access"), r), o && await i(f("refresh"), o), r;
	} catch {
		return null;
	}
}
function m() {
	r(f("access")), r(f("refresh")), r("auth_user"), a.onUnauthorized?.();
}
function h() {
	return a.locale ? typeof a.locale == "function" ? a.locale() : a.locale : localStorage.getItem("app_locale") ?? "id";
}
function g(e) {
	return {
		400: "Permintaan tidak valid.",
		401: "Sesi telah berakhir. Silakan login kembali.",
		403: "Akses ditolak.",
		404: "Data tidak ditemukan.",
		422: "Data yang dikirim tidak valid.",
		429: "Terlalu banyak permintaan.",
		500: "Terjadi kesalahan di server."
	}[e] ?? `Terjadi kesalahan (${e}).`;
}
function _(t) {
	let r = a.services[t];
	if (!r) throw Error(`[http] Service "${t}" is not configured.`);
	let i = e.create({
		baseURL: r.baseURL,
		timeout: r.timeout ?? 3e4,
		headers: {
			"Content-Type": "application/json",
			Accept: "application/json",
			...r.headers
		}
	});
	return i.interceptors.request.use(async (e) => {
		let t = await n(f("access"));
		return t && (e.headers.Authorization = `Bearer ${t}`), e.headers["Accept-Language"] = h(), e;
	}, (e) => Promise.reject(e)), i.interceptors.response.use((e) => e, async (e) => {
		if (!e.response) {
			let e = {
				message: "Koneksi gagal. Periksa jaringan internet kamu.",
				status: 0
			};
			return a.onError?.(e), Promise.reject(e);
		}
		let { status: t, data: n } = e.response, r = {
			message: n?.message ?? g(t),
			errors: n?.errors,
			status: t
		};
		if (t === 401) {
			let t = e.config;
			if (t._retry || t.url?.includes("/refresh")) return m(), Promise.reject(r);
			if (!s) {
				s = !0, t._retry = !0;
				let e = await p();
				return e ? (s = !1, u(e), t.headers.Authorization = `Bearer ${e}`, i(t)) : (s = !1, d(), m(), Promise.reject(r));
			}
			return new Promise((e, n) => {
				l((n) => {
					t.headers.Authorization = `Bearer ${n}`, e(i(t));
				});
				let a = setInterval(() => {
					!s && c.length === 0 && (clearInterval(a), n(r));
				}, 50);
			});
		}
		return t === 403 && (r.message = n?.message ?? "Kamu tidak punya akses untuk melakukan ini."), t === 419 && (r.message = "Sesi telah kadaluarsa. Silakan refresh halaman."), t === 429 && (r.message = "Terlalu banyak permintaan. Coba lagi nanti."), t >= 500 && (r.message = "Terjadi kesalahan di server. Coba lagi nanti."), t !== 401 && t !== 422 && a.onError?.(r), Promise.reject(r);
	}), i;
}
function v(e) {
	a = e, o.clear();
}
function y(e = "main") {
	return o.has(e) || o.set(e, _(e)), o.get(e);
}
function b() {
	return y("main");
}
function x(e, t) {
	return b().get(e, t).then((e) => e.data);
}
function S(e, t, n) {
	return b().post(e, t, n).then((e) => e.data);
}
function C(e, t, n) {
	return b().put(e, t, n).then((e) => e.data);
}
function w(e, t, n) {
	return b().patch(e, t, n).then((e) => e.data);
}
function T(e, t) {
	return b().delete(e, t).then((e) => e.data);
}
function E(e, t, n) {
	return b().post(e, t, {
		headers: { "Content-Type": "multipart/form-data" },
		onUploadProgress: (e) => {
			n && e.total && n(Math.round(e.loaded / e.total * 100));
		}
	}).then((e) => e.data);
}
function D(e, n) {
	return b().get(e, {
		...n,
		responseType: "blob"
	}).then((e) => e).catch(async (e) => {
		if (e && typeof e == "object" && "status" in e && "message" in e) return Promise.reject(e);
		if (e instanceof t && e.response?.data instanceof Blob) {
			let t = e.response.data, n = e.response.status;
			if (t.type === "application/json" || t.type.includes("json")) try {
				let e = await t.text(), r = JSON.parse(e), i = {
					message: r.message ?? g(n),
					errors: r.errors,
					status: n
				};
				return Promise.reject(i);
			} catch {}
			let r = {
				message: g(n),
				status: n
			};
			return Promise.reject(r);
		}
		return Promise.reject(e);
	});
}
//#endregion
export { T as del, D as download, x as get, v as initHttp, w as patch, S as post, C as put, E as upload, y as useHttp };
