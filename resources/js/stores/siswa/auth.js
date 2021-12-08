import $axios from '../../api.js'

/**
 * List of endpoint
 * @type {Object}
 */
const endpoint = Object.freeze({
    auth: "logedin",
    logout: "peserta/logout"
})

/**
 * Actions for auth
 * @type {Object}
 */
const actions = {
	submit,
	logoutPeserta,
}

/**
 * Let's play the game
 */
export default {
	namespaced: true,
	actions
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
 * submit data auth
 * @param {*} store
 * @param {*} payload
 */
function submit({ commit }, payload) {
    return new Promise(async (resolve, reject) => {
        try {
            commit('SET_LOADING', true, { root: true })
            localStorage.setItem('token',null)
            commit('SET_TOKEN',null,{ root: true } )
            const network = await $axios.post(endpoint.auth, payload)

            if (network.data.status == 'success') {
                localStorage.setItem('token',network.data.token)
                commit('CLEAR_ERRORS', "", { root: true })
                commit('SET_TOKEN',network.data.token, { root: true })
            }
            else if(network.data.status == 'loggedin') {
                commit('SET_ERRORS', { invalid: 'User sudah login, minta proktor untuk mereset' }, { root: true })
            }
            else if(network.data.status == 'non-sesi') {
                commit('SET_ERRORS', { invalid: 'Ujian tidak sesuai sesi' }, { root: true })
            }
            else if (network.data.status == 'susspend') {
                commit('SET_ERRORS', { invalid: 'Peserta ujian tidak aktif, hubungi administrator' }, { root: true })
            }
            else {
                commit('SET_ERRORS', { invalid: 'No ujian/Password salah' } , { root: true })
            }
            commit('SET_LOADING', false, { root: true })
            resolve(network.data)
        } catch (error) {
            if (typeof error.response != 'undefined') {
                if (error.response.status == 422) {
                    commit('SET_ERRORS',error.response.data.errors, { root: true})
                }
            }
            reject(getError(error))
            commit('SET_LOADING', false, { root: true })
        }
    })
}

/**
 * logout data attention
 * @param {*} store
 * @param {*} payload
 */
function logoutPeserta({ commit }, payload) {
    return new Promise(async (resolve, reject) => {
        try {
            commit('SET_LOADING', true, { root: true })
            const network = await $axios.get('peserta/logout', payload)

            commit('SET_LOADING', false, { root: true })
            resolve(network.data)
        } catch (err) {
            reject(getError(err))
            commit('SET_LOADING', false, { root: true })
        }
    })
}
