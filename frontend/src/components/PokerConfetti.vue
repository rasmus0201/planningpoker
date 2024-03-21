<script lang="ts" setup>
import { create as createConfetti } from "canvas-confetti";
import { onMounted, ref } from "vue";

const canvas = ref<HTMLCanvasElement | null>(null);

onMounted(() => {
  if (!canvas.value) {
    return;
  }

  const cannon = createConfetti(canvas.value, {
    resize: true,
    useWorker: true,
    disableForReducedMotion: true
  });
  const duration = 5 * 1000;
  const animationEnd = Date.now() + duration;
  const colors = ["#00a489", "#007dc6", "#e74c3c", "#f99f3c", "#7f8b98", "#8463ff", "#c91611"];
  const defaults = { colors, startVelocity: 25, decay: 0.95, spread: 100, ticks: 40, zIndex: 0 };

  const interval = setInterval(function () {
    const timeLeft = animationEnd - Date.now();

    if (timeLeft <= 0) {
      return clearInterval(interval);
    }

    // If component is mounted/unmounted instantaneously the canvas could be gone!
    if (!cannon || !canvas.value) {
      return clearInterval(interval);
    }

    const particleCount = 50 * (timeLeft / duration);

    cannon({
      ...defaults,
      particleCount,
      angle: 60,
      origin: { x: 0 }
    });
    cannon({
      ...defaults,
      particleCount,
      angle: 120,
      origin: { x: 1 }
    });
  }, 250);
});
</script>

<template>
  <canvas id="confettiCanvas" ref="canvas" />
</template>

<style scoped>
#confettiCanvas {
  pointer-events: none;
  position: fixed;
  width: 100vw;
  height: 100vh;
  top: 0;
  left: 0;
}
</style>
