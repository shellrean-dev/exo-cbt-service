import $axios from '../../api.js'

const state = () => ({
	reference: {
		periode: ''
	},
	ajaran: []
})

const mutations = {
	ASSIGN_AJARAN(state, payload) {
		state.ajaran = payload
	},
    ASSIGN_FORM(state, payload) {
        state.reference = {
            periode: payload.periode
        }
    }
}

const actions = {
	getAjaranSelect({ commit, state }, payload) {
		let tahun = [0,0,1,1,2,2]
        let i = 0
        let periode = ''
        let t1 = ''
        let t2 = ''
        let t3 = ''
        let value = '' 
        let newarr = ''

        let ajaran = []

        tahun.forEach((tahun, index) => {
            if (i%2) {
                periode = 'Genap'
            }
            else {
                periode  = 'Ganjil'
            }
            let d = new Date()

            t1 = (d.getFullYear()+tahun)-2
            t2 = (d.getFullYear()+tahun)-1
            t3 = 'Semester ' + periode
                    
            value = `${t1} / ${t2} ${t3}`

            newarr = {
                value: value
            }

            ajaran.push(newarr)

            i += 1
        })

        commit('ASSIGN_AJARAN', ajaran)
	},
    getSetting({ commit }, payload) {
        return new Promise((resolve, reject) => {
            $axios.get(`/settings`)
            .then((response) => {
                commit('ASSIGN_FORM', response.data.data)
                resolve(response.data)
            })
        })
    },
    submitSetting({ dispatch, commit, state }) {
        return new Promise((resolve, reject) => {
            $axios.post(`/settings`, state.reference)
            .then((response) => {
                dispatch('getSetting').then(() => {
                    resolve(response.data)
                })
            })
            .catch((error) => {
                if (error.response.status == 422) {
                    commit('SET_ERRORS', error.response.data.errors, { root: true})
                }
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