import $axios from '../../api.js'

const state = () => ({
	banksoals: [],
	page: 1,
	id: '',
	ujian: [],
	
})

const mutations = {
	ASSIGN_DATA(state, payload) {
		state.banksoals = payload
	},
	SET_PAGE(state, payload) {
		state.page = payload
	},
	SET_ID_UPDATE(state, payload) {
		state.id = payload
	},
	ASSIGN_SOAL_UJIAN(state, payload) {
		state.ujian = payload
	}
}

const actions = {
	getBanksoals({ commit, state }, payload) {
		let search = typeof payload != 'undefined' ? payload : ''
		return new Promise(( resolve, reject) => {
			$axios.get(`/banksoals?page=${state.page}&q=${search}`)
			.then((response) => {
				commit('ASSIGN_DATA', response.data)
				resolve(response.data)
			})
		})
	},
	getUjian({ commit, state }, payload) {
		return new Promise(( resolve, reject) => {
			commit('SET_LOADING',true, { root: true })
			$axios.post(`/ujian/setter`,payload)
			.then((response) => {
				commit('ASSIGN_SOAL_UJIAN', response.data)
				commit('SET_LOADING',false, { root: true })
				resolve(response.data)
			})
			.catch(() => {
				commit('SET_LOADING',false, { root: true })
				resolve(response.data)
			})
		})
	}
}

export default {
	namespaced: true,
	state,
	actions,
	mutations
}