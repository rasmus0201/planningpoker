<script lang="ts" setup>
import { useThrottleFn } from "@vueuse/shared";
import { onBeforeUnmount, onMounted, ref, watch } from "vue";

import { useUserStore } from "@/pinia/user";

const emit = defineEmits<{
  (e: "move", value: { x: number; y: number; containerWidth: number; containerHeight: number }): void;
}>();

const userStore = useUserStore();

const container = ref<HTMLElement>();
const token = ref<HTMLElement>();

const mouseX = ref(0);
const mouseY = ref(0);

onMounted(() => {
  resetTokenPosition();

  window.addEventListener("resize", resetTokenPosition, { passive: true });
});

onBeforeUnmount(() => {
  window.removeEventListener("resize", resetTokenPosition);
});

const emitMove = useThrottleFn(() => {
  if (!token.value || !container.value) {
    return;
  }

  const tokenX = parseInt(token.value.style.left);
  const tokenY = parseInt(token.value.style.top);

  emit("move", {
    x: tokenX ?? 0,
    y: tokenY ?? 0,
    containerWidth: container.value.clientWidth,
    containerHeight: container.value.clientHeight
  });
}, 100);

const resetTokenPosition = () => {
  if (!token.value) {
    return;
  }

  token.value.style.left = `${window.innerWidth / 2 - token.value.offsetWidth / 2}px`;
  token.value.style.top = `${window.innerHeight / 2 - token.value.offsetHeight / 2}px`;

  emitMove();
};

watch(
  () => token.value,
  (el: HTMLElement | undefined, oldEl: HTMLElement | undefined) => {
    if (!el) {
      oldEl?.removeEventListener("mousedown", dragMouseDown);
      oldEl?.removeEventListener("touchmove", onTouchMove);

      return;
    }

    el.addEventListener("mousedown", dragMouseDown);
    el.addEventListener("touchmove", onTouchMove);
  },
  { immediate: true }
);

const dragMouseDown = (e: MouseEvent) => {
  e.preventDefault();

  // Get the mouse cursor position at startup:
  mouseX.value = e.clientX;
  mouseY.value = e.clientY;
  document.onmouseup = closeDragElement;

  // Call a function whenever the cursor moves:
  document.onmousemove = elementDrag;
};

const elementDrag = (e: MouseEvent) => {
  if (!token.value) {
    return;
  }

  e.preventDefault();

  // Calculate the new cursor position:
  const tokenX = mouseX.value - e.clientX;
  const tokenY = mouseY.value - e.clientY;
  mouseX.value = e.clientX;
  mouseY.value = e.clientY;

  // Set the element's new position:
  token.value.style.top = `${token.value.offsetTop - tokenY}px`;
  token.value.style.left = `${token.value.offsetLeft - tokenX}px`;

  emitMove();
};

const closeDragElement = () => {
  // Stop moving when mouse button is released
  document.onmouseup = null;
  document.onmousemove = null;
};

const onTouchMove = (e: TouchEvent) => {
  if (!token.value) {
    return;
  }

  e.preventDefault();

  // Grab the location of touch
  const touchLocation = e.targetTouches[0];

  const x = touchLocation.pageX;
  const y = touchLocation.pageY;

  // Assign new coordinates based on the touch.
  token.value.style.top = `${y - token.value.offsetHeight / 2}px`;
  token.value.style.left = `${x - token.value.offsetWidth / 2}px`;

  emitMove();
};
</script>

<template>
  <div ref="container" class="token-container">
    <div ref="token" class="token">
      <span>{{ userStore.user.username.substring(0, 3) }}</span>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.token-container {
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  overflow: hidden;
}

.token {
  position: absolute;
  top: 0;
  left: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 1rem;
  line-height: 1em;
  width: 64px;
  height: 64px;
  border-radius: 50%;
  user-select: none;
  z-index: 1;
  cursor: move;
  background-color: aquamarine;
}
</style>
