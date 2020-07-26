import $axios from '../../api.js'

const state = () => ({
	pesertaDetail: []
})

const mutations = {
	ASSIGN_PESERTA_DETAIL(state, payload) {
		state.pesertaDetail = payload
	}
}

const actions = {
	getUserLogin({ commit }) {
		return new Promise((resolve, reject) => {
			$axios.get(`peserta-authenticated`)
			.then((response) => {
				commit('ASSIGN_PESERTA_DETAIL', response.data.data)
				resolve(response.data)
			})
            .catch((error) => {
                reject(error.response.data)
            })
		})
	},
	getSettingSekolah({ commit }) {
		return new Promise(async (resolve, reject) => {
			try {
				let network = await $axios.get('setting')

				commit('SET_SETTING', network.data.data, { root: true })
				resolve(network.data)
			} catch (error) {
				reject(error.response.data)
			}
		})
	}
}

export default {
	namespaced: true,
	state, 
	actions,
	mutations
}