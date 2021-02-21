import Vue from 'vue'
import Router from 'vue-router'
import store from './store.js'

const LoginUjian = () => import('./pages/siswa/LoginUjian.vue')
import IndexUjian from './pages/siswa/Index'
import UjianKonfirm from './pages/siswa/UjianKonfirm'
import UjianPrepare from './pages/siswa/UjianPrepare'
import Ujian from './pages/siswa/Ujian'
import UjianSelesai from './pages/siswa/UjianSelesai'

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
					component: Ujian
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
