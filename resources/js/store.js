import Vue from 'vue'
import Vuex from 'vuex'

import siswa_auth from './stores/siswa/auth.js'
import siswa_user from './stores/siswa/user.js'
import siswa_ujian from './stores/siswa/ujian.js'
import siswa_jadwal from './stores/siswa/jadwal.js'
import siswa_channel from './stores/siswa/channel.js'

Vue.use(Vuex)

const store = new Vuex.Store({
	modules: {
		siswa_auth,
		siswa_user,
		siswa_ujian,
		siswa_jadwal,
		siswa_channel
	},
	state: {
		token: localStorage.getItem('token'),
		errors: [],
		isLoading: false,
		isLoadinger: false,
		setting: []
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
		},
		setting: state => {
			return state.setting
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
		},
		SET_SETTING(state, payload) {
			state.setting = payload
		}
	}
})

export default store