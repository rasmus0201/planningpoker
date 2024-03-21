<script lang="ts" setup>
import { isAxiosError } from "axios";
import { reactive, ref } from "vue";
import { RouterLink, useRouter } from "vue-router";

import AuthFormWrapper from "@/components/AuthFormWrapper.vue";
import { useUnauthenticatedApi } from "@/composables";

const router = useRouter();

const api = useUnauthenticatedApi();

const state = ref<"init" | "loading" | "success" | "error">("init");
const form = reactive({
  email: "",
  password: ""
});

const defaultErrors = {
  email: [],
  password: [],
  token: []
};
const errors = reactive<Record<string, string[]>>({ ...defaultErrors });

const onSubmit = async () => {
  state.value = "loading";
  Object.assign(errors, { ...defaultErrors });

  try {
    await api.post("/auth/reset-password", {
      ...form,
      token: router.currentRoute.value.query.token ?? ""
    });

    router.push({ name: "auth.login" });
    state.value = "success";
  } catch (e) {
    if (isAxiosError(e) && e.response?.status === 400) {
      if (e.response.data.data?.errors) {
        errors.email = e.response.data.data.errors.email || [];
        errors.password = e.response.data.data.errors.password || [];
        errors.token = e.response.data.data.errors.token || [];
      } else {
        errors.token = [e.response.data?.message ?? "Please try again"];
      }
    }

    state.value = "error";
  }
};
</script>

<template>
  <AuthFormWrapper :disabled="state === 'loading'" @submit="onSubmit()">
    <div class="columns w-100 is-flex is-flex-direction-column box">
      <div v-if="state === 'error'" class="column">
        <ul>
          <li v-for="(error, index) in Object.values(errors)" :key="index" class="has-text-danger">{{ error[0] }}</li>
        </ul>
      </div>
      <div class="column">
        <label for="email">Confirm Email</label>
        <input v-model="form.email" required class="input is-primary" type="email" placeholder="Email address" />
      </div>
      <div class="column">
        <label for="Name">New Password</label>
        <input v-model="form.password" required class="input is-primary" type="password" placeholder="Password" />
      </div>
      <div class="column">
        <button class="button is-primary is-fullwidth" type="submit">Reset Password</button>
      </div>
      <div class="has-text-centered">
        <p class="is-size-7">
          Don't want to reset?
          <RouterLink :to="{ name: 'auth.login' }" class="has-text-primary">Sign in</RouterLink>
        </p>
      </div>
    </div>
  </AuthFormWrapper>
</template>
