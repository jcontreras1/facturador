const formatoEditable = new Intl.NumberFormat('es-AR', { maximumFractionDigits: 20 })
const formatoCalculado = new Intl.NumberFormat('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })

export function formatearImporte(valor, calculado = false) {
  if (valor === null || valor === undefined || valor === '') {
    return ''
  }

  return (calculado ? formatoCalculado : formatoEditable).format(Number(valor))
}

export function editarImporte(texto, cursor, dato = null) {
  if (typeof dato === 'string' && dato.length > 1) {
    const insertado = prepararImportePegado(dato)
    if (insertado === null) {
      return null
    }
    texto = texto.slice(0, cursor - dato.length) + insertado + texto.slice(cursor)
    cursor += insertado.length - dato.length
  }

  // Solo el punto recien escrito es decimal; los puntos existentes separan miles.
  if (dato === '.' && texto[cursor - 1] === '.') {
    texto = texto.slice(0, cursor - 1) + ',' + texto.slice(cursor)
  }

  const sinMiles = texto.replace(/\./g, '')
  if (!/^\d*(,\d*)?$/.test(sinMiles)) {
    return null
  }

  let posicion = texto.slice(0, cursor).replace(/\./g, '').length
  const [entero, decimales] = sinMiles.split(',')
  let normalizado = entero.replace(/^0+(?=\d)/, '')
  posicion = Math.max(0, posicion - (entero.length - normalizado.length))
  if (normalizado === '' && decimales !== undefined) {
    normalizado = '0'
    posicion++
  }

  const agrupado = normalizado.replace(/\B(?=(\d{3})+(?!\d))/g, '.')
  const resultado = agrupado + (decimales === undefined ? '' : ',' + decimales)
  let nuevoCursor = 0
  let caracteres = 0
  while (nuevoCursor < resultado.length && caracteres < posicion) {
    if (resultado[nuevoCursor] !== '.') {
      caracteres++
    }
    nuevoCursor++
  }

  const valor = resultado === '' ? null : Number((normalizado || '0') + (decimales === undefined ? '' : '.' + decimales))
  if (valor !== null && !Number.isFinite(valor)) {
    return null
  }

  return {
    texto: resultado,
    cursor: nuevoCursor,
    valor,
  }
}

export function prepararImportePegado(texto) {
  const valor = texto.trim()
  if (/^\d{1,3}(\.\d{3})+(,\d*)?$/.test(valor)) {
    return valor.replace(/\./g, '')
  }
  if (/^\d*([.,]\d*)?$/.test(valor)) {
    return valor.replace('.', ',')
  }

  return null
}
