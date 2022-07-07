import $axios from '../../api.js'

/**
 * List of endpoint
 * @type {Object}
 */
const endpoint = Object.freeze({
    test: 'ujian',
    doubt: 'ujian/ragu-ragu',
    finish: 'ujian/selesai',
    filled: 'ujians/filled',
    startTime: 'ujians/start/time',
    start: 'ujians/start',
    peserta: 'ujians/peserta',
    uncomplete: 'ujians/uncomplete',
    hasils: 'ujian/hasils',
    leaveCounter: 'ujians/leave-counter'
})

/**
 * State for ujian
 * @type {Object}
 */
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
    hasils: [],
	banksoalAktif: '',
    uncomplete: {},
    interval: ''
})

/**
 * mutations for ujian
 * @type {Object}
 */
const mutations = {
	FILLED_DATA_UJIAN,
	SLICE_DATA_RESP,
	SLICE_RAGU_JAWABAN,
    ASSIGN_PESERTA_UJIAN,
    ASSIGN_PESERTA_UNCOMPLETE_UJIAN,
    ASSIGN_HASIL_UJIAN,
}

/**
 * actions for ujian
 * @type {Object}
 */
const actions = {
	submitJawaban,
	submitJawabanEssy,
	updateRaguJawaban,
	selesaiUjianPeserta,
	takeFilled,
	pesertaMulai,
    startUjian,
    getPesertaUjian,
    getUncompleteUjian,
    getHasilUjian,
    submitJawabanMenjodohkan,
    submitJawabanMengurutkan,
    leaveCounterUjian
}

/**
 * Let's play the game
 *
 */
export default {
	namespaced: true,
	state,
	mutations,
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
 * Set filled ujian
 * @param {*} state
 * @param {*} payload
 */
function FILLED_DATA_UJIAN(state, payload) {
    state.filledUjian = payload
}

/**
 * Slice data response
 * @param {*} state
 * @param {*} payload
 */
function SLICE_DATA_RESP(state, payload) {
    state.filledUjian.data[payload.index].jawab = payload.data.jawab
    state.filledUjian.data[payload.index].answered = true
}

/**
 * Slice ragu ragu
 * @param {*} state
 * @param {*} payload
 */
function SLICE_RAGU_JAWABAN(state, payload) {
    state.filledUjian.data[payload.index].ragu_ragu = payload.data.ragu_ragu
}

/**
 * Assign peserta ujian data
 * @param {*} state
 * @param {*} payload
 */
function ASSIGN_PESERTA_UJIAN(state, payload) {
    state.ujian = payload
}

/**
 * Assign uncomplete data to state
 * @param {*} state
 * @param {*} payload
 */
function ASSIGN_PESERTA_UNCOMPLETE_UJIAN(state, payload) {
    state.uncomplete = payload
}

/**
 * Assig hasil ujian to state
 * @param {*} state
 * @param {*} payload
 */
function ASSIGN_HASIL_UJIAN(state, payload) {
    state.hasils = payload
}

/**
 * Submit student answer
 * @param {*} store
 * @param {*} payload
 */
function submitJawaban({ commit, state }, payload) {
    return new Promise(async(resolve, reject) => {
        try {
            commit('SET_LOADINGER',true, { root: true })
            const network = await $axios.post(endpoint.test, payload)
            commit('SET_LOADINGER',false, { root: true })
            commit('SLICE_DATA_RESP', network.data)
            resolve(network.data)
        } catch (error) {
            if (typeof error.response != 'undefined') {
                if (error.response && error.response.status == 422) {
                    commit('SET_ERRORS', error.response.data.errors, { root: true })
                }
            }
            reject(getError(error))
            commit('SET_LOADINGER',false, { root: true })
        }
    })
}

/**
 * Submit student esay answer
 * @param {*} store
 * @param {*} payload
 */
function submitJawabanEssy({ commit, state }, payload) {
    return new Promise(async(resolve, reject) => {
        try {
            commit('SET_LOADINGER',true, { root: true })
            const network = await $axios.post(endpoint.test, payload)
            commit('SET_LOADINGER',false, { root: true })
            commit('SLICE_DATA_RESP', network.data)
            resolve(network.data)
        } catch (error) {
            if (typeof error.response != 'undefined') {
                if (error.response.status == 422) {
                    commit('SET_ERRORS', error.response.data.errors, { root: true })
                }
            }
            reject(getError(error))
            commit('SET_LOADINGER',false, { root: true })
        }
    })
}

/**
 * Submit student menjodohkan
 * @param {*} store
 * @param {*} payload
 */
function submitJawabanMenjodohkan({ commit, state }, payload) {
    return new Promise(async(resolve, reject) => {
        try {
            commit('SET_LOADINGER',true, { root: true })
            const network = await $axios.post(endpoint.test, payload)
            commit('SET_LOADINGER',false, { root: true })
            commit('SLICE_DATA_RESP', network.data)
            resolve(network.data)
        } catch (error) {
            if (typeof error.response != 'undefined') {
                if (error.response.status == 422) {
                    commit('SET_ERRORS', error.response.data.errors, { root: true })
                }
            }
            reject(getError(error))
            commit('SET_LOADINGER',false, { root: true })
        }
    })
}

/**
 * Submit student mengurutkan
 * @param {*} store
 * @param {*} payload
 */
function submitJawabanMengurutkan({ commit, state }, payload) {
    return new Promise(async(resolve, reject) => {
        try {
            commit('SET_LOADINGER',true, { root: true })
            const network = await $axios.post(endpoint.test, payload)
            commit('SET_LOADINGER',false, { root: true })
            commit('SLICE_DATA_RESP', network.data)
            resolve(network.data)
        } catch (error) {
            if (typeof error.response != 'undefined') {
                if (error.response.status == 422) {
                    commit('SET_ERRORS', error.response.data.errors, { root: true })
                }
            }
            reject(getError(error))
            commit('SET_LOADINGER',false, { root: true })
        }
    })
}

/**
 * Update doubt answer
 * @param {*} store
 * @param {*} payload
 */
function updateRaguJawaban({ commit, state }, payload) {
    return new Promise(async(resolve, reject) => {
        try {
            commit('SET_LOADINGER',true, { root: true })
            const network = await $axios.post(endpoint.doubt, payload)
            commit('SET_LOADINGER',false, { root: true })
            commit('SLICE_RAGU_JAWABAN', network.data)
            resolve(network.data)
        } catch (error) {
            reject(getError(error))
            commit('SET_LOADINGER',false, { root: true })
        }
    })
}

/**
 * Finish test
 * @param {*} store
 * @param {*} payload
 */
function selesaiUjianPeserta({commit}, payload) {
    return new Promise(async(resolve, reject) => {
        try {
            commit('SET_LOADING', true, { root: true })
            const network = await $axios.get(endpoint.finish, payload)
            commit('SET_LOADING', false, { root: true })
            resolve(network.data)
        } catch (error) {
            reject(getError(error))
            commit('SET_LOADING', false, { root: true })
        }
    })
}

/**
 * Create filled
 * @param {*} store
 */
function takeFilled({ commit }) {
    return new Promise(async (resolve, reject) => {
        try {
            commit('SET_LOADING',true, { root: true })
            const network = await $axios.get(endpoint.filled)
            commit('FILLED_DATA_UJIAN', network.data)
            commit('SET_LOADING',false, { root: true })
            resolve(network.data)
        } catch (error) {
            reject(getError(error))
            commit('SET_LOADING',false, { root: true })
        }
    })
}

/**
 * Start time
 * @param {*} store
 */
function pesertaMulai({ commit }) {
    return new Promise(async ( resolve, reject) => {
        try {
            commit('SET_LOADING',true, { root: true })
            const network = await $axios.post(endpoint.startTime)
            commit('SET_LOADING',false, { root: true })
            resolve(network.data)
        } catch (error) {
            reject(getError(error))
            commit('SET_LOADING',false, { root: true })
        }
    })
}

/**
 * start test
 * @param {*} store
 * @param {*} payload
 */
function startUjian({ commit, state }, payload) {
    commit('SET_LOADING', true, { root: true })
    return new Promise(async(resolve, reject) => {
        try {
            let network = await $axios.post('ujians/start', payload)

            commit('SET_LOADING', false, { root: true })
            resolve(network.data)
        } catch (error) {
            commit('SET_LOADING', false, { root: true })
            reject(getError(error))
        }
    })
}

/**
 * get peserta test
 * @param {*} store
 */
function getPesertaUjian({ commit, state }) {
    return new Promise(async(resolve, reject) => {
        try {
            commit('SET_LOADING', true, { root: true })
            let network = await $axios.get(endpoint.peserta)

            commit('ASSIGN_PESERTA_UJIAN', network.data.data)
            commit('SET_LOADING', false, { root: true })
            resolve(network.data)
        } catch (error) {
            reject(getError(error))
            commit('SET_LOADING', false, { root: true })
        }
    })
}

/**
 * get student's uncomplete test
 * @param {*} store
 */
function getUncompleteUjian({ commit, state }) {
    return new Promise(async(resolve, reject) => {
        try {
            commit('SET_LOADING', true, { root: true })
            let network = await $axios.get(endpoint.uncomplete)

            commit('ASSIGN_PESERTA_UNCOMPLETE_UJIAN', network.data.data)
            commit('SET_LOADING', false, { root: true })
            resolve(network.data)
        } catch (error) {
            reject(getError(error))
            commit('SET_LOADING', false, { root: true })
        }
    })
}

/**
 * Get peserta's hasil ujian
 * @param {*} commit
 */
function getHasilUjian({ commit }) {
    return new Promise(async(resolve, reject) => {
        try {
            commit('SET_LOADING', true, { root: true })
            let network = await $axios.post(endpoint.hasils)

            commit('ASSIGN_HASIL_UJIAN', network.data.data)
            commit('SET_LOADING', false, { root: true })
            resolve(network.data)
        } catch (error) {
            reject(getError(error))
            commit('SET_LOADING', false, { root: true })
        }
    })
}

/**
 * Leave counter ujian
 * @param {*} commit
 */
 function leaveCounterUjian({ commit }, payload) {
    return new Promise(async(resolve, reject) => {
        try {
            let network = await $axios.post(endpoint.leaveCounter, payload)
            resolve(network.data)
        } catch (error) {
            reject(getError(error))
        }
    })
}
