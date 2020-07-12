import axios from 'axios'
import store from './store.js'
import router from './router.js'

const $axios = axios.create({
	baseURL: '/api/v2',
	headers: {
        'Accept': 'application/json',
		'Content-Type' : 'application/json'
	}
})

$axios.interceptors.request.use (
	function ( config ) {
		config.headers.Authorization = 'Bearer '+store.state.token;
		return config;
	},
	function ( error ) {
		return Promise.reject( error )
	}
)

$axios.interceptors.response.use((response) => {
	return response
  }, (error) => {
	if (error.response.status == 401) {
	  new Promise((resolve, reject) => {
		  localStorage.removeItem('token')
		  resolve()
	  }).then(() => {
		  store.state.token = localStorage.getItem('token')
		  router.push({ name: 'login' })
	  })
	}
	return Promise.reject(error);
  })

export default $axios