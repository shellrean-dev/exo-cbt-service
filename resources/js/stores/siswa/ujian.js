import $axios from '../../api.js'

const state = () => ({
	jawabanPeserta: [],
	ujianList: [],
	filledUjian: [],
	dataUjian: {},
	invalidToken: {
		release: false,
		token: false
	},
    ujian: {},
	banksoalAktif: '',
    uncomplete: {}
})

const mutations = {
	FILLED_DATA_UJIAN(state, payload) {
		state.filledUjian = payload
	},
	SLICE_DATA_RESP(state, payload) {
		state.filledUjian.data[payload.index].jawab = payload.data.jawab
		state.filledUjian.data[payload.index].iscorrect = payload.data.iscorrect
		state.filledUjian.data[payload.index].iscorrect = payload.data.jawab_essy
	},
	SLICE_RAGU_JAWABAN(state, payload) {
		state.filledUjian.data[payload.index].ragu_ragu = payload.data.ragu_ragu
	},
    ASSIGN_PESERTA_UJIAN(state, payload) {
        state.ujian = payload
    },
    ASSIGN_PESERTA_UNCOMPLETE_UJIAN(state, payload) {
        state.uncomplete = payload
    }
}

const actions = {
	submitJawaban({ commit, state }, payload) {
		commit('SET_LOADINGER',true, { root: true })
		return new Promise(( resolve, reject ) => {
			$axios.post(`/ujian`, payload) 
			.then((response) => {
				commit('SET_LOADINGER',false, { root: true })
				commit('SLICE_DATA_RESP', response.data)
				resolve(response.data)
			})
			.catch((error) => {
				commit('SET_LOADINGER',false, { root: true })
				if (error.response && error.response.status == 422) {
					commit('SET_ERRORS', error.response.data.errors, { root: true })
				}
				reject(error.response.data)
			})
		})
	},
	submitJawabanEssy({ commit, state }, payload) {
		commit('SET_LOADINGER',true, { root: true })
		return new Promise(( resolve, reject ) => {
			$axios.post(`/ujian`, payload)
			.then((response) => {
				commit('SET_LOADINGER',false, { root: true })
				commit('SLICE_DATA_RESP', response.data)
				resolve(response.data)
			})
			.catch((error) => {
				commit('SET_LOADINGER',false, { root: true })
				if (error.response.status == 422) {
					commit('SET_ERRORS', error.response.data.errors, { root: true })
				}
				reject(error.response.data)
			})
		}) 
	},
	updateRaguJawaban({ commit, state }, payload) {
		commit('SET_LOADINGER',true, { root: true })
		return new Promise(( resolve, reject) => {
			$axios.post(`/ujian/ragu-ragu`, payload) 
			.then((response) => {
				commit('SET_LOADINGER',false, { root: true })
				commit('SLICE_RAGU_JAWABAN', response.data)
				resolve(response.data)
			})
			.catch((error) => {
				commit('SET_LOADINGER',false, { root: true })
                reject(error.response.data)
			})
		})
	},
	selesaiUjianPeserta({commit}, payload) {
        commit('SET_LOADING', true, { root: true })
		return new Promise(( resolve, reject) => {
			$axios.get(`/ujian/selesai`, payload)
			.then((response) => {
                commit('SET_LOADING', false, { root: true })
				resolve(response.daa)
			})
            .catch((error) => {
                commit('SET_LOADING', false, { root: true })
                reject(error.response.data)
            })
		})
	},
	takeFilled({ commit }) {
		commit('SET_LOADING',true, { root: true })
		return new Promise((resolve, reject) => {
			$axios.get(`/ujians/filled`)
			.then((response) => {
				commit('SET_LOADING',false, { root: true })
				commit('FILLED_DATA_UJIAN', response.data)
				resolve(response.data)
			})
			.catch((error) => {
				commit('SET_LOADING',false, { root: true })
				reject(error.response.data)
			})
		})
	},
	pesertaMulai({ commit, state }) {
        commit('SET_LOADING',true, { root: true })
		return new Promise(( resolve, reject) => {
            $axios.post('ujians/start/time') 
			.then((response) => {
                commit('SET_LOADING',false, { root: true })
				resolve(response.data)
			})
            .catch((error) => {
                commit('SET_LOADING',false, { root: true })
                reject(error.response.data)
            })
		})
	},
    startUjian({ commit, state }, payload) {
        commit('SET_LOADING', true, { root: true })
        return new Promise(async(resolve, reject) => {
            try {
                let network = await $axios.post('ujians/start', payload)

                commit('SET_LOADING', false, { root: true })
                resolve(network.data)
            } catch (error) {
                commit('SET_LOADING', false, { root: true })
                reject(error.response.data)
            }
        })
    },
    getPesertaUjian({ commit, state }) {
        commit('SET_LOADING', true, { root: true })
        return new Promise(async(resolve, reject) => {
            try {
                let network = await $axios.get('ujians/peserta')

                commit('ASSIGN_PESERTA_UJIAN', network.data.data)
                commit('SET_LOADING', false, { root: true })
                resolve(network.data)
            } catch (error) {
                commit('SET_LOADING', false, { root: true })
                reject(error.response.data)
            }
        })
    },
    getUncompleteUjian({ commit, state }) {
        commit('SET_LOADING', true, { root: true })
        return new Promise(async(resolve, reject) => {
            try {
                let network = await $axios.get('ujians/uncomplete')

                commit('ASSIGN_PESERTA_UNCOMPLETE_UJIAN', network.data.data)
                commit('SET_LOADING', false, { root: true })
                resolve(network.data)
            } catch (error) {
                commit('SET_LOADING', false, { root: true })
                reject(error.response.data)
            }
        })
    }
}

export default {
	namespaced: true,
	state,
	mutations,
	actions
}