<div class="modal fade" id="modal_create_item" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formCreateItem" method="post" route="{{route('items.store')}}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Crear Servicio</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="codigo" class="form-label">Código</label>
                        <input type="text" class="form-control" id="codigo" name="codigo">
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción <span class="text-danger">*</span></label>
                        <input type="text" autocomplete="off" class="form-control" id="descripcion" name="descripcion" required>
                    </div>
                    <div class="mb-3">
                        <label for="valor" class="form-label">Valor Unitario <span class="text-danger">*</span></label>
                        <input type="number" autocomplete="off" class="form-control" id="valor" name="precio_unitario" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button id="btnGuardarItem" type="button" class="btn btn-primary">Crear</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const btn = document.getElementById('btnGuardarItem');
    const descripcionInput = document.getElementById('descripcion');
    const valorInput = document.getElementById('valor');
    
    btn.addEventListener('click', () => {
        btn.disabled = true;
        // Verificamos si los campos de descripción o valor están vacíos
        if (descripcionInput.value.trim() === '' || valorInput.value.trim() === '') {
            Swal.fire({
                icon: 'error',
                title: 'Los campos descripción y valor son obligatorios',
                //   text: 'Debes completar los campos obligatorios',
            });
            btn.disabled = false;
            return;
        }
        btn.disabled = false;
        document.getElementById('formCreateItem').submit();
    });
</script>
@endpush