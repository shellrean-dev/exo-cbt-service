(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[3],{

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/pages/siswa/UjianKonfirm.vue?vue&type=script&lang=js&":
/*!************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/pages/siswa/UjianKonfirm.vue?vue&type=script&lang=js& ***!
  \************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var vuex__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vuex */ "./node_modules/vuex/dist/vuex.esm.js");
/* harmony import */ var _entities_notif__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../entities/notif */ "./resources/js/entities/notif.js");
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//


/* harmony default export */ __webpack_exports__["default"] = ({
  name: 'KonfirmUjian',
  data: function data() {
    return {
      token_ujian: '',
      timeout: 0
    };
  },
  computed: _objectSpread(_objectSpread(_objectSpread(_objectSpread({}, Object(vuex__WEBPACK_IMPORTED_MODULE_0__["mapGetters"])(['isAuth', 'isLoading'])), Object(vuex__WEBPACK_IMPORTED_MODULE_0__["mapState"])('siswa_jadwal', {
    jadwal: function jadwal(state) {
      return state.banksoalAktif;
    }
  })), Object(vuex__WEBPACK_IMPORTED_MODULE_0__["mapState"])('siswa_user', {
    peserta: function peserta(state) {
      return state.pesertaDetail;
    }
  })), Object(vuex__WEBPACK_IMPORTED_MODULE_0__["mapState"])('siswa_ujian', {
    ujian: function ujian(state) {
      return state.dataUjian;
    },
    invalidToken: function invalidToken(state) {
      return state.invalidToken;
    }
  })),
  methods: _objectSpread(_objectSpread({}, Object(vuex__WEBPACK_IMPORTED_MODULE_0__["mapActions"])('siswa_ujian', ['tokenChecker'])), {}, {
    cekToken: function cekToken() {
      var _this = this;

      this.tokenChecker({
        token: this.token_ujian
      }).then(function (res) {
        _this.$router.replace({
          name: 'ujian.prepare'
        });
      })["catch"](function (error) {
        _this.$bvToast.toast(error.message, Object(_entities_notif__WEBPACK_IMPORTED_MODULE_1__["errorToas"])());
      });
    }
  })
});

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/pages/siswa/UjianKonfirm.vue?vue&type=template&id=3d154058&":
/*!****************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/pages/siswa/UjianKonfirm.vue?vue&type=template&id=3d154058& ***!
  \****************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", { staticClass: "container" }, [
    _c("div", { staticClass: "row justify-content-center" }, [
      _c("div", { staticClass: "col-md-8" }, [
        _c("div", { staticClass: "page-inner mt--5" }, [
          _c("div", { staticClass: "card" }, [
            _vm._m(0),
            _vm._v(" "),
            _c("div", { staticClass: "card-body" }, [
              _c(
                "form",
                {
                  staticClass: "form-custom form-ajax",
                  attrs: { id: "fmToken", name: "fmToken" },
                  on: {
                    submit: function($event) {
                      $event.preventDefault()
                      return _vm.cekToken($event)
                    }
                  }
                },
                [
                  _c("div", { staticClass: "form-group" }, [
                    _c("label", { attrs: { for: "nisn" } }, [
                      _vm._v("NO UJIAN")
                    ]),
                    _vm._v(" "),
                    _c("p", {
                      staticClass: "form-control-static",
                      domProps: { textContent: _vm._s(_vm.peserta.no_ujian) }
                    })
                  ]),
                  _vm._v(" "),
                  _c("div", { staticClass: "form-group" }, [
                    _c("label", { attrs: { for: "nama" } }, [
                      _vm._v("Nama Peserta")
                    ]),
                    _vm._v(" "),
                    _c("p", {
                      staticClass: "form-control-static",
                      domProps: { textContent: _vm._s(_vm.peserta.nama) }
                    })
                  ]),
                  _vm._v(" "),
                  _vm.jadwal && _vm.ujian
                    ? [
                        _c("div", { staticClass: "form-group" }, [
                          _c("label", { attrs: { for: "nm_uji" } }, [
                            _vm._v("Mata Ujian")
                          ]),
                          _vm._v(" "),
                          _vm.jadwal && _vm.ujian
                            ? _c("p", {
                                staticClass: "form-control-static",
                                domProps: {
                                  textContent: _vm._s(_vm.jadwal.matpel)
                                }
                              })
                            : _vm._e(),
                          _vm._v(" "),
                          !_vm.ujian
                            ? _c("p", { staticClass: "form-control-static" }, [
                                _vm._v("Tidak ada jadwal ujian pada hari ini")
                              ])
                            : _vm._e(),
                          _vm._v(" "),
                          _c("span", { staticClass: "line" })
                        ]),
                        _vm._v(" "),
                        _vm.jadwal && _vm.ujian && _vm.ujian.status_ujian != "1"
                          ? _c("div", { staticClass: "form-group" }, [
                              _c("label", { attrs: { for: "token" } }, [
                                _vm._v("Token")
                              ]),
                              _vm._v(" "),
                              _c("input", {
                                directives: [
                                  {
                                    name: "model",
                                    rawName: "v-model",
                                    value: _vm.token_ujian,
                                    expression: "token_ujian"
                                  }
                                ],
                                staticClass: "form-control",
                                attrs: {
                                  type: "text",
                                  autofocus: "",
                                  placeholder: "Masukkan token"
                                },
                                domProps: { value: _vm.token_ujian },
                                on: {
                                  input: function($event) {
                                    if ($event.target.composing) {
                                      return
                                    }
                                    _vm.token_ujian = $event.target.value
                                  }
                                }
                              }),
                              _vm._v(" "),
                              _c("span", { staticClass: "line" }),
                              _vm._v(" "),
                              _vm.invalidToken.token
                                ? _c("small", { staticClass: "text-danger" }, [
                                    _vm._v("Token tidak sesuai")
                                  ])
                                : _vm._e(),
                              _vm._v(" "),
                              _vm.invalidToken.release
                                ? _c("small", { staticClass: "text-danger" }, [
                                    _vm._v("Status token belum dirilis")
                                  ])
                                : _vm._e()
                            ])
                          : _vm._e(),
                        _vm._v(" "),
                        _vm.jadwal && _vm.ujian && _vm.ujian.status_ujian != "1"
                          ? _c(
                              "div",
                              { staticClass: "form-group" },
                              [
                                _c(
                                  "b-button",
                                  {
                                    attrs: {
                                      variant: "info",
                                      type: "submit",
                                      block: "",
                                      disabled: _vm.isLoading
                                    }
                                  },
                                  [
                                    _vm._v(
                                      "\n    \t\t\t\t\t\t\t\t\t\t" +
                                        _vm._s(
                                          _vm.isLoading
                                            ? "Processing..."
                                            : "Submit"
                                        ) +
                                        "\n    \t\t\t\t\t\t\t\t\t"
                                    )
                                  ]
                                )
                              ],
                              1
                            )
                          : _vm._e()
                      ]
                    : _vm._e()
                ],
                2
              )
            ])
          ])
        ])
      ])
    ])
  ])
}
var staticRenderFns = [
  function() {
    var _vm = this
    var _h = _vm.$createElement
    var _c = _vm._self._c || _h
    return _c("div", { staticClass: "card-header" }, [
      _c("h4", [_vm._v("Konfirmasi Data Peserta")])
    ])
  }
]
render._withStripped = true



/***/ }),

/***/ "./resources/js/pages/siswa/UjianKonfirm.vue":
/*!***************************************************!*\
  !*** ./resources/js/pages/siswa/UjianKonfirm.vue ***!
  \***************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _UjianKonfirm_vue_vue_type_template_id_3d154058___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./UjianKonfirm.vue?vue&type=template&id=3d154058& */ "./resources/js/pages/siswa/UjianKonfirm.vue?vue&type=template&id=3d154058&");
/* harmony import */ var _UjianKonfirm_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./UjianKonfirm.vue?vue&type=script&lang=js& */ "./resources/js/pages/siswa/UjianKonfirm.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _UjianKonfirm_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _UjianKonfirm_vue_vue_type_template_id_3d154058___WEBPACK_IMPORTED_MODULE_0__["render"],
  _UjianKonfirm_vue_vue_type_template_id_3d154058___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/pages/siswa/UjianKonfirm.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/pages/siswa/UjianKonfirm.vue?vue&type=script&lang=js&":
/*!****************************************************************************!*\
  !*** ./resources/js/pages/siswa/UjianKonfirm.vue?vue&type=script&lang=js& ***!
  \****************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_UjianKonfirm_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib??ref--4-0!../../../../node_modules/vue-loader/lib??vue-loader-options!./UjianKonfirm.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/pages/siswa/UjianKonfirm.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_UjianKonfirm_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/pages/siswa/UjianKonfirm.vue?vue&type=template&id=3d154058&":
/*!**********************************************************************************!*\
  !*** ./resources/js/pages/siswa/UjianKonfirm.vue?vue&type=template&id=3d154058& ***!
  \**********************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_UjianKonfirm_vue_vue_type_template_id_3d154058___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../node_modules/vue-loader/lib??vue-loader-options!./UjianKonfirm.vue?vue&type=template&id=3d154058& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/pages/siswa/UjianKonfirm.vue?vue&type=template&id=3d154058&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_UjianKonfirm_vue_vue_type_template_id_3d154058___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_UjianKonfirm_vue_vue_type_template_id_3d154058___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ })

}]);