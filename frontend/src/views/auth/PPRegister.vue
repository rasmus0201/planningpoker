<script lang="ts" setup>
import { reactive, ref } from "vue";
import { RouterLink, useRouter } from "vue-router";

import AuthFormWrapper from "@/components/AuthFormWrapper.vue";
import { API_URL } from "@/config";
import { useUserStore } from "@/pinia/user";

const router = useRouter();
const userStore = useUserStore();

const state = ref<"init" | "loading" | "success" | "error">("init");
const form = reactive({
  email: "",
  username: "",
  password: ""
});

const onSubmit = async () => {
  state.value = "loading";

  try {
    const response = await fetch(`${API_URL}/auth/register`, {
      method: "POST",
      headers: new Headers({ "Content-Type": "application/json" }),
      body: JSON.stringify(form)
    });

    if (!response.ok) {
      throw new Error();
    }

    const json = await response.json();

    userStore.login({ user: json.user, token: json.token.token });

    router.push({ name: "home" });

    state.value = "success";
  } catch (error) {
    state.value = "error";
  }
};
</script>

<template>
  <AuthFormWrapper :disabled="state === 'loading'" @submit="onSubmit()">
    <div class="columns w-100 is-flex is-flex-direction-column box">
      <div v-if="state === 'error'" class="column">
        <p class="has-text-danger">Please try again.</p>
      </div>
      <div class="column">
        <label for="email">Email</label>
        <input v-model="form.email" required class="input is-primary" type="email" placeholder="Email address" />
      </div>
      <div class="column">
        <label for="Name">Username</label>
        <input v-model="form.username" required class="input is-primary" type="text" placeholder="Username" />
      </div>
      <div class="column">
        <label for="Name">Password</label>
        <input v-model="form.password" required class="input is-primary" type="password" placeholder="Password" />
      </div>
      <div class="column">
        <button class="button is-primary is-fullwidth" type="submit">Register account</button>
      </div>
      <div class="has-text-centered">
        <p class="is-size-7">
          Already have an account?
          <RouterLink :to="{ name: 'auth.login' }" class="has-text-primary">Sign in</RouterLink>
        </p>
      </div>
    </div>
  </AuthFormWrapper>
</template>
