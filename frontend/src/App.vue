<script setup lang="ts">
import { io } from "socket.io-client";

const socket = io('http://127.0.0.1:3333')
socket.on('news', (data) => {
  console.log(data)
  socket.emit('my other event', { my: 'data' })
})
</script>

<template>

  aaaaaaahhhhh
</template>

<style scoped>
</style>
