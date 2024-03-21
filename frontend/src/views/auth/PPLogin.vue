<script lang="ts" setup>
import { isAxiosError } from "axios";
import { reactive, ref } from "vue";
import { RouterLink, useRouter } from "vue-router";

import AuthFormWrapper from "@/components/AuthFormWrapper.vue";
import { useUnauthenticatedApi } from "@/composables";
import { useUserStore } from "@/pinia/user";

const router = useRouter();
const userStore = useUserStore();

const api = useUnauthenticatedApi();

const state = ref<"init" | "loading" | "success" | "error">("init");
const form = reactive({
  email: "",
  password: ""
});

const defaultErrors = {
  email: [],
  password: []
};
const errors = reactive<Record<string, string[]>>({ ...defaultErrors });

const onSubmit = async () => {
  state.value = "loading";
  Object.assign(errors, { ...defaultErrors });

  try {
    const response = await api.post("/auth/login", form);

    userStore.login(response.data.data);
    router.push({ name: "home" });

    state.value = "success";
  } catch (e) {
    if (isAxiosError(e) && e.response?.status === 400) {
      errors.email = e.response.data.data.errors.email || [];
      errors.password = e.response.data.data.errors.password || [];
    }

    if (isAxiosError(e) && e.response?.status === 401) {
      errors.email = [e.response.data.message];
    }

    state.value = "error";
  }
};
</script>

<template>
  <AuthFormWrapper :disabled="state === 'loading'" @submit="onSubmit()">
    <div class="columns is-flex is-flex-direction-column box">
      <div v-if="state === 'error'" class="column">
        <ul>
          <li v-for="(error, index) in Object.values(errors)" :key="index" class="has-text-danger">{{ error[0] }}</li>
        </ul>
      </div>
      <div class="column">
        <label for="email">Email</label>
        <input v-model="form.email" required class="input is-primary" type="email" placeholder="Email address" />
      </div>
      <div class="column">
        <label for="Name">Password</label>
        <input v-model="form.password" required class="input is-primary" type="password" placeholder="Password" />
        <RouterLink :to="{ name: 'auth.forgotPassword' }" class="is-size-7 has-text-primary">
          forgot password?
        </RouterLink>
      </div>
      <div class="column">
        <button class="button is-primary is-fullwidth" type="submit">Login</button>
      </div>
      <div class="has-text-centered">
        <p class="is-size-7">
          Don't have an account?
          <RouterLink :to="{ name: 'auth.register' }" class="has-text-primary">Sign up</RouterLink>
        </p>
      </div>
    </div>
  </AuthFormWrapper>
</template>
