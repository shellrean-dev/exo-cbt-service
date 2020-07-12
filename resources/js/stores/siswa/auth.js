import $axios from '../../api.js'

const state = () => ({

})

const mutations = {

}

const actions = {
	submit({ commit }, payload) {
		localStorage.setItem('token',null)

		commit('SET_TOKEN',null,{ root: true } )
		return new Promise((resolve, reject) => {
			commit('SET_LOADING', true, { root: true })
			$axios.post('/logedin', payload)
			.then((response) => {
				if (response.data.status == 'success') {
					localStorage.setItem('token',response.data.token)
					commit('SET_TOKEN',response.data.token, { root: true })
					commit('SET_LOADING',false, { root: true })
				}
				else if(response.data.status == 'loggedin') {
					commit('SET_ERRORS', { invalid: 'User sudah login, minta proktor untuk mereset' }, { root: true })
					commit('SET_LOADING', false, { root: true })
				}
				else if(response.data.status == 'non-sesi') {
					commit('SET_ERRORS', { invalid: 'Ujian tidak sesuai sesi' }, { root: true })
					commit('SET_LOADING', false, { root: true })
				}
				else {
					commit('SET_ERRORS', { invalid: 'No ujian/Password salah' } , { root: true })
					commit('SET_LOADING',false, { root: true })
				}
				resolve(response.data)
			})
			.catch((error) => {
				if (error.response.status == 422) {
					commit('SET_ERRORS',error.response.data.errors, { root: true})
				}
				commit('SET_LOADING',false, { root: true })
				reject(error)
			})
		})
	},
	logoutPeserta({ commit }, payload) {
		return new Promise((resolve, reject) => {
			commit('SET_LOADING', true, { root: true })
			$axios.get('peserta/logout', payload) 
			.then((response) => {
				commit('SET_LOADING', false, { root: true })
				resolve(response.data)
			}) 
			.catch((err) => {
				commit('SET_LOADING', false, { root: true })
				reject(err)
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