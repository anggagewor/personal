//#region src/index.ts
var e = "purdia_", t = "purdia-client-secret-v1", n = 1e5, r = 300 * 1e3;
function i(i) {
	i.prefix !== void 0 && (e = i.prefix), i.secret !== void 0 && (t = i.secret), i.iterations !== void 0 && (n = i.iterations), i.cacheTtl !== void 0 && (r = i.cacheTtl);
}
var a = /* @__PURE__ */ new Map();
function o(e) {
	let t = a.get(e);
	return t ? Date.now() > t.expiresAt ? (a.delete(e), null) : t.value : null;
}
function s(e, t, n = r) {
	a.set(e, {
		value: t,
		expiresAt: Date.now() + n
	});
}
function c(e) {
	a.delete(e);
}
function l() {
	a.clear();
}
function u(e) {
	return new TextEncoder().encode(e);
}
function d(e) {
	return new TextDecoder().decode(e);
}
function f(e) {
	let t = new Uint8Array(e), n = "";
	for (let e = 0; e < t.length; e++) n += String.fromCharCode(t[e]);
	return btoa(n);
}
function p(e) {
	let t = atob(e), n = new Uint8Array(t.length);
	for (let e = 0; e < t.length; e++) n[e] = t.charCodeAt(e);
	return n;
}
async function m(e) {
	let r = await crypto.subtle.importKey("raw", u(t), "PBKDF2", !1, ["deriveKey"]);
	return crypto.subtle.deriveKey({
		name: "PBKDF2",
		salt: e,
		iterations: n,
		hash: "SHA-256"
	}, r, {
		name: "AES-GCM",
		length: 256
	}, !1, ["encrypt", "decrypt"]);
}
async function h(t, n) {
	let r = crypto.getRandomValues(/* @__PURE__ */ new Uint8Array(16)), i = crypto.getRandomValues(/* @__PURE__ */ new Uint8Array(12)), a = await m(r), o = await crypto.subtle.encrypt({
		name: "AES-GCM",
		iv: i
	}, a, u(n)), c = [
		f(r.buffer),
		f(i.buffer),
		f(o)
	].join(".");
	localStorage.setItem(e + t, c), s(t, n);
}
async function g(t) {
	let n = o(t);
	if (n !== null) return n;
	let r = localStorage.getItem(e + t);
	if (!r) return null;
	try {
		let [e, n, i] = r.split(".");
		if (!e || !n || !i) return null;
		let a = p(e), o = p(n), c = p(i), l = await m(a), u = d(await crypto.subtle.decrypt({
			name: "AES-GCM",
			iv: o
		}, l, c));
		return s(t, u), u;
	} catch {
		return _(t), null;
	}
}
function _(t) {
	localStorage.removeItem(e + t), c(t);
}
function v() {
	let t = [];
	for (let n = 0; n < localStorage.length; n++) {
		let r = localStorage.key(n);
		r?.startsWith(e) && t.push(r);
	}
	t.forEach((e) => localStorage.removeItem(e)), l();
}
//#endregion
export { i as configureSecureStorage, v as secureClearAll, g as secureGet, _ as secureRemove, h as secureSet };
