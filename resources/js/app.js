import Vue from 'vue'
import router from './router.js'
import store from './store.js'
import App from './App.vue'

import CKEditor from 'ckeditor4-vue';
Vue.use(CKEditor);

import VueSweetalert2 from 'vue-sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css';

Vue.use(VueSweetalert2)

import { mapActions, mapGetters } from 'vuex'
import { showSweetError } from './entities/alert.js'

const app = new Vue({
    el: '#app',
    router,
    store,
    components: {
        App
    },
    computed: {
        ...mapGetters(['isAuth'])
    },
    methods: {
        ...mapActions('siswa_user', ['getUserLogin','getSettingSekolah']),
        showError(err) {
            showSweetError(this, err)
        }
    },
    async created() {
        await this.getSettingSekolah()
        if (this.isAuth) {
            this.getUserLogin()
            .catch((error) => {
                this.showError(error)
            })
        }
    }
})
