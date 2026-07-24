import { computed as e, ref as t, shallowRef as n, watch as r } from "vue";
import { get as i } from "@purdia/http";
//#region src/useApi.ts
function a(e, r = {}) {
	let { initialData: i = null, immediate: a = !1, onSuccess: o, onError: s } = r, c = n(i), l = t(!1), u = n(null);
	async function d(...t) {
		l.value = !0, u.value = null;
		try {
			let n = await e(...t), r = n && typeof n == "object" && "data" in n ? n.data : n;
			return c.value = r, o?.(r), r;
		} catch (e) {
			let t = e;
			return u.value = t, s?.(t), null;
		} finally {
			l.value = !1;
		}
	}
	function f() {
		c.value = i, l.value = !1, u.value = null;
	}
	return a && d(), {
		data: c,
		loading: l,
		error: u,
		execute: d,
		reset: f
	};
}
//#endregion
//#region src/usePagination.ts
function o(n, o = {}) {
	let { initialPage: s = 1, initialPerPage: c = 10, searchDebounce: l = 300, immediate: u = !0, onSuccess: d, onError: f } = o, p = t(s), m = t(c), h = t(""), g = t(""), _ = t("asc"), v = t({}), y = t({
		current_page: s,
		last_page: 1,
		per_page: c,
		total: 0
	});
	function b() {
		let e = {
			page: p.value,
			per_page: m.value
		};
		return h.value && (e.search = h.value), g.value && (e.sort_by = g.value, e.sort_dir = _.value), Object.assign(e, v.value), e;
	}
	let { data: x, loading: S, error: C } = a(async () => [], { initialData: [] });
	async function w() {
		let e = b();
		try {
			S.value = !0, C.value = null;
			let t;
			t = typeof n == "function" ? await n(e) : await i(n, { params: e }), x.value = t.data, t.meta && (y.value = t.meta), d?.(t.data);
		} catch (e) {
			C.value = e, f?.(e);
		} finally {
			S.value = !1;
		}
	}
	async function T(e) {
		p.value = e, await w();
	}
	async function E() {
		p.value = 1, await w();
	}
	let D = e(() => y.value.last_page), O = e(() => y.value.total);
	r(p, () => {
		w();
	}), r(m, () => {
		p.value = 1, w();
	});
	let k = null;
	return r(h, () => {
		k && clearTimeout(k), k = setTimeout(() => {
			p.value = 1, w();
		}, l);
	}), u && w(), {
		data: x,
		loading: S,
		error: C,
		meta: y,
		currentPage: p,
		perPage: m,
		search: h,
		sortBy: g,
		sortDir: _,
		totalPages: D,
		totalItems: O,
		fetch: w,
		goToPage: T,
		refresh: E,
		filters: v
	};
}
//#endregion
export { a as useApi, o as usePagination };
