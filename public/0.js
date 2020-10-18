(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[0],{

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/pages/siswa/LoginUjian.vue?vue&type=script&lang=js&":
/*!**********************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/pages/siswa/LoginUjian.vue?vue&type=script&lang=js& ***!
  \**********************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var vuex__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vuex */ "./node_modules/vuex/dist/vuex.esm.js");
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
  data: function data() {
    return {
      data: {
        no_ujian: '',
        password: ''
      }
    };
  },
  created: function created() {
    if (this.isAuth) {
      this.$router.replace({
        name: 'ujian.konfirm'
      });
    }
  },
  computed: _objectSpread(_objectSpread({}, Object(vuex__WEBPACK_IMPORTED_MODULE_0__["mapGetters"])(['isAuth', 'isLoading', 'setting'])), Object(vuex__WEBPACK_IMPORTED_MODULE_0__["mapState"])(['errors'])),
  methods: _objectSpread(_objectSpread(_objectSpread({}, Object(vuex__WEBPACK_IMPORTED_MODULE_0__["mapActions"])('siswa_auth', ['submit'])), Object(vuex__WEBPACK_IMPORTED_MODULE_0__["mapMutations"])(['CLEAR_ERRORS', 'SET_LOADING'])), {}, {
    postLogin: function postLogin() {
      var _this = this;

      this.SET_LOADING(true);
      this.submit(this.data).then(function (response) {
        if (_this.isAuth) {
          _this.$store.commit('siswa_user/ASSIGN_PESERTA_DETAIL', response.data);

          _this.CLEAR_ERRORS();

          _this.$router.replace({
            name: 'ujian.konfirm'
          });
        }
      })["catch"](function () {});
    },
    clearError: function clearError() {
      this.CLEAR_ERRORS();
    }
  })
});

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/pages/siswa/LoginUjian.vue?vue&type=template&id=5699bc2e&":
/*!**************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/pages/siswa/LoginUjian.vue?vue&type=template&id=5699bc2e& ***!
  \**************************************************************************************************************************************************************************************************************/
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
  return _c("div", { staticClass: "wrapper overlay-sidebar" }, [
    _c("div", { staticClass: "content" }, [
      _c("div", { staticClass: "panel-header bg-info-gradient" }, [
        _c("div", { staticClass: "page-inner py-5" }, [
          _c(
            "div",
            {
              staticClass:
                "d-flex align-items-left align-items-md-center flex-column flex-md-row"
            },
            [
              typeof _vm.setting.sekolah != "undefined"
                ? _c("div", { staticClass: "logo" }, [
                    _c("img", {
                      attrs: {
                        src:
                          _vm.setting.sekolah.logo != ""
                            ? "/storage/" + _vm.setting.sekolah.logo
                            : "/img/logo-white.png"
                      }
                    }),
                    _vm._v(" "),
                    _c("h2", { staticClass: "text-white pb-2 fw-bold" }, [
                      _vm._v(
                        _vm._s(
                          _vm.setting.sekolah.nama != ""
                            ? _vm.setting.sekolah.nama
                            : "ExtraordinaryCBT"
                        )
                      )
                    ])
                  ])
                : _vm._e()
            ]
          )
        ])
      ]),
      _vm._v(" "),
      _c("div", { staticClass: "container" }, [
        _c("div", { staticClass: "row justify-content-center" }, [
          _c("div", { staticClass: "col-lg-5" }, [
            _c("div", { staticClass: "card mt--5" }, [
              _c("div", { staticClass: "card-body" }, [
                _c("h4", [_vm._v("Selamat Datang")]),
                _vm._v(" "),
                _c("p", [
                  _vm._v(
                    "Silahkan login dengan no ujian dan password yang anda miliki"
                  )
                ]),
                _vm._v(" "),
                _c(
                  "form",
                  {
                    staticClass: "auth-form",
                    on: {
                      submit: function($event) {
                        $event.preventDefault()
                        return _vm.postLogin($event)
                      }
                    }
                  },
                  [
                    _c("div", { staticClass: "input-group mb-3" }, [
                      _vm._m(0),
                      _vm._v(" "),
                      _c("input", {
                        directives: [
                          {
                            name: "model",
                            rawName: "v-model",
                            value: _vm.data.no_ujian,
                            expression: "data.no_ujian"
                          }
                        ],
                        staticClass: "form-control active",
                        class: { "is-invalid": _vm.errors.no_ujian },
                        attrs: {
                          type: "text",
                          autofocus: "",
                          placeholder: "No peserta",
                          required: ""
                        },
                        domProps: { value: _vm.data.no_ujian },
                        on: {
                          keyup: _vm.clearError,
                          input: function($event) {
                            if ($event.target.composing) {
                              return
                            }
                            _vm.$set(_vm.data, "no_ujian", $event.target.value)
                          }
                        }
                      }),
                      _vm._v(" "),
                      _vm.errors.no_ujian
                        ? _c("div", { staticClass: "invalid-feedback" }, [
                            _vm._v(_vm._s(_vm.errors.no_ujian[0]))
                          ])
                        : _vm._e()
                    ]),
                    _vm._v(" "),
                    _c("div", { staticClass: "input-group mb-3" }, [
                      _vm._m(1),
                      _vm._v(" "),
                      _c("input", {
                        directives: [
                          {
                            name: "model",
                            rawName: "v-model",
                            value: _vm.data.password,
                            expression: "data.password"
                          }
                        ],
                        staticClass: "form-control",
                        class: { "is-invalid": _vm.errors.password },
                        attrs: {
                          type: "password",
                          placeholder: "Password",
                          required: ""
                        },
                        domProps: { value: _vm.data.password },
                        on: {
                          keyup: _vm.clearError,
                          input: function($event) {
                            if ($event.target.composing) {
                              return
                            }
                            _vm.$set(_vm.data, "password", $event.target.value)
                          }
                        }
                      }),
                      _vm._v(" "),
                      _vm.errors.password
                        ? _c("div", { staticClass: "invalid-feedback" }, [
                            _vm._v(_vm._s(_vm.errors.password[0]) + " ")
                          ])
                        : _vm._e()
                    ]),
                    _vm._v(" "),
                    _vm.errors
                      ? _c("p", {
                          staticClass: "text-danger mb-2",
                          domProps: { textContent: _vm._s(_vm.errors.invalid) }
                        })
                      : _vm._e(),
                    _vm._v(" "),
                    _c(
                      "b-button",
                      {
                        attrs: {
                          variant: "info",
                          size: "lg",
                          block: "",
                          disabled: _vm.isLoading,
                          type: "submit"
                        }
                      },
                      [
                        _vm._v(
                          "\n\t\t\t\t\t\t\t\t\t" +
                            _vm._s(_vm.isLoading ? "Loading.." : "Login") +
                            "\n\t\t\t\t\t\t\t\t"
                        )
                      ]
                    )
                  ],
                  1
                )
              ]),
              _vm._v(" "),
              _c("div", { staticClass: "card-footer" })
            ])
          ])
        ])
      ]),
      _vm._v(" "),
      _vm._m(2)
    ])
  ])
}
var staticRenderFns = [
  function() {
    var _vm = this
    var _h = _vm.$createElement
    var _c = _vm._self._c || _h
    return _c("div", { staticClass: "input-group-prepend rounded-0" }, [
      _c("span", { staticClass: "input-group-text rounded-0" }, [
        _c("i", { staticClass: "flaticon-avatar" })
      ])
    ])
  },
  function() {
    var _vm = this
    var _h = _vm.$createElement
    var _c = _vm._self._c || _h
    return _c("div", { staticClass: "input-group-prepend rounded-0" }, [
      _c("span", { staticClass: "input-group-text rounded-0" }, [
        _c("i", { staticClass: "flaticon-lock" })
      ])
    ])
  },
  function() {
    var _vm = this
    var _h = _vm.$createElement
    var _c = _vm._self._c || _h
    return _c("div", { staticClass: "nav-fixed-bottom" }, [
      _c("p", { staticClass: "text-center" }, [
        _vm._v("© ExtraordinaryCBT 2020 v2.0.0 by Shellrean")
      ])
    ])
  }
]
render._withStripped = true



/***/ }),

/***/ "./resources/js/pages/siswa/LoginUjian.vue":
/*!*************************************************!*\
  !*** ./resources/js/pages/siswa/LoginUjian.vue ***!
  \*************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _LoginUjian_vue_vue_type_template_id_5699bc2e___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./LoginUjian.vue?vue&type=template&id=5699bc2e& */ "./resources/js/pages/siswa/LoginUjian.vue?vue&type=template&id=5699bc2e&");
/* harmony import */ var _LoginUjian_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./LoginUjian.vue?vue&type=script&lang=js& */ "./resources/js/pages/siswa/LoginUjian.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _LoginUjian_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _LoginUjian_vue_vue_type_template_id_5699bc2e___WEBPACK_IMPORTED_MODULE_0__["render"],
  _LoginUjian_vue_vue_type_template_id_5699bc2e___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/pages/siswa/LoginUjian.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/pages/siswa/LoginUjian.vue?vue&type=script&lang=js&":
/*!**************************************************************************!*\
  !*** ./resources/js/pages/siswa/LoginUjian.vue?vue&type=script&lang=js& ***!
  \**************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_LoginUjian_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib??ref--4-0!../../../../node_modules/vue-loader/lib??vue-loader-options!./LoginUjian.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/pages/siswa/LoginUjian.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_LoginUjian_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/pages/siswa/LoginUjian.vue?vue&type=template&id=5699bc2e&":
/*!********************************************************************************!*\
  !*** ./resources/js/pages/siswa/LoginUjian.vue?vue&type=template&id=5699bc2e& ***!
  \********************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_LoginUjian_vue_vue_type_template_id_5699bc2e___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../node_modules/vue-loader/lib??vue-loader-options!./LoginUjian.vue?vue&type=template&id=5699bc2e& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/pages/siswa/LoginUjian.vue?vue&type=template&id=5699bc2e&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_LoginUjian_vue_vue_type_template_id_5699bc2e___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_LoginUjian_vue_vue_type_template_id_5699bc2e___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ })

}]);