import $axios from '../../api.js'

/**
 * List of endpoint
 * @type {Object}
 */
const endpoint = Object.freeze({
    auth: 'peserta-authenticated',
    setting: 'setting'
})

/**
 * state for user
 * @type {Object}
 */
const state = () => ({
	pesertaDetail: {}
})

/**
 * Mutations for user
 * @type {Object}
 */
const mutations = {
	ASSIGN_PESERTA_DETAIL,
    REMOTE_PESERTA_DETAIL
}

/**
 * actions for user
 * @type {Object}
 */
const actions = {
	getUserLogin,
	getSettingSekolah
}

/**
 * Let's play the game
 */
export default {
	namespaced: true,
	state,
	actions,
	mutations
}

/**
 * Get error data
 * @param {*} error
 */
function getError(error) {
    if (typeof error.response != 'undefined') {
        if (typeof error.response.data != 'undefined') {
            return error.response.data
        }
        return { message: 'Terjadi kesalahan yang tidak dapat dijelaskan'}
    }
    return { message: 'Tidak dapat mengirim data, cek koneksi internet anda'}
}

/**
 * assign peserta detail
 * @param {*} state,
 * @param {*} payload
 */
function ASSIGN_PESERTA_DETAIL(state, payload) {
    state.pesertaDetail = payload
}

/**
 * remove peserta detail
 * @param {*} state
 * @param {*} payload
 */
function REMOTE_PESERTA_DETAIL(state, payload) {
    state.pesertaDetail = []
}

/**
 * get user's login data
 * @param {*} store
 */
function getUserLogin({ commit }) {
    return new Promise(async (resolve, reject) => {
        try {
            const network = await $axios.get(endpoint.auth)
            commit('ASSIGN_PESERTA_DETAIL', network.data.data)
            resolve(network.data)
        } catch (error) {
            reject(getError(error))
        }
    })
}

/**
 * get school's setting
 * @param {*} store
 */
function getSettingSekolah({ commit }) {
    return new Promise(async (resolve, reject) => {
        try {
            let network = await $axios.get('setting')

            commit('SET_SETTING', network.data.data, { root: true })
            resolve(network.data)
        } catch (error) {
            reject(getError(error))
        }
    })
}
