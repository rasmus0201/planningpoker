<script lang="ts" setup>
import { useThrottleFn } from "@vueuse/shared";
import { onBeforeUnmount, onMounted, ref } from "vue";

import { darkenColor, dynamicForeground } from "@/helpers";
import { useUserStore } from "@/pinia/user";
import { TokenMovedEvent } from "@/types";

defineProps<{
  color: string;
}>();

const emit = defineEmits<{
  (e: "move", value: TokenMovedEvent): void;
}>();

const userStore = useUserStore();

const container = ref<HTMLElement>();
const token = ref<HTMLElement>();

const active = ref(false);
const currentX = ref(0);
const currentY = ref(0);
const initialX = ref(0);
const initialY = ref(0);
const xOffset = ref(0);
const yOffset = ref(0);

onMounted(() => {
  if (!container.value) {
    return;
  }

  container.value.addEventListener("touchstart", dragStart);
  container.value.addEventListener("touchend", dragEnd);
  container.value.addEventListener("touchmove", drag);

  container.value.addEventListener("mousedown", dragStart);
  container.value.addEventListener("mouseup", dragEnd);
  container.value.addEventListener("mousemove", drag);
});

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

  emit("move", {
    x: Number(currentX.value.toFixed(0)),
    y: Number(currentY.value.toFixed(0)),
    containerWidth: container.value.clientWidth,
    containerHeight: container.value.clientHeight
  });
}, 50);

const resetTokenPosition = () => {
  if (!token.value) {
    return;
  }

  currentX.value = window.innerWidth / 2 - token.value.offsetWidth / 2;
  currentY.value = window.innerHeight / 2 - token.value.offsetHeight / 2;

  xOffset.value = currentX.value;
  yOffset.value = currentY.value;

  setTranslate(currentX.value, currentY.value, token.value);
  dragEnd();

  emitMove();
};

const dragStart = (e: MouseEvent | TouchEvent) => {
  if (e.type === "touchstart" && "touches" in e) {
    initialX.value = e.touches[0].clientX - xOffset.value;
    initialY.value = e.touches[0].clientY - yOffset.value;
  } else if ("clientX" in e) {
    initialX.value = e.clientX - xOffset.value;
    initialY.value = e.clientY - yOffset.value;
  }

  if (e.target === token.value) {
    active.value = true;
  }
};

const dragEnd = () => {
  initialX.value = currentX.value;
  initialY.value = currentY.value;

  active.value = false;
};

const drag = (e: MouseEvent | TouchEvent) => {
  if (!active.value || !token.value) {
    return;
  }

  e.preventDefault();

  if (e.type === "touchmove" && "touches" in e) {
    currentX.value = e.touches[0].clientX - initialX.value;
    currentY.value = e.touches[0].clientY - initialY.value;
  } else if ("clientX" in e) {
    currentX.value = e.clientX - initialX.value;
    currentY.value = e.clientY - initialY.value;
  }

  xOffset.value = currentX.value;
  yOffset.value = currentY.value;

  setTranslate(currentX.value, currentY.value, token.value);

  emitMove();
};

const setTranslate = (xPos: number, yPos: number, el: HTMLElement) => {
  el.style.transform = "translate3d(" + xPos + "px, " + yPos + "px, 0)";
};
</script>

<template>
  <div ref="container" class="token-container">
    <div
      ref="token"
      class="token"
      :style="{ backgroundColor: color, borderColor: darkenColor(color), color: dynamicForeground(color) }"
    >
      <span class="token__inner">{{ userStore.user.username.substring(0, 3) }}</span>
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
  touch-action: none;
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
  touch-action: none;
  user-select: none;
  background-color: #000;
  border: 6px solid #fff;

  &__inner {
    pointer-events: none;
  }
}
</style>
