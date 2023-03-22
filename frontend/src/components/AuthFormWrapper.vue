<script lang="ts" setup>
defineProps<{
  disabled?: boolean;
}>();

const emit = defineEmits(["submit"]);
</script>

<template>
  <div class="hero is-fullheight">
    <form
      class="form hero-body is-justify-content-center is-align-items-center"
      @submit.prevent="emit('submit', $event)"
    >
      <fieldset class="w-100" :disabled="disabled">
        <slot></slot>
      </fieldset>
    </form>
  </div>
</template>

<style lang="scss" scoped>
.form {
  max-width: 400px;
  width: 100%;
  align-self: center;
}
</style>
