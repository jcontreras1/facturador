<template>
  <h3 class="d-flex justify-content-between align-items-center">
    Crear Comprobante B
    <button type="button" class="btn btn-primary" @click="back">
      <i class="fas fa-chevron-left"></i>
    </button>
  </h3>
  <hr>
  <form @submit.prevent="submitForm">
    <div class="card mb-3">
      <div class="card-header fs-4"><i class="fas fa-file-invoice-dollar"></i> Datos de la Factura</div>
      <div class="card-body">
        <div class="row mb-3">
          <div class="col-12 col-md-6">
            <label for="fecha">Fecha</label>
            <input type="date" v-model="form.fecha" class="form-control" required>
          </div>
          <div class="col-12 col-md-6">
            <label for="concepto">Concepto</label>
            <select v-model="form.concepto" class="form-select" required>
              <option value="1">Productos</option>
              <option value="2">Servicios</option>
              <option value="3">Productos y Servicios</option>
            </select>
          </div>
        </div>
        <div class="row" v-if="form.concepto == 2 || form.concepto == 3">
          <div class="col-12 col-md-4">
            <label for="fechaInicioServicios">Fecha de inicio de servicios</label>
            <input type="date" v-model="form.fechaInicioServicios" class="form-control" required>
          </div>
          <div class="col-12 col-md-4">
            <label for="fechaFinServicios">Fecha de finalización de servicios</label>
            <input type="date" v-model="form.fechaFinServicios" class="form-control" required>
          </div>
          <div class="col-12 col-md-4">
            <label for="fechaVencimientoPago">Fecha de vencimiento para el pago</label>
            <input type="date" v-model="form.fechaVencimientoPago" class="form-control" required>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header fs-4"><i class="fas fa-user-alt"></i> Cliente</div>
      <div class="card-body">
        <div class="row">
          <div class="col-12 col-md-3">
            <label for="condicionIva">Condición IVA</label>
            <select :disabled="clienteFijo" v-model="form.condicionIva" class="form-select" required>
              <option v-for="condicion in condicionesIva" :key="condicion.id" :value="condicion.id">{{ condicion.descripcion }}</option>
            </select>
          </div>
          <div class="col-12 col-md-3">
            <label for="tipoDocumentoId">Tipo de Cliente</label>
            <select :disabled="clienteFijo" v-model="form.tipoDocumentoId" @change="handletipoDocumentoIdChange" class="form-select" required>
              <option value="80">CUIT</option>
              <option value="86">CUIL</option>
              <option value="96">DNI</option>
              <option value="99">Consumidor Final</option>
            </select>
          </div>
          <div class="col-12 col-md-6 mb-3">
            <label for="documento">DNI/CUIL/CUIT</label>
            <input type="number" v-model.text="form.documento" :disabled="isDocumentoDisabled || clienteFijo" :class="{'is-invalid' : invalidDocument}" class="form-control" @focusout="onDocumentoFocusOut">
            <span class="invalid-feedback">El campo no coincide con el tipo de documento, o está incompleto.</span>
          </div>
          <div class="col-12 col-md-6">
            <label for="razonSocial">Razón Social <i v-show="isLoading" class="fas fa-spinner fa-spin"></i></label>
            <input type="text" v-model="form.razonSocial" class="form-control" readonly>
          </div>
          <div class="col-12 col-md-6">
            <label for="domicilio">Domicilio <i v-show="isLoading" class="fas fa-spinner fa-spin"></i></label>
            <input type="text" v-model="form.domicilio" class="form-control" readonly>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header fs-4"><i class="fas fa-list"></i> Detalle</div>
      <div class="card-body">
        <button type="button" class="btn btn-primary my-2" @click="agregarLinea">Agregar Línea (F7) <i class="fas fa-caret-down"></i></button>
        <div v-for="(linea, index) in lineas" :key="index" class="linea-detalle row mb-3">
          <div class="col-12 col-md-2">
            <label for="descripcion{{ index }}">Descripción</label>
            <textarea v-model="linea.descripcion" class="form-control form-control-sm descripcion"></textarea>
          </div>
          <div class="col-12 col-md-1">
            <label for="cantidad{{ index }}">Cantidad</label>
            <input type="number" v-model="linea.cantidad" class="form-control form-control-sm" @input="calcularSubtotal(index)" min="0" step="0.01" required>
          </div>
          <div class="col-12 col-md-1">
            <label for="unidadDeMedida{{ index }}">Unidad</label>
            <select v-model="linea.unidadDeMedida" class="form-select form-select-sm" @change="calcularSubtotal(index)">
              <option v-for="um in unidadesDeMedida" :key="um" :value="um">{{ um }}</option>
            </select>
          </div>
          <div class="col-12 col-md-1">
            <label for="precioUnitario{{ index }}">$ Unit.</label>
            <input type="number" v-model="linea.precioUnitario" class="form-control form-control-sm" @input="calcularSubtotal(index)" min="0" step="0.01" required>
          </div>
          <div class="col-12 col-md-1">
            <label for="bonificacion{{ index }}">% Bonif.</label>
            <input type="number" v-model="linea.bonificacion" class="form-control form-control-sm" @input="calcularSubtotal(index)" min="0" max="100" step="0.01" required>
          </div>
          <div class="col-12 col-md-1">
            <label for="importeBonificado{{ index }}">$ Bonif.</label>
            <input type="number" v-model="linea.importeBonificado" class="form-control form-control-sm" step="0.01" readonly disabled>
          </div>
          <div class="col-12 col-md-1">
            <label for="subtotal{{ index }}">Subtotal</label>
            <input type="number" v-model="linea.subtotal" class="form-control form-control-sm" step="0.01" readonly disabled>
          </div>
          <div class="col-12 col-md-1">
            <label for="iva{{ index }}">IVA</label>
            <select v-model="linea.iva" class="form-select form-select-sm" @change="calcularSubtotal(index)">
              <option v-for="iva in props.ivas" :key="iva.id" :value="iva.id">{{ iva.descripcion }}</option>
            </select>
          </div>
          <div class="col-12 col-md-2">
            <label for="subtotalConIva{{ index }}">Subtotal c/IVA</label>
            <input type="number" v-model="linea.subtotalConIva" class="form-control form-control-sm" step="0.01" readonly disabled>
          </div>
          <div class="col-12 col-md-1 d-flex align-items-end">
            <button type="button" class="btn btn-danger btn-sm" title="Eliminar esta línea" @click="eliminarLinea(index)">
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-success mt-3 float-end" :disabled="invalidDocument || isLoading">
      <span v-if="isLoading">
        <i class="fas fa-spinner fa-spin"></i> Enviando...
      </span>
      <span v-else>
        Emitir Comprobante
      </span>
    </button>

    <div class="mt-3">
      <h4>Total: <span>{{ importeTotalFormatoArgentino }}</span></h4>
    </div>
  </form>
</template>

<script setup>
import { ref, reactive, computed, defineProps, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const props = defineProps({
  condicionesIva: Object,
  cliente: {
    type: Object,
    default: null,
  },
  ivas: Object,
})

const form = reactive({
  tipoDocumentoId: '99',
  documento: '',
  razonSocial: '',
  domicilio: '',
  condicionIva: null,
  concepto: '2',
  fecha: new Date().toISOString().split('T')[0],
  fechaInicioServicios: new Date().toISOString().split('T')[0],
  fechaFinServicios: new Date().toISOString().split('T')[0],
  fechaVencimientoPago: new Date().toISOString().split('T')[0],
  importeNeto: 0,
  importeTotal: 0,
  lineas: null,
})

const lineas = ref([])
const unidadesDeMedida = ['unidad', 'metros', 'kilos', 'litros']
const isLoading = ref(false)
const roundAmount = (value) => Math.round((Number(value) + Number.EPSILON) * 100) / 100

const importeTotal = computed(() => {
  return roundAmount(lineas.value.reduce((acc, linea) => acc + Number(linea.subtotalConIva || 0), 0)).toFixed(2)
})

const importeNeto = computed(() => {
  return roundAmount(lineas.value.reduce((acc, linea) => acc + Number(linea.subtotal || 0), 0)).toFixed(2)
})

const importeTotalFormatoArgentino = computed(() => {
  return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(importeTotal.value)
})

const isDocumentoDisabled = computed(() => form.tipoDocumentoId === '99')
const back = () => { history.back() }
const invalidDocument = computed(() => {
  if (form.tipoDocumentoId === '99') { return false }
  if (form.tipoDocumentoId === '80' && String(form.documento).length !== 11) { return true }
  if (form.tipoDocumentoId === '86' && String(form.documento).length !== 11) { return true }
  if (form.tipoDocumentoId === '96' && String(form.documento).length === 0) { return true }
  return false
})

const clienteFijo = computed(() => {
  return props.cliente && props.cliente.cuit !== ''
})

const condicionPorDescripcion = (descripcion) => {
  return props.condicionesIva.find((condicion) => condicion.descripcion === descripcion)?.id ?? null
}

const handletipoDocumentoIdChange = () => {
  if (form.tipoDocumentoId === '99') {
    form.documento = ''
    form.condicionIva = condicionPorDescripcion('Consumidor Final')
    return
  }

  if (form.tipoDocumentoId === '96') {
    form.condicionIva = condicionPorDescripcion('Consumidor Final')
  }
}

const onDocumentoFocusOut = async () => {
  if (invalidDocument.value || isDocumentoDisabled.value) {
    return
  }

  isLoading.value = true

  let url = `/api/contribuyente/${form.documento}`

  if ([80, 86].includes(parseInt(form.tipoDocumentoId))) {
    url += '?tipo=cuit'
  } else {
    url += '?tipo=dni'
  }

  try {
    const response = await axios.get(url)
    form.razonSocial = response.data.razonSocial
    form.domicilio = response.data.domicilio
  } catch (error) {
    const msgError = error.response?.data?.error ?? 'No se encontró el usuario con el documento ingresado.'
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: msgError,
    })
  } finally {
    isLoading.value = false
  }
}

const calcularSubtotal = (index) => {
  calcularBonificacion(index)
  const linea = lineas.value[index]
  linea.subtotal = roundAmount((Number(linea.precioUnitario) * Number(linea.cantidad)) - Number(linea.importeBonificado))
  const ivaSeleccionado = props.ivas.find((iva) => iva.id === linea.iva)
  if (ivaSeleccionado) {
    linea.subtotalConIva = roundAmount(linea.subtotal + (linea.subtotal * (ivaSeleccionado.iva / 100)))
  } else {
    linea.subtotalConIva = linea.subtotal
  }
  lineas.value[index] = { ...linea }
}

const calcularBonificacion = (index) => {
  const linea = lineas.value[index]
  linea.importeBonificado = roundAmount((Number(linea.precioUnitario) * Number(linea.cantidad)) * (Number(linea.bonificacion) / 100))
}

const agregarLinea = () => {
  const idLinea = lineas.value.length
  const ivaPorDefecto = props.ivas.find((iva) => iva.descripcion === '21%')?.id ?? props.ivas[0]?.id ?? null
  lineas.value.push({
    id: idLinea,
    codigo: '',
    descripcion: '',
    cantidad: 1,
    unidadDeMedida: 'unidad',
    precioUnitario: 0,
    bonificacion: 0,
    importeBonificado: 0,
    subtotal: 0,
    iva: ivaPorDefecto,
    subtotalConIva: 0,
  })
  setTimeout(() => {
    const lastDescripcionField = document.querySelectorAll('.descripcion')
    if (lastDescripcionField.length > 0) {
      lastDescripcionField[lastDescripcionField.length - 1].focus()
    }
  }, 0)
}

const eliminarLinea = (index) => {
  lineas.value.splice(index, 1)
}

onMounted(() => {
  form.condicionIva = condicionPorDescripcion('Consumidor Final')

  document.addEventListener('keydown', (event) => {
    if (event.key === 'F7') {
      event.preventDefault()
      agregarLinea()
    }
  })

  if (props.cliente) {
    if (props.cliente.condicion_iva_receptor_id) {
      form.condicionIva = props.cliente.condicion_iva_receptor_id
    } else if (props.cliente.cuit?.length === 11) {
      form.condicionIva = condicionPorDescripcion('IVA Sujeto Exento')
    } else {
      form.condicionIva = condicionPorDescripcion('Consumidor Final')
    }

    if (props.cliente.tipo_documento_afip) {
      form.tipoDocumentoId = String(props.cliente.tipo_documento_afip)
    } else if (props.cliente.cuit?.length === 11) {
      form.tipoDocumentoId = '80'
    } else if (props.cliente.cuit?.length === 0) {
      form.tipoDocumentoId = '99'
    } else {
      form.tipoDocumentoId = '96'
    }

    form.documento = props.cliente.cuit
    form.razonSocial = props.cliente.nombre
    form.domicilio = props.cliente.direccion
  }
})

const submitForm = () => {
  form.importeTotal = importeTotal.value
  form.importeNeto = importeNeto.value
  form.lineas = lineas.value

  if (lineas.value.length === 0) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Debe haber al menos una línea de detalle en la factura.',
    })
    return
  }

  if (importeTotal.value == 0) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'El total de la factura no puede ser 0.',
    })
    return
  }

  Swal.fire({
    title: '¿Estás seguro?',
    text: 'Estás por emitir una factura. Una vez emitida no podrá ser modificada.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, emitir',
    cancelButtonText: 'Cancelar',
  }).then((result) => {
    if (result.isConfirmed) {
      let url = '/api/comprobante/b'
      if (props.cliente) {
        url += `?cliente=${props.cliente.id}`
      }
      axios.post(url, form)
        .then(() => {
          location.href = '/comprobantes'
        })
        .catch((error) => {
          const mensaje = error.response?.data?.message ?? 'Ocurrió un error al emitir la factura. Por favor, intente nuevamente.'
          const codigo = error.response?.status
          const title = codigo == 500 ? 'Error en el servidor' : 'Error'
          Swal.fire({
            icon: 'error',
            title,
            text: mensaje,
            footer: 'Si considera que es un error del sistema, por favor comuníquese con el administrador.',
          })
        })
    }
  })
}
</script>

<style scoped>
.form-control.form-control-sm{
  font-size: 0.75rem;
}

.form-select.form-select-sm{
  font-size: 0.75rem;
}
</style>
  
  