import Vue from 'vue'
import Router from 'vue-router'
import store from './store.js'

const LoginUjian = () => import('./pages/siswa/LoginUjian.vue')
const IndexUjian = () => import('./pages/siswa/Index.vue')
const UjianKonfirm = () => import('./pages/siswa/UjianKonfirm.vue')
const UjianPrepare = () => import('./pages/siswa/UjianPrepare.vue')
const Kerjakan = () => import('./pages/siswa/Kerjakan.vue')
const UjianSelesai = () => import('./pages/siswa/UjianSelesai.vue')

Vue.use(Router)

const router = new Router({
	mode: 'history',
	routes: [
		{
			path: '/',
			name: 'login',
			component: LoginUjian,
		},
		{
			path: '/ujian',
			component: IndexUjian,
			meta: { requiresAuth: true },
			children: [
				{
					path: 'konfirm',
					name: 'ujian.konfirm',
					component: UjianKonfirm
				},
				{
					path: 'prepare',
					name: 'ujian.prepare',
					component: UjianPrepare
				},
				{
					path: 'extraordinary',
					name: 'ujian.while',
					component: Kerjakan
				},
				{
					path: 'selesai',
					name: 'ujian.selesai',
					component: UjianSelesai
				}
			]
		}
	]
})

router.beforeEach((to, from , next) => {
	store.commit('CLEAR_ERRORS')
	if (to.matched.some(record => record.meta.requiresAuth)) {
		let auth = store.getters.isAuth
		if (!auth) {
			next({ name: 'login' })
		}
		else {
			next()
		}
	}
	else {
		next()
	}
})
export default router