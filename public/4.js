(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[4],{

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/pages/siswa/UjianPrepare.vue?vue&type=script&lang=js&":
/*!************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/pages/siswa/UjianPrepare.vue?vue&type=script&lang=js& ***!
  \************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/regenerator */ "./node_modules/@babel/runtime/regenerator/index.js");
/* harmony import */ var _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var vuex__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! vuex */ "./node_modules/vuex/dist/vuex.esm.js");
/* harmony import */ var _entities_notif__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../entities/notif */ "./resources/js/entities/notif.js");


function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }

function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }

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


/* harmony default export */ __webpack_exports__["default"] = ({
  name: 'PrepareUjian',
  created: function created() {
    this.starTime();
  },
  data: function data() {
    return {
      disable: true,
      time: '',
      starter: '',
      durasi: ''
    };
  },
  computed: _objectSpread(_objectSpread(_objectSpread({}, Object(vuex__WEBPACK_IMPORTED_MODULE_1__["mapState"])(['isLoading'])), Object(vuex__WEBPACK_IMPORTED_MODULE_1__["mapState"])('siswa_jadwal', {
    jadwal: function jadwal(state) {
      return state.banksoalAktif;
    },
    mulai: function mulai(state) {
      return state.banksoalAktif.jadwal.mulai;
    }
  })), Object(vuex__WEBPACK_IMPORTED_MODULE_1__["mapState"])('siswa_user', {
    peserta: function peserta(state) {
      return state.pesertaDetail;
    }
  })),
  methods: _objectSpread(_objectSpread({}, Object(vuex__WEBPACK_IMPORTED_MODULE_1__["mapActions"])('siswa_ujian', ['pesertaMulai'])), {}, {
    start: function start() {
      var _this = this;

      return _asyncToGenerator( /*#__PURE__*/_babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default.a.mark(function _callee() {
        return _babel_runtime_regenerator__WEBPACK_IMPORTED_MODULE_0___default.a.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                _context.prev = 0;
                _context.next = 3;
                return _this.pesertaMulai();

              case 3:
                _this.$router.replace({
                  name: 'ujian.while'
                });

                _context.next = 9;
                break;

              case 6:
                _context.prev = 6;
                _context.t0 = _context["catch"](0);

                _this.$bvToast.toast(_context.t0.message, Object(_entities_notif__WEBPACK_IMPORTED_MODULE_2__["errorToas"])());

              case 9:
              case "end":
                return _context.stop();
            }
          }
        }, _callee, null, [[0, 6]]);
      }))();
    },
    starTime: function starTime() {
      var _this2 = this;

      setInterval(function () {
        _this2.time = new Date();
      }, 1000);
    }
  }),
  watch: {
    time: function time() {
      if (this.starter < this.time) {
        this.disable = false;
      }
    }
  }
});

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/pages/siswa/UjianPrepare.vue?vue&type=template&id=332a5517&":
/*!****************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/pages/siswa/UjianPrepare.vue?vue&type=template&id=332a5517& ***!
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
    _c("div", { staticClass: "row" }, [
      _c("div", { staticClass: "col-sm-8" }, [
        _c("div", { staticClass: "page-inner mt--5" }, [
          _vm.jadwal
            ? _c("div", { staticClass: "card card-bg" }, [
                _vm._m(0),
                _vm._v(" "),
                _c("div", { staticClass: "card-body" }, [
                  _c(
                    "form",
                    {
                      staticClass: "form-custom form-ajax",
                      attrs: { id: "fmTes", name: "fmTes", method: "POST" }
                    },
                    [
                      _c("div", { staticClass: "form-group" }, [
                        _c("label", [_vm._v("Mata Pelajaran")]),
                        _vm._v(" "),
                        _c("p", { staticClass: "form-control-static" }, [
                          _vm._v(_vm._s(_vm.jadwal.matpel) + " ")
                        ])
                      ]),
                      _vm._v(" "),
                      _c("div", { staticClass: "form-group" }, [
                        _c("label", [_vm._v("Alokasi Waktu Tes")]),
                        _vm._v(" "),
                        _c("p", { staticClass: "form-control-static" }, [
                          _vm._v(
                            _vm._s(Math.floor(_vm.jadwal.jadwal.lama / 60)) +
                              " Menit  "
                          )
                        ])
                      ]),
                      _vm._v(" "),
                      _c("div", { staticClass: "form-group" }, [
                        _c("label", [_vm._v("Waktu mulai")]),
                        _vm._v(" "),
                        _c("p", { staticClass: "form-control-static" }, [
                          _vm._v(_vm._s(_vm.mulai) + " ")
                        ])
                      ])
                    ]
                  )
                ]),
                _vm._v(" "),
                _c("div", { staticClass: "card-footer" })
              ])
            : _vm._e()
        ])
      ]),
      _vm._v(" "),
      _c("div", { staticClass: "col-sm-4" }, [
        _c("div", { staticClass: "page-inner mt--5" }, [
          _vm.jadwal
            ? _c("div", { staticClass: "card card-bg" }, [
                _c("div", { staticClass: "card-body" }, [
                  _c("p", [
                    _vm._v(
                      "Tombol MULAI hanya akan muncul apabila waktu sekarang sudah melewati waktu mulai tes"
                    )
                  ]),
                  _vm._v(" "),
                  !_vm.disable
                    ? _c(
                        "button",
                        {
                          staticClass:
                            "btn btn-info w-100 rounded-pill btn-form-ajax",
                          attrs: { type: "button", disabled: _vm.isLoading },
                          on: { click: _vm.start }
                        },
                        [
                          _vm._v(
                            "\n                                " +
                              _vm._s(_vm.isLoading ? "Loading..." : "MULAI") +
                              "\n                            "
                          )
                        ]
                      )
                    : _vm._e()
                ])
              ])
            : _vm._e()
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
      _c("h4", [_vm._v("Konfirmasi Tes")])
    ])
  }
]
render._withStripped = true



/***/ }),

/***/ "./resources/js/pages/siswa/UjianPrepare.vue":
/*!***************************************************!*\
  !*** ./resources/js/pages/siswa/UjianPrepare.vue ***!
  \***************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _UjianPrepare_vue_vue_type_template_id_332a5517___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./UjianPrepare.vue?vue&type=template&id=332a5517& */ "./resources/js/pages/siswa/UjianPrepare.vue?vue&type=template&id=332a5517&");
/* harmony import */ var _UjianPrepare_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./UjianPrepare.vue?vue&type=script&lang=js& */ "./resources/js/pages/siswa/UjianPrepare.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _UjianPrepare_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _UjianPrepare_vue_vue_type_template_id_332a5517___WEBPACK_IMPORTED_MODULE_0__["render"],
  _UjianPrepare_vue_vue_type_template_id_332a5517___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/pages/siswa/UjianPrepare.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/pages/siswa/UjianPrepare.vue?vue&type=script&lang=js&":
/*!****************************************************************************!*\
  !*** ./resources/js/pages/siswa/UjianPrepare.vue?vue&type=script&lang=js& ***!
  \****************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_UjianPrepare_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib??ref--4-0!../../../../node_modules/vue-loader/lib??vue-loader-options!./UjianPrepare.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/pages/siswa/UjianPrepare.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_UjianPrepare_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/pages/siswa/UjianPrepare.vue?vue&type=template&id=332a5517&":
/*!**********************************************************************************!*\
  !*** ./resources/js/pages/siswa/UjianPrepare.vue?vue&type=template&id=332a5517& ***!
  \**********************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_UjianPrepare_vue_vue_type_template_id_332a5517___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../node_modules/vue-loader/lib??vue-loader-options!./UjianPrepare.vue?vue&type=template&id=332a5517& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/pages/siswa/UjianPrepare.vue?vue&type=template&id=332a5517&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_UjianPrepare_vue_vue_type_template_id_332a5517___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_UjianPrepare_vue_vue_type_template_id_332a5517___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ })

}]);