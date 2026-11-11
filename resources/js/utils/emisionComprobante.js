import { ref } from 'vue'
import Swal from 'sweetalert2'

export const emisionEnCurso = ref(false)

export function iniciarEmisionComprobante(title = 'Emitiendo comprobante...') {
  if (emisionEnCurso.value) {
    return false
  }

  emisionEnCurso.value = true
  Swal.fire({
    title,
    text: 'Por favor, esperá mientras se procesa la solicitud.',
    allowOutsideClick: false,
    allowEscapeKey: false,
    allowEnterKey: false,
    showConfirmButton: false,
    showCancelButton: false,
    didOpen: () => Swal.showLoading(),
  })

  return true
}

export function finalizarEmisionComprobante() {
  if (emisionEnCurso.value) {
    emisionEnCurso.value = false
    Swal.close()
  }
}
