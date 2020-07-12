import Vue from 'vue'
import Vuex from 'vuex'

import siswa_auth from './stores/siswa/auth.js'
import siswa_user from './stores/siswa/user.js'
import siswa_reference from './stores/siswa/reference.js'
import siswa_banksoal from './stores/siswa/banksoal.js'
import siswa_soal from './stores/siswa/soal.js'
import siswa_ujian from './stores/siswa/ujian.js'
import siswa_jadwal from './stores/siswa/jadwal.js'

Vue.use(Vuex)

const store = new Vuex.Store({
	modules: {
		siswa_auth,
		siswa_user,
		siswa_reference,
		siswa_banksoal,
		siswa_soal,
		siswa_ujian,
		siswa_jadwal
	},
	state: {
		token: localStorage.getItem('token'),
		errors: [],
		isLoading: false,
		isLoadinger: false
	},
	getters: {
		isAuth: state => {
			return state.token != 'null' && state.token != null
		},
		isLoading: state => {
			return state.isLoading
		},
		isLoadinger: state => {
			return state.isLoadinger
		}
	},
	mutations: {
		SET_TOKEN(state, payload) {
			state.token = payload
		},
		SET_ERRORS(state, payload) {
			state.errors = payload
		},
		CLEAR_ERRORS(state) {
			state.errors = []
		},
		SET_LOADING(state, payload) {
			state.isLoading = payload
		},
		SET_LOADINGER(state, payload) {
			state.isLoadinger = payload
		}
	}
})

export default store