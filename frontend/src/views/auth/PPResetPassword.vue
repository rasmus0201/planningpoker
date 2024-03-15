<script lang="ts" setup>
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

const onSubmit = async () => {
  state.value = "loading";

  try {
    const response = await api.post("/auth/reset-password", {
      ...form,
      token: router.currentRoute.value.query.token ?? ""
    });

    if (response.status !== 200) {
      throw new Error();
    }

    router.push({ name: "auth.login" });
    state.value = "success";
  } catch (e) {
    state.value = "error";
  }
};
</script>

<template>
  <AuthFormWrapper :disabled="state === 'loading'" @submit="onSubmit()">
    <div class="columns w-100 is-flex is-flex-direction-column box">
      <div v-if="state === 'error'" class="column">
        <p class="has-text-danger">Error...</p>
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
