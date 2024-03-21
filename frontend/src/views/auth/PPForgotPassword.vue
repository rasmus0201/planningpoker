<script lang="ts" setup>
import { isAxiosError } from "axios";
import { ref } from "vue";
import { RouterLink, useRouter } from "vue-router";

import AuthFormWrapper from "@/components/AuthFormWrapper.vue";
import { useUnauthenticatedApi } from "@/composables";

const router = useRouter();
const api = useUnauthenticatedApi();

const state = ref<"init" | "loading" | "success" | "error">("init");
const error = ref("");
const email = ref("");

const onSubmit = async () => {
  error.value = "";
  state.value = "loading";

  try {
    await api.post("/auth/forgot-password", {
      email: email.value,
      returnPath: router.resolve({ name: "auth.resetPassword" }).path
    });

    state.value = "success";
  } catch (e) {
    if (isAxiosError(e) && e.response?.status === 400) {
      error.value = e.response.data.data?.errors?.email[0] ?? e.response.data.message ?? "Please try again";
    }

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
