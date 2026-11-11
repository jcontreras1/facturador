<template>
  <label class="agregar-cliente d-flex align-items-center gap-3 rounded-3 border p-3 mt-3"
    :class="{ 'agregar-cliente-activo': modelValue }">
    <input type="checkbox" class="form-check-input flex-shrink-0 m-0"
      :checked="modelValue" @change="$emit('update:modelValue', $event.target.checked)">
    <span class="agregar-cliente-icono rounded-circle d-flex align-items-center justify-content-center">
      <i class="fas fa-user-plus" aria-hidden="true"></i>
    </span>
    <span>
      <span class="d-block fw-semibold">Agregar como cliente</span>
      <small class="text-body-secondary">Se guardará al emitir la factura, sin duplicar clientes existentes.</small>
    </span>
  </label>
</template>

<script setup>
defineProps({ modelValue: Boolean })
defineEmits(['update:modelValue'])
</script>

<style scoped>
.agregar-cliente {
  cursor: pointer;
  background: var(--bs-tertiary-bg);
  transition: background-color .15s, border-color .15s;
}
.agregar-cliente:hover,
.agregar-cliente:focus-within,
.agregar-cliente-activo {
  border-color: var(--bs-primary) !important;
  background: var(--bs-primary-bg-subtle);
}
.agregar-cliente .form-check-input {
  width: 1.4rem;
  height: 1.4rem;
  cursor: pointer;
}
.agregar-cliente-icono {
  width: 2.5rem;
  height: 2.5rem;
  flex-shrink: 0;
  color: var(--bs-primary);
  background: var(--bs-body-bg);
}
</style>
