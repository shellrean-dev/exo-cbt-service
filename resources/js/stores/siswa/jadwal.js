import $axios from '../../api.js'

const state = () => ({
	banksoalHariIni: [],
	banksoalAktif: ''
})

const mutations = {
	UJIAN_HARI_INI(state, payload) {
		state.banksoalHariIni = payload
	},
	ASSIGN_UJIAN_AKTIF(state, payload) {
		state.banksoalAktif = payload
	}
}

const actions = {
	ujianAktif({ commit, state }) {
		return new Promise(( resolve, reject) => {
			$axios.get(`/jadwal/aktif`)
			.then((response) => {
				commit('ASSIGN_UJIAN_AKTIF', response.data.data)
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