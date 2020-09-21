import $axios from '../../api.js'

const state = () => ({
	banksoalAktif: {}
})

const mutations = {
	ASSIGN_UJIAN_AKTIF(state, payload) {
		state.banksoalAktif = payload
	}
}

const actions = {
	ujianAktif({ commit, state }) {
		return new Promise(( resolve, reject) => {
            $axios.get('jadwals/peserta') 
			.then((response) => {
				commit('ASSIGN_UJIAN_AKTIF', response.data.data)
				resolve(response.data)
			})
            .catch((error) => {
                reject(error.response.data)
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