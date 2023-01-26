<script lang="ts" setup>
import { PokerCardsType } from "@/types";

defineProps<{
  active?: string;
  username?: string;
  card: PokerCardsType;
}>();
</script>

<template>
  <button class="poker-card" :class="{ 'poker-card--active': card === active }">
    <div :class="['poker-card__inner', 'poker-card__image', `poker-card__image--${card}`]">
      <div v-if="username" class="poker-card__symbol poker-card__symbol--big">
        <h4 class="poker-card__title">{{ username }}</h4>
      </div>
    </div>
  </button>
</template>

<style lang="scss" scoped>
.poker-card {
  position: relative;
  width: 48%;
  height: 250px;
  font: 16px "Trebuchet MS";
  padding: 0;
  border-radius: 4px;
  background: #fff;
  border: 1px solid #000;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.18);
  transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out,
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
  border-radius: 4px;
}

.poker-card__value {
  margin: 0;
  background: #fff;
  border-radius: 4px;
  margin-top: 0.5rem;
  padding: 8px;
  line-height: 1em;
  word-break: break-all;
  hyphens: auto;
}

.poker-card__input {
  width: 100%;
  border: none;
  border-radius: 0.25rem;
}

.poker-card__input:placeholder-shown {
  opacity: 0.5;
}

.poker-card__title {
  position: absolute;
  display: inline-block;
  top: 10px;
  right: 10px;
  font-size: 1.5rem;
  background: #fff;
  border-radius: 2px;
  padding: 4px;
  margin: 0;
}

.poker-card__symbol {
  text-align: center;
  font-size: 1rem;
}

.poker-card__image {
  background-size: cover;
  background-position: center;
}

.poker-card__image--1 {
  background-image: url(/covers/1.svg);
}

.poker-card__image--2 {
  background-image: url(/covers/2.svg);
}

.poker-card__image--3 {
  background-image: url(/covers/3.svg);
}

.poker-card__image--5 {
  background-image: url(/covers/5.svg);
}

.poker-card__image--8 {
  background-image: url(/covers/8.svg);
}

.poker-card__image--13 {
  background-image: url(/covers/13.svg);
}

.poker-card__image--20 {
  background-image: url(/covers/20.svg);
}

.poker-card__image--40 {
  background-image: url(/covers/40.svg);
}

.poker-card__image--100 {
  background-image: url(/covers/100.svg);
}

.poker-card__image--brownie {
  background-image: url(/covers/brownie.svg);
}

.poker-card__image--coffee {
  background-image: url(/covers/coffee.svg);
}

.poker-card__image--cover {
  background-image: url(/covers/cover.svg);
}

.poker-card__image--infinite {
  background-image: url(/covers/infinite.svg);
}

.poker-card__image--question {
  background-image: url(/covers/question.svg);
}

.poker-card__image--yak-shaving {
  background-image: url(/covers/yak-shaving.svg);
}

@media (min-width: 768px) {
  .poker-card {
    width: 160px;
    height: 244px;
  }

  .poker-card__symbol {
    font-size: 3rem;
  }
}

@media (min-width: 1200px) {
  .poker-card {
    width: 180px;
    height: 274px;
  }
}
</style>
