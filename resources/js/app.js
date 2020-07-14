import Vue from 'vue'
import router from './router.js'
import store from './store.js'
const App = () =>  import('./App.vue')

import {
 ButtonPlugin,
 FormCheckboxPlugin,
 FormInputPlugin,
 ModalPlugin,
 ToastPlugin,
 FormRadioPlugin 
} from 'bootstrap-vue';
import CKEditor from 'ckeditor4-vue';

[ButtonPlugin,
 FormCheckboxPlugin,
 FormInputPlugin,
 ModalPlugin,
 ToastPlugin,
 FormRadioPlugin  ].forEach(comp => {
  Vue.use(comp);
});
Vue.use(CKEditor);

import { mapActions, mapGetters } from 'vuex'
import { successToas, errorToas} from './entities/notif'

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
        ...mapActions('siswa_user', ['getUserLogin'])
    },
    created() {
        if (this.isAuth) {
            this.getUserLogin()
            .catch((error) => {
                this.$bvToast.toast(error.message, errorToas())
            })
        }
    }
})
