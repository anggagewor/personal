import { computed as e, ref as t } from "vue";
import { defineStore as n } from "pinia";
import { secureGet as r, secureRemove as i, secureSet as a } from "@purdia/crypto";
//#region src/store.ts
var o = {};
function s(e) {
	return o.keys?.[e] ?? {
		token: "auth_token",
		refreshToken: "refresh_token",
		user: "auth_user"
	}[e];
}
var c = n("auth", () => {
	let n = t(null), c = t(null), l = t(!1), u = e(() => !!c.value);
	async function d() {
		if (l.value) return;
		let e = await r(s("token")), t = await r(s("user"));
		if (e && t) {
			c.value = e;
			try {
				n.value = JSON.parse(t);
			} catch {
				await h();
			}
		}
		l.value = !0;
	}
	async function f(e, t) {
		if (o.login) {
			let r = await o.login(e, t);
			n.value = r.user, c.value = r.tokens.token, await a(s("token"), r.tokens.token), r.tokens.refresh_token && await a(s("refreshToken"), r.tokens.refresh_token), await a(s("user"), JSON.stringify(r.user));
		} else {
			let t = {
				id: 1,
				name: "Admin User",
				email: e,
				avatar: void 0
			}, r = "mock-jwt-token-" + Date.now(), i = "mock-refresh-token-" + Date.now();
			n.value = t, c.value = r, await a(s("token"), r), await a(s("refreshToken"), i), await a(s("user"), JSON.stringify(t));
		}
	}
	async function p(e, t, r) {
		if (o.register) {
			let i = await o.register(e, t, r);
			n.value = i.user, c.value = i.tokens.token, await a(s("token"), i.tokens.token), i.tokens.refresh_token && await a(s("refreshToken"), i.tokens.refresh_token), await a(s("user"), JSON.stringify(i.user));
		} else {
			let r = {
				id: 1,
				name: e,
				email: t,
				avatar: void 0
			}, i = "mock-jwt-token-" + Date.now(), o = "mock-refresh-token-" + Date.now();
			n.value = r, c.value = i, await a(s("token"), i), await a(s("refreshToken"), o), await a(s("user"), JSON.stringify(r));
		}
	}
	async function m(e) {
		return o.forgotPassword ? o.forgotPassword(e) : { message: "Link reset password telah dikirim ke email Anda." };
	}
	async function h() {
		i(s("token")), i(s("refreshToken")), i(s("user"));
	}
	async function g() {
		await o.onLogout?.(), n.value = null, c.value = null, await h();
	}
	return {
		user: n,
		token: c,
		isAuthenticated: u,
		ready: l,
		init: d,
		login: f,
		register: p,
		forgotPassword: m,
		logout: g
	};
});
//#endregion
//#region src/guard.ts
function l(e, t = {}) {
	let { loginRoute: n = "login", homeRoute: r = "dashboard", guestMeta: i = "guest", publicMeta: a = "public" } = t;
	e.beforeEach(async (e) => {
		let t = c();
		t.ready || await t.init();
		let o = e.meta[i] === !0, s = e.meta[a] === !0;
		if (!t.isAuthenticated && !o && !s) return {
			name: n,
			query: { redirect: e.fullPath }
		};
		if (t.isAuthenticated && o && !s) return { name: r };
	});
}
//#endregion
export { l as createAuthGuard, c as useAuthStore };
