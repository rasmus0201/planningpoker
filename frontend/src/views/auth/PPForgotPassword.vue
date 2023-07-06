<script lang="ts" setup>
import { ref } from "vue";
import { RouterLink, useRouter } from "vue-router";

import AuthFormWrapper from "@/components/AuthFormWrapper.vue";
import { API_URL } from "@/config";

const router = useRouter();

const state = ref<"init" | "loading" | "success" | "error">("init");
const error = ref("");
const email = ref("");

const onSubmit = async () => {
  error.value = "";
  state.value = "loading";

  try {
    const response = await fetch(`${API_URL}/auth/forgot-password`, {
      method: "POST",
      headers: new Headers({ "Content-Type": "application/json" }),
      body: JSON.stringify({ email: email.value, returnPath: router.resolve({ name: "auth.resetPassword" }).path })
    });

    const body = await response.json();

    if (!response.ok) {
      error.value = body.message ?? "Please try again";
      throw new Error();
    }

    state.value = "success";
  } catch (e) {
    state.value = "error";
  }
};
</script>

<template>
  <AuthFormWrapper @submit="onSubmit()">
    <div v-if="state === 'success'" class="columns w-100 is-flex is-flex-direction-column box">
      <div class="has-text-centered">We have send you an email with a confirmation link.</div>
    </div>
    <div v-else class="columns w-100 is-flex is-flex-direction-column box">
      <div v-if="state === 'error'" class="column">
        <p class="has-text-danger">{{ error }}</p>
      </div>
      <div class="column">
        <label for="email">Email</label>
        <input v-model="email" required class="input is-primary" type="email" placeholder="Email address" />
      </div>
      <div class="column">
        <button class="button is-primary is-fullwidth" type="submit">Submit</button>
      </div>
      <div class="has-text-centered">
        <p class="is-size-7">
          Remember your password?
          <RouterLink :to="{ name: 'auth.login' }" class="has-text-primary">Sign in</RouterLink>
        </p>
      </div>
    </div>
  </AuthFormWrapper>
</template>
