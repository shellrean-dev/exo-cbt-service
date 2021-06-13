import io from 'socket.io-client'

const state = () => ({
	socket: io(process.env.MIX_SOCKET_URL,{
		autoConnect: false, 
		forceNew:true,  
		pingInterval: 2000,
		pingTimeout: 10000
  	}),
})

const mutations = {

}

const actions = {

}

export default {
	namespaced: true,
	state,
	mutations,
	actions
}