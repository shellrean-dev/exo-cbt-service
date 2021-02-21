import $axios from '../../api.js'

/**
 * List of endpoint
 * @type {Object}
 */
const endpoint = Object.freeze({
    getData: "jadwals/peserta"
})

/**
 * state for jadwal
 * @type {Object}
 */
const state = () => ({
	banksoalAktif: {}
})

/**
 * mutations for jadwal
 * @type {Object}
 */
const mutations = {
    _assign_ujian_aktif
}

/**
 * actions for jadwal
 * @type {Object}
 */
const actions = {
	ujianAktif
}

/**
 * Let's play the game
 *
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
 * assign data ujian aktif to state
 * @param {*} state
 * @param {*} payload
 */
function _assign_ujian_aktif(state, payload) {
    state.banksoalAktif = payload
}

/**
 * Get ujian aktif data
 * @param {*} store
 */
function ujianAktif({ commit }) {
    return new Promise(async ( resolve, reject) => {
        try {
            commit('SET_LOADING', true, { root: true })
            const network = await $axios.get(endpoint.getData)
            commit('_assign_ujian_aktif', network.data.data)
            commit('SET_LOADING', false, { root: true })
			resolve(network.data)
        } catch (err) {
            reject(getError(err))
            commit('SET_LOADING', false, { root: true })
        }
    })
}
