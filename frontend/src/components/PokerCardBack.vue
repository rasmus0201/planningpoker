<script lang="ts" setup>
defineProps<{
  username: string;
}>();
</script>

<template>
  <div class="poker-card">
    <div class="poker-card__inner poker-card__image">
      <div class="poker-card__symbol poker-card__symbol--big">
        <h4 class="poker-card__title">{{ username }}</h4>
      </div>
    </div>
  </div>
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

.poker-card__inner {
  box-sizing: border-box;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
  padding: 10px;
  border-radius: 3px;
}

.poker-card__title {
  position: absolute;
  display: inline-block;
  top: 2px;
  right: 2px;
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
  background-image: url(/covers/cover.svg);
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
