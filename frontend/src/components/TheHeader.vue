<script lang="ts" setup>
import { UsePreferredDark } from "@vueuse/components";
import { ref } from "vue";
import { RouterLink } from "vue-router";

const props = withDefaults(
  defineProps<{
    hideByDefault: boolean;
  }>(),
  { hideByDefault: false }
);

const isHeaderHidden = ref(props.hideByDefault);
const isMenuOpen = ref(false);
</script>

<template>
  <UsePreferredDark v-slot="{ prefersDark }">
    <nav
      class="navbar has-shadow px-2"
      :class="{ 'is-dark': prefersDark, 'is-hidden': isHeaderHidden }"
      role="navigation"
      aria-label="main navigation"
    >
      <div class="navbar-brand">
        <RouterLink :to="{ name: 'home' }" class="is-flex is-align-items-center">
          <span>PlanningPoker</span>
        </RouterLink>

        <a
          role="button"
          class="navbar-burger"
          :class="{ 'is-active': isMenuOpen }"
          aria-label="menu"
          :aria-expanded="isMenuOpen"
          @click.prevent="isMenuOpen = !isMenuOpen"
        >
          <span aria-hidden="true"></span>
          <span aria-hidden="true"></span>
          <span aria-hidden="true"></span>
        </a>
      </div>

      <div class="navbar-menu" :class="{ 'is-active': isMenuOpen }">
        <div class="navbar-start">
          <div class="navbar-item is-hoverable">
            <RouterLink :to="{ name: 'game.create' }" class="navbar-link is-arrowless">Host game</RouterLink>
          </div>
          <div class="navbar-item is-hoverable">
            <RouterLink :to="{ name: 'game.join' }" class="navbar-link is-arrowless">Join</RouterLink>
          </div>
        </div>
        <div class="navbar-end">
          <div class="navbar-item has-dropdown is-hoverable">
            <RouterLink to="#" class="navbar-link">Account</RouterLink>
            <div class="navbar-dropdown">
              <RouterLink :to="{ name: 'account.settings' }" class="navbar-item">Settings</RouterLink>
              <hr class="navbar-divider" />
              <RouterLink :to="{ name: 'account.logout' }" class="navbar-item">Logout</RouterLink>
            </div>
          </div>
        </div>
      </div>
    </nav>
    <button v-if="hideByDefault" class="header-toggle-btn" @click="isHeaderHidden = !isHeaderHidden"></button>
  </UsePreferredDark>
</template>

<style lang="scss" scoped>
.navbar.is-dark {
  background-color: hsl(0deg, 0%, 20%);

  &.has-shadow {
    box-shadow: 0 2px 0 0 hsl(0deg 0% 30%);
  }
}

.header-toggle-btn {
  position: absolute;
  top: 0;
  right: 0;
  height: 32px;
  width: 32px;
  background: none;
  border-radius: 0;
  border: none;
  overflow: hidden;
  padding: 0;
  cursor: pointer;
  z-index: 999;

  &::before,
  &::after {
    content: "";
    position: absolute;
    top: -23px;
    right: -24px;
    height: 45.25px;
    width: 45.25px;
    background: hsl(229deg, 53%, 53%);
    transform: rotate(45deg);
  }
}
</style>
