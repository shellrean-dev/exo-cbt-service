import Vue from 'vue'
import imageZoom from 'vue-image-zoomer'

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
            <div class="fixed z-10 inset-0 overflow-y-auto" v-if="imgShow">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>
                    <span class="hidden sm:inline-block sm:align-middle " aria-hidden="true">&#8203;</span>
                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" role="dialog" aria-modal="true" aria-labelledby="modal-headline">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <image-zoom :zoom-amount="3" :regular="currImg"></image-zoom>
                        </div>
                        <div class="px-4 border-t-2 border-dashed py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button
                            @click="imgShow = false"
                            type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div></div>`,
            components: {
                imageZoom
            },
            data() {
                return {
                    currImg: "",
                    imgShow: false
                }
            },
            methods: {
                showImage(src) {
                    this.currImg = src
                    this.imgShow = true
                },
                markComplete() {
                    console.log("this method callded")
                }
            }
        }
        return h(render)
    }
})
