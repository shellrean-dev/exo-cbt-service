function showSweetError(vue, err) {
    let message
    if (typeof err.message == 'undefined') {
        message = 'Terjadi kesalahan yang tidak dapat dijelaskan'
    } else {
        message = err.message
    }
    vue.$swal('Kesalahan', message,'error')
}

export { showSweetError }
