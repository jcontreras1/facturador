<!-- Modal -->
<div class="modal fade" id="modalEnviarFacturaMail" tabindex="-1" aria-labelledby="modalEnviarFacturaMailLabel" aria-hidden="true">
  <form id="formEnviarFacturaMail" method="post" >
    @csrf
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="modalEnviarFacturaMailLabel">Enviar por mail</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btnBloquear" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" id="btnSubmitEnviarFactura" class="btn btn-primary btnBloquear">Enviar</button>
        </div>
      </div>
    </div>
  </form>
</div>