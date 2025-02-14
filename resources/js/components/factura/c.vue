<template>
    <div class="container">
      <h3>Crear Comprobante C</h3>
      <hr>
      <form @submit.prevent="submitForm">
        <input type="hidden" v-model="importeTotalEscondido" required>
  
        <!-- Datos de la Factura -->
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
  
        <!-- Cliente -->
        <div class="card mb-3">
          <div class="card-header fs-4"><i class="fas fa-user-alt"></i> Cliente</div>
          <div class="card-body">
            <div class="row">
              <div class="col-12 col-md-3">
                <label for="condicionIva">Condición IVA</label>
                <select v-model="form.condicionIva" class="form-select" required>
                  <option v-for="condicion in condicionesIva" :key="condicion.id" :value="condicion.id">{{ condicion.descripcion }}</option>
                  </select> 
              </div>
              <div class="col-12 col-md-3">
                <label for="tipoDocumento">Tipo de Cliente</label>
                <select v-model="form.tipoDocumento" @change="handleTipoDocumentoChange" class="form-select" required>
                  <option value="80">CUIT</option>
                  <option value="86">CUIL</option>
                  <option value="96">DNI</option>
                  <option value="99" selected>Consumidor Final</option>
                </select>
              </div>
              <div class="col-12 col-md-6 mb-3">
                <label for="documento">DNI/CUIL/CUIT</label>
                <input type="number" v-model.text="form.documento" :disabled="isDocumentoDisabled" :class="{'is-invalid' : invalidDocument}" class="form-control" @focusout="onDocumentoFocusOut">
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
  
        <!-- Detalle de la factura -->
        <div class="card">
          <div class="card-header fs-4"><i class="fas fa-list"></i> Detalle</div>
          <div class="card-body">
            <button type="button" class="btn btn-primary my-2" @click="agregarLinea">Agregar Línea <i class="fas fa-caret-down"></i></button>
            <div v-for="(linea, index) in lineas" :key="index" class="linea-detalle row mb-3">
              <div class="col-12 col-md-3">
                <label for="descripcion{{ index }}">Descripción</label>
                <input type="text" v-model="linea.descripcion" class="form-control" @input="calcularSubtotal(index)">
              </div>
              <div class="col-12 col-md-1">
                <label for="cantidad{{ index }}">Cantidad</label>
                <input type="number" v-model="linea.cantidad" class="form-control" @input="calcularSubtotal(index)" min="0" required>
              </div>
              <div class="col-12 col-md-1">
                <label for="unidadDeMedida{{ index }}">Unidad</label>
                <select v-model="linea.unidadDeMedida" class="form-select" @change="calcularSubtotal(index)">
                  <option v-for="um in unidadesDeMedida" :key="um" :value="um">{{ um }}</option>
                </select>
              </div>
              <div class="col-12 col-md-2">
                <label for="precioUnitario{{ index }}">Precio Unit.</label>
                <input type="number" v-model="linea.precioUnitario" class="form-control" @input="calcularSubtotal(index)" required>
              </div>
              <div class="col-12 col-md-1">
                <label for="bonificacion{{ index }}">% Bonif.</label>
                <input type="number" v-model="linea.bonificacion" class="form-control" @input="calcularBonificacion(index)" required>
              </div>
              <div class="col-12 col-md-1">
                <label for="importeBonificado{{ index }}">$ Bonif.</label>
                <input type="number" v-model="linea.importeBonificado" class="form-control" readonly>
              </div>
              <div class="col-12 col-md-2">
                <label for="subtotal{{ index }}">Subtotal</label>
                <input type="number" v-model="linea.subtotal" class="form-control" readonly>
              </div>
              <div class="col-12 col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger" @click="eliminarLinea(index)">
                  <i class="fas fa-trash-alt"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
  
        <button type="submit" class="btn btn-success mt-3 float-end">Emitir Comprobante</button>
  
        <div class="mt-3">
          <h4>Total: $<span>{{ importeTotal }}</span></h4>
        </div>
      </form>

    </div>
  </template>
  
  <script setup>
  import { ref, reactive, computed, defineProps } from 'vue'
  import axios from 'axios'
  import Swal from 'sweetalert2'
  
  const props = defineProps({
    condicionesIva: Object,
  })

  // Reactive state
  const form = reactive({
    fecha: new Date().toISOString().split('T')[0],
    concepto: '2',
    tipoDocumento: '99',
    documento: '',
    condicionIva: 7,
    razonSocial: '',
    fechaInicioServicios : new Date().toISOString().split('T')[0],
    fechaFinServicios : new Date().toISOString().split('T')[0],
    fechaVencimientoPago : new Date().toISOString().split('T')[0],
    domicilio: '',
  })
  
  const lineas = ref([])
  const unidadesDeMedida = ['unidad', 'metros', 'kilos', 'litros']
  const isLoading = ref(false)
  const importeTotalEscondido = ref(0)
  
  // Computed property for total amount
  const importeTotal = computed(() => {
    return lineas.value.reduce((acc, linea) => acc + linea.subtotal, 0).toFixed(2)
  })
  
  // Computed property to disable document input for "Consumidor Final"
  const isDocumentoDisabled = computed(() => form.tipoDocumento === '99')
  
  const invalidDocument = computed(() => {
    //si es consumidor final no se valida
    if(form.tipoDocumento === '99') { return false; }
    if(form.tipoDocumento === '80' && String(form.documento).length !== 11) { return true; }
    if(form.tipoDocumento === '86' && String(form.documento).length !== 11) { return true; }
    if(form.tipoDocumento === '96' && String(form.documento).length === 0) { return true; }
    return false;
  })
  
  // Functions
  const handleTipoDocumentoChange = () => {
    if (form.tipoDocumento === '99') {
      form.documento = ''
    }
  }

  //averigua los datos extras del contribuyente
  const onDocumentoFocusOut = async () => {

    if (invalidDocument.value) {
      return;
    }
    isLoading.value = true

    let url = `/api/contribuyente/${form.documento}`;
    if([80,86].includes(form.tipoDocumento)){
      url += `?tipo=cuit`
    }else{
      url += `?tipo=dni`
    }

    try {
      const response = await axios.get(url)
      form.razonSocial = response.data.razonSocial
      form.domicilio = response.data.domicilio
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se encontró el usuario con el documento ingresado.',
      })
    } finally {
      isLoading.value = false
    }
  }
  
  const calcularSubtotal = (index) => {
    const linea = lineas.value[index]
    linea.subtotal = (linea.precioUnitario * linea.cantidad) - linea.importeBonificado
    lineas.value[index] = { ...linea }
  }
  
  const calcularBonificacion = (index) => {
    const linea = lineas.value[index]
    linea.importeBonificado = (linea.precioUnitario * linea.cantidad) * (linea.bonificacion / 100)
    calcularSubtotal(index)
  }
  
  const agregarLinea = () => {
    const idLinea = lineas.value.length
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
    })
  }
  
  const eliminarLinea = (index) => {
    lineas.value.splice(index, 1)
  }
  
  const submitForm = () => {

    return;
    if (lineas.value.length === 0) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Debe haber al menos una línea de detalle en la factura.',
      })
      return
    }
  
    if (importeTotalEscondido.value == 0) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'El total de la factura no puede ser 0.',
      })
      return
    }
  
    // Submit form logic here
  }
  </script>
  
  <style scoped>
  /* Bootstrap styles are included by default */
  </style>
  