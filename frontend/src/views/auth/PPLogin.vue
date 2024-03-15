<script lang="ts" setup>
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

const onSubmit = async () => {
  state.value = "loading";
  try {
    const response = await api.post("/auth/login", form);

    if (response.status !== 200) {
      throw new Error();
    }

    userStore.login(response.data.data);
    router.push({ name: "home" });

    state.value = "success";
  } catch (error) {
    state.value = "error";
  }
};
</script>

<template>
  <AuthFormWrapper :disabled="state === 'loading'" @submit="onSubmit()">
    <div class="columns is-flex is-flex-direction-column box">
      <div v-if="state === 'error'" class="column">
        <p class="has-text-danger">Please try again.</p>
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
