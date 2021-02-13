import Vue from 'vue'

import {
    ButtonPlugin,
    FormCheckboxPlugin,
    FormInputPlugin,
    ModalPlugin,
    ToastPlugin,
    FormRadioPlugin
   } from 'bootstrap-vue';
[ButtonPlugin,
 FormCheckboxPlugin,
 FormInputPlugin,
 ModalPlugin,
 ToastPlugin,
 FormRadioPlugin  ].forEach(comp => {
  Vue.use(comp);
});
import imageZoom from 'vue-image-zoomer';
Vue.component("RenderString", {
    props: {
        string: {
            required: true,
            type: String
        }
    },
    render(h) {
        const render = {
            template: `<div>` + this.string + `
            <b-modal id="modal-zoom" centered title="Gambar" class="shadow">
                <template v-slot:modal-footer="{ cancel }">
                    <div class="button-wrapper">
                    <b-button size="sm" variant="info"" @click="cancel()">
                        Tutup
                    </b-button>
                    </div>
                </template>
                <template v-slot:default="{ hide }">
                    <image-zoom :zoom-amount="3" :regular="currImg"></image-zoom>
                </template>
            </b-modal>
            </div>`,
            components: {
                imageZoom
            },
            data() {
                return {
                    currImg: ""
                }
            },
            methods: {
                showImage(src) {
                    this.currImg = src
                    this.$bvModal.show('modal-zoom')
                },
                markComplete() {
                    console.log("this method callded")
                }
            }
        }
        return h(render)
    }
})
