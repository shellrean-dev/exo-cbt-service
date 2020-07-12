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
	getUserLogin({ commit, payload}) {
		return new Promise((resolve, reject) => {
			$axios.get(`peserta-authenticated`)
			.then((response) => {
				commit('ASSIGN_PESERTA_DETAIL', response.data.data)
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