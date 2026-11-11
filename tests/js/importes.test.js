import assert from 'node:assert/strict'
import test from 'node:test'
import { editarImporte, formatearImporte, prepararImportePegado } from '../../resources/js/utils/importes.js'

function escribir(texto) {
  let estado = { texto: '', cursor: 0, valor: null }
  for (const caracter of texto) {
    const entrada = estado.texto.slice(0, estado.cursor) + caracter + estado.texto.slice(estado.cursor)
    estado = editarImporte(entrada, estado.cursor + 1, caracter)
  }
  return estado
}

test('agrupa miles mientras se escribe y acepta ambos separadores decimales', () => {
  for (const [entrada, esperado, valor] of [
    ['1234', '1.234', 1234],
    ['123.2', '123,2', 123.2],
    ['1234.2', '1.234,2', 1234.2],
    ['1234,2', '1.234,2', 1234.2],
    ['1234567.89', '1.234.567,89', 1234567.89],
    ['.25', '0,25', 0.25],
    ['0,05', '0,05', 0.05],
    ['0001234', '1.234', 1234],
  ]) {
    assert.deepEqual(escribir(entrada), { texto: esperado, cursor: esperado.length, valor })
  }
})

test('conserva la coma y los ceros decimales durante la edicion', () => {
  assert.deepEqual(escribir('1234.'), { texto: '1.234,', cursor: 6, valor: 1234 })
  assert.deepEqual(escribir('1234.20'), { texto: '1.234,20', cursor: 8, valor: 1234.2 })
  assert.deepEqual(editarImporte('', 0), { texto: '', cursor: 0, valor: null })
})

test('mantiene el cursor al insertar y borrar digitos dentro del importe', () => {
  assert.deepEqual(editarImporte('19.234,50', 2, '9'), { texto: '19.234,50', cursor: 2, valor: 19234.5 })
  assert.deepEqual(editarImporte('1.34,50', 2), { texto: '134,50', cursor: 1, valor: 134.5 })
  assert.deepEqual(editarImporte('1.234,5', 7), { texto: '1.234,5', cursor: 7, valor: 1234.5 })
})

test('interpreta importes pegados en formato argentino o con punto decimal', () => {
  for (const [entrada, esperado] of [
    ['1.234,20', '1234,20'],
    ['1.234.567,89', '1234567,89'],
    ['1234.20', '1234,20'],
    [' 1234,20 ', '1234,20'],
    ['1.234', '1234'],
  ]) {
    assert.equal(prepararImportePegado(entrada), esperado)
    assert.equal(editarImporte(esperado, esperado.length).valor, Number(esperado.replace(',', '.')))
  }
})

test('interpreta inserciones completas sin confundir el punto decimal con los miles', () => {
  assert.deepEqual(editarImporte('1234.2', 6, '1234.2'), { texto: '1.234,2', cursor: 7, valor: 1234.2 })
  assert.deepEqual(editarImporte('1.234,20', 8, '1.234,20'), { texto: '1.234,20', cursor: 8, valor: 1234.2 })
})

test('rechaza importes negativos, texto y separadores decimales repetidos', () => {
  for (const entrada of ['-123', 'abc', '1,2,3']) {
    assert.equal(editarImporte(entrada, entrada.length), null)
    assert.equal(prepararImportePegado(entrada), null)
  }
})

test('formatea subtotales numericos sin cambiar los valores de calculo', () => {
  assert.equal(formatearImporte(1234.2), '1.234,2')
  assert.equal(formatearImporte(1234.2, true), '1.234,20')
  assert.equal(formatearImporte(0, true), '0,00')
  assert.equal(formatearImporte(null), '')
})
