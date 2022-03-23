import io from 'socket.io-client'

const state = () => ({
	socket: io(process.env.MIX_SOCKET_URL,{
		autoConnect: false,
		pingInterval: 1000,
		pingTimeout: 5000,
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