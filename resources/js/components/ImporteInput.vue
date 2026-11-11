<template>
  <input
    type="text"
    inputmode="decimal"
    :value="texto"
    :readonly="readonly"
    pattern="[0-9.]+(,[0-9]{0,2})?"
    title="Ingrese un importe positivo con hasta dos decimales."
    @input="actualizar"
    @beforeinput="borrarSeparador"
    @paste="pegar"
  >
</template>

<script setup>
import { ref, watch } from 'vue'
import { editarImporte, formatearImporte, prepararImportePegado } from '../utils/importes'

const props = defineProps({
  modelValue: { type: [Number, String], default: null },
  readonly: Boolean,
})
const emit = defineEmits(['update:modelValue'])
const texto = ref('')

watch(() => [props.modelValue, props.readonly], ([valor, readonly]) => {
  const actual = editarImporte(texto.value, texto.value.length)
  if (readonly || actual?.valor !== (valor === '' || valor === null ? null : Number(valor))) {
    texto.value = formatearImporte(valor, readonly)
  }
}, { immediate: true })

function actualizar(event) {
  const input = event.target
  const resultado = editarImporte(input.value, input.selectionStart, event.data)
  if (!resultado) {
    input.value = texto.value
    input.setCustomValidity('Ingrese un importe positivo usando punto o coma para los decimales.')
    return
  }

  input.setCustomValidity('')
  texto.value = resultado.texto
  input.value = resultado.texto
  input.setSelectionRange(resultado.cursor, resultado.cursor)
  emit('update:modelValue', resultado.valor)
}

function borrarSeparador(event) {
  const input = event.target
  const inicio = input.selectionStart
  if (input.selectionEnd !== inicio) {
    return
  }

  if (event.inputType === 'deleteContentBackward' && input.value[inicio - 1] === '.') {
    event.preventDefault()
    input.setRangeText('', inicio - 2, inicio, 'end')
    actualizar({ target: input })
  } else if (event.inputType === 'deleteContentForward' && input.value[inicio] === '.') {
    event.preventDefault()
    input.setRangeText('', inicio, inicio + 2, 'end')
    actualizar({ target: input })
  }
}

function pegar(event) {
  if (props.readonly) {
    return
  }

  event.preventDefault()
  const valor = prepararImportePegado(event.clipboardData.getData('text'))
  if (valor === null) {
    event.target.setCustomValidity('El importe pegado no tiene un formato numerico valido.')
    event.target.reportValidity()
    return
  }

  event.target.setRangeText(valor, event.target.selectionStart, event.target.selectionEnd, 'end')
  actualizar({ target: event.target })
}
</script>
