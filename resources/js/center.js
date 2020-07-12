import axios from 'axios'
import store from './store.js'
import router from './router.js'

const $axios = axios.create({
	baseURL: process.env.MIX_SERVER_CENTER+'/api/',
	headers: {
		'Content-Type' : 'application/json'
	}
})

$axios.interceptors.request.use (
	function ( config ) {
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
   router.push({ name: 'login' })
  }
  return Promise.reject(error);
})

export default $axios