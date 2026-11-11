import { computed, ref, watch } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { emisionEnCurso } from './emisionComprobante.js'

export function useClienteFactura(form, props, invalidDocument) {
  const isLoading = ref(false)
  const documentoConsultado = ref('')
  const receptorValido = ref(false)
  const claveDocumento = () => `${form.tipoDocumentoId}:${form.documento}`
  let ultimaSolicitud = 0

  form.cliente = props.cliente?.id ?? null
  form.agregarComoCliente = false

  watch([() => form.documento, () => form.tipoDocumentoId], () => {
    if (props.cliente) return
    ultimaSolicitud++
    documentoConsultado.value = ''
    receptorValido.value = false
    isLoading.value = false
    form.cliente = null
    form.agregarComoCliente = false
    form.razonSocial = ''
    form.domicilio = ''
  }, { flush: 'sync' })

  const mostrarAgregarCliente = computed(() =>
    !props.cliente && !form.cliente && !isLoading.value && receptorValido.value &&
    documentoConsultado.value === claveDocumento() && String(form.tipoDocumentoId) !== '99'
  )

  const onDocumentoFocusOut = async () => {
    if (props.cliente || String(form.tipoDocumentoId) === '99') return true
    if (invalidDocument.value || emisionEnCurso.value || isLoading.value) return false
    if (documentoConsultado.value === claveDocumento()) return receptorValido.value

    const clave = claveDocumento()
    const solicitud = ++ultimaSolicitud
    isLoading.value = true

    try {
      const tipo = String(form.tipoDocumentoId) === '96' ? 'dni' : 'cuit'
      const { data } = await axios.get(`/api/contribuyente/${form.documento}?tipo=${tipo}&buscarCliente=1`)
      if (solicitud !== ultimaSolicitud) return false

      form.razonSocial = data.razonSocial
      form.domicilio = data.domicilio ?? ''
      form.cliente = data.cliente?.id ?? null
      documentoConsultado.value = clave
      const condicion = data.cliente?.condicion_iva_receptor_id
      if (condicion != null) {
        form.condicionIva = condicion
        if (!props.condicionesIva.some((iva) => Number(iva.id) === Number(condicion))) {
          Swal.fire({
            icon: 'warning',
            title: 'Tipo de factura incorrecto',
            text: 'La condición de IVA del cliente registrado no corresponde a esta factura.',
          })
          return false
        }
      }
      receptorValido.value = true
      return true
    } catch (error) {
      if (solicitud !== ultimaSolicitud) return false
      Swal.fire({
        icon: 'error',
        title: 'Error al buscar el receptor',
        text: error.response?.data?.error ?? 'No se pudo consultar el documento. Volvé a intentarlo.',
      })
      return false
    } finally {
      if (solicitud === ultimaSolicitud) isLoading.value = false
    }
  }

  return { isLoading, mostrarAgregarCliente, onDocumentoFocusOut }
}
