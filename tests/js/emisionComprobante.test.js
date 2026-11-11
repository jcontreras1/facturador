import assert from 'node:assert/strict'
import test from 'node:test'
import Swal from 'sweetalert2'
import {
  emisionEnCurso,
  iniciarEmisionComprobante,
  finalizarEmisionComprobante,
} from '../../resources/js/utils/emisionComprobante.js'

test('bloquea la pantalla con spinner y rechaza una segunda emision', (t) => {
  const fire = t.mock.method(Swal, 'fire', () => {})
  const loading = t.mock.method(Swal, 'showLoading', () => {})
  const close = t.mock.method(Swal, 'close', () => {})
  t.after(() => finalizarEmisionComprobante())

  assert.equal(iniciarEmisionComprobante(), true)
  assert.equal(emisionEnCurso.value, true)
  const options = fire.mock.calls[0].arguments[0]
  assert.equal(options.allowOutsideClick, false)
  assert.equal(options.allowEscapeKey, false)
  assert.equal(options.allowEnterKey, false)
  assert.equal(options.showConfirmButton, false)
  assert.equal(options.showCancelButton, false)
  options.didOpen()
  assert.equal(loading.mock.callCount(), 1)

  assert.equal(iniciarEmisionComprobante(), false)
  assert.equal(fire.mock.callCount(), 1)
  assert.equal(close.mock.callCount(), 0)
});

test('libera el bloqueo tras un error y permite reintentar o emitir una nota de credito', (t) => {
  const fire = t.mock.method(Swal, 'fire', () => {})
  const close = t.mock.method(Swal, 'close', () => {})
  t.after(() => finalizarEmisionComprobante())

  iniciarEmisionComprobante()
  finalizarEmisionComprobante()

  assert.equal(emisionEnCurso.value, false)
  assert.equal(close.mock.callCount(), 1)
  assert.equal(iniciarEmisionComprobante('Emitiendo nota de crédito...'), true)
  assert.equal(fire.mock.calls[1].arguments[0].title, 'Emitiendo nota de crédito...')
  assert.equal(emisionEnCurso.value, true)
});
