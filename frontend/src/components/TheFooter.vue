<script lang="ts" setup>
import { RouterLink } from "vue-router";
</script>

<template>
  <footer class="footer px-1 py-2">
    <div class="content has-text-centered">
      <p>
        <strong>PlanningPoker</strong> by <a href="https://rasmusbundsgaard.dk">Rasmus Bundsgaard</a> &copy;
        {{ new Date().getFullYear() }}. <RouterLink :to="{ name: 'legal' }">Privacy & Cookies</RouterLink>
      </p>
    </div>
  </footer>
</template>

<style lang="scss" scoped>
.footer {
  margin-top: auto;
}

.dark .footer {
  background-color: hsl(0deg, 0%, 20%);
}
</style>
