<script lang="ts" setup>
import { computed, ref, watch } from "vue";

import { PokerCards } from "@/types";

const props = defineProps<{
  active?: string;
}>();

const emit = defineEmits<{
  (e: "select", value: string): void;
}>();

const value = ref("");

const isCardActive = computed(() => props.active && !(PokerCards as readonly string[]).includes(props.active));
watch(
  () => props.active,
  (active) => {
    if (!isCardActive.value) {
      return;
    }

    value.value = active ?? "";
  }
);
</script>

<template>
  <button class="poker-card" :class="{ 'poker-card--active': isCardActive }" @click="emit('select', value)">
    <div class="poker-card__inner">
      <input
        v-model="value"
        placeholder="✏️"
        class="poker-card__input"
        autocomplete="off"
        type="text"
        maxlength="12"
        @keyup="emit('select', value)"
        @blur="emit('select', value)"
      />
    </div>
  </button>
</template>

<style lang="scss" scoped>
.poker-card {
  position: relative;
  width: 48%;
  aspect-ratio: 0.685;
  font: 16px "Trebuchet MS";
  padding: 0;
  border-radius: 4px;
  background: #fff;
  border: 1px solid #000;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.18);
  transition:
    color 0.15s ease-in-out,
    background-color 0.15s ease-in-out,
    border-color 0.15s ease-in-out,
    box-shadow 0.15s ease-in-out;
}

.poker-card:focus,
.poker-card:active {
  outline: none;
}

.poker-card--active {
  color: #fff;
  background-color: #007bff;
  border: 5px solid #007bff;
}

.poker-card:disabled {
  opacity: 0.6;
  pointer-events: none;
}

.poker-card__inner {
  box-sizing: border-box;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
  padding: 10px;
  border-radius: 3px;
  background-color: #fff;
}

.poker-card__input {
  width: 100%;
  border: none;
  border-radius: 4px;
  font-size: 1.5rem;
  outline: none;
  padding: 2px;
  box-shadow: 0 0 1px 1px rgba(0, 0, 0, 0.1);
}

.poker-card__input:focus {
  box-shadow: 0 0 1px 3px rgba(0, 0, 0, 0.3);
}

.poker-card__input:placeholder-shown {
  opacity: 0.8;
}

@media (min-width: 768px) {
  .poker-card {
    width: 160px;
  }

  .poker-card__symbol {
    font-size: 3rem;
  }
}

@media (min-width: 1200px) {
  .poker-card {
    width: 180px;
  }
}
</style>
