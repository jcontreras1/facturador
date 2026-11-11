import assert from 'node:assert/strict'
import test from 'node:test'
import { effectScope, reactive, ref } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { useClienteFactura } from '../../resources/js/utils/clienteFactura.js'

function receptor(t, cliente = null) {
  const scope = effectScope()
  t.after(() => scope.stop())
  const form = reactive({
    tipoDocumentoId: '80', documento: '20123456789', razonSocial: '', domicilio: '', condicionIva: 1,
  })
  const estado = scope.run(() => useClienteFactura(form, {
    cliente, condicionesIva: [{ id: 1 }],
  }, ref(false)))
  return { form, ...estado }
}

test('un cliente local completa sus datos y oculta el alta sin repetir la consulta al tabular', async (t) => {
  const get = t.mock.method(axios, 'get', async () => ({ data: {
    razonSocial: 'Cliente local', domicilio: 'Domicilio local',
    cliente: { id: 7, condicion_iva_receptor_id: 1 },
  } }))
  const estado = receptor(t)
  assert.equal(estado.mostrarAgregarCliente.value, false)
  assert.equal(await estado.onDocumentoFocusOut(), true)
  assert.equal(estado.form.cliente, 7)
  assert.equal(estado.form.razonSocial, 'Cliente local')
  assert.equal(estado.mostrarAgregarCliente.value, false)
  await estado.onDocumentoFocusOut()
  assert.equal(get.mock.callCount(), 1)
})

test('el alta solo aparece para un receptor nuevo y se reinicia al cambiar de documento o tipo', async (t) => {
  const get = t.mock.method(axios, 'get', async () => ({ data: {
    razonSocial: 'Nuevo receptor', domicilio: 'Domicilio ARCA', cliente: null,
  } }))
  const estado = receptor(t)
  await estado.onDocumentoFocusOut()
  assert.equal(estado.mostrarAgregarCliente.value, true)
  assert.equal(estado.form.agregarComoCliente, false)
  estado.form.agregarComoCliente = true
  estado.form.documento = '30999999999'
  assert.equal(estado.form.agregarComoCliente, false)
  assert.equal(estado.form.razonSocial, '')
  assert.equal(estado.mostrarAgregarCliente.value, false)
  await estado.onDocumentoFocusOut()
  assert.equal(estado.mostrarAgregarCliente.value, true)
  estado.form.tipoDocumentoId = '96'
  estado.form.documento = '12345678'
  await estado.onDocumentoFocusOut()
  assert.equal(get.mock.calls.at(-1).arguments[0], '/api/contribuyente/12345678?tipo=dni&buscarCliente=1')
  estado.form.tipoDocumentoId = '99'
  await estado.onDocumentoFocusOut()
  assert.equal(get.mock.callCount(), 3)
  assert.equal(estado.mostrarAgregarCliente.value, false)
})

test('se ignoran respuestas tardias para un documento que ya cambio', async (t) => {
  let responder
  t.mock.method(axios, 'get', () => new Promise((resolve) => { responder = resolve }))
  const estado = receptor(t)
  const consulta = estado.onDocumentoFocusOut()
  estado.form.documento = '30999999999'
  responder({ data: { razonSocial: 'Respuesta anterior', cliente: { id: 7 } } })
  assert.equal(await consulta, false)
  assert.equal(estado.form.razonSocial, '')
  assert.equal(estado.form.cliente, null)
  assert.equal(estado.mostrarAgregarCliente.value, false)
})

test('un error se informa y permite reintentar sin mostrar el alta', async (t) => {
  const get = t.mock.method(axios, 'get', async () => { throw new Error('Sin conexión') })
  const fire = t.mock.method(Swal, 'fire', () => {})
  const estado = receptor(t)
  assert.equal(await estado.onDocumentoFocusOut(), false)
  assert.equal(fire.mock.callCount(), 1)
  assert.equal(estado.isLoading.value, false)
  assert.equal(estado.mostrarAgregarCliente.value, false)
  await estado.onDocumentoFocusOut()
  assert.equal(get.mock.callCount(), 2)
})

test('un cliente seleccionado no se consulta ni ofrece volver a guardarlo', async (t) => {
  const get = t.mock.method(axios, 'get', async () => {})
  const estado = receptor(t, { id: 8 })
  assert.equal(await estado.onDocumentoFocusOut(), true)
  assert.equal(get.mock.callCount(), 0)
  assert.equal(estado.form.cliente, 8)
  assert.equal(estado.mostrarAgregarCliente.value, false)
})

test('una condicion de IVA incompatible se informa y no habilita la emision', async (t) => {
  t.mock.method(axios, 'get', async () => ({ data: {
    razonSocial: 'Cliente B', cliente: { id: 7, condicion_iva_receptor_id: 5 },
  } }))
  const fire = t.mock.method(Swal, 'fire', () => {})
  const estado = receptor(t)
  assert.equal(await estado.onDocumentoFocusOut(), false)
  assert.equal(await estado.onDocumentoFocusOut(), false)
  assert.equal(estado.form.condicionIva, 5)
  assert.equal(fire.mock.callCount(), 1)
  assert.equal(estado.mostrarAgregarCliente.value, false)
})
