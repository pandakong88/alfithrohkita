<script src="{{ asset('kaiadmin/assets/js/core/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('kaiadmin/assets/js/core/popper.min.js') }}"></script>
<script src="{{ asset('kaiadmin/assets/js/core/bootstrap.min.js') }}"></script>

<script src="{{ asset('kaiadmin/assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>
<script src="{{ asset('kaiadmin/assets/js/plugin/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('kaiadmin/assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>

<script src="{{ asset('kaiadmin/assets/js/kaiadmin.min.js') }}"></script>


<script src="{{ asset('kaiadmin/assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
<script src="{{ asset('kaiadmin/assets/js/plugin/select2/select2.full.min.js') }}"></script>

{{-- Global SweetAlert Toast / Popup for Session Success, Error & Validation Errors --}}
@if(session('success'))
<script>
    $(document).ready(function() {
        swal({
            title: "Berhasil!",
            text: {!! json_encode(session('success')) !!},
            icon: "success",
            buttons: {
                confirm: {
                    className: 'btn btn-success'
                }
            }
        });
    });
</script>
@endif

@if(session('error'))
<script>
    $(document).ready(function() {
        swal({
            title: "Gagal!",
            text: {!! json_encode(session('error')) !!},
            icon: "error",
            buttons: {
                confirm: {
                    className: 'btn btn-danger'
                }
            }
        });
    });
</script>
@endif

@if($errors->any())
<script>
    $(document).ready(function() {
        swal({
            title: "Validasi Gagal!",
            text: {!! json_encode(implode("\n", $errors->all())) !!},
            icon: "warning",
            buttons: {
                confirm: {
                    className: 'btn btn-warning'
                }
            }
        });
    });
</script>
@endif