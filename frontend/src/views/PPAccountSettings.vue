<script lang="ts" setup>
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";

import { API_URL } from "@/config";
import { useUserStore } from "@/pinia/user";

const router = useRouter();
const userStore = useUserStore();

const state = ref<"init" | "loading" | "success" | "error">("init");
const form = reactive({
  email: userStore.user.email,
  username: userStore.user.username,
  password: ""
});

const onAccountUpdate = async () => {
  state.value = "loading";
  try {
    const response = await fetch(`${API_URL}/me`, {
      method: "PATCH",
      headers: new Headers({ "Content-Type": "application/json", Authorization: userStore.authHeader }),
      body: JSON.stringify(form)
    });

    if (!response.ok) {
      throw new Error();
    }

    const json = await response.json();
    userStore.user = json.user;

    form.password = "";

    state.value = "success";
  } catch (error) {
    state.value = "error";
  }
};

const onExport = async () => {
  state.value = "loading";
  try {
    const response = await fetch(`${API_URL}/me/export`, {
      method: "POST",
      headers: new Headers({ Authorization: userStore.authHeader })
    });

    if (!response.ok) {
      throw new Error();
    }

    window.alert("We will send you an email with the exported data once it's ready.");

    state.value = "success";
  } catch (error) {
    state.value = "error";
  }
};

const onErasure = async () => {
  const confirmed = window.confirm("Are you sure? This cannot be undone and all your data will be deleted permanently");
  if (confirmed !== true) {
    return;
  }

  state.value = "loading";
  try {
    const response = await fetch(`${API_URL}/me`, {
      method: "DELETE",
      headers: new Headers({ Authorization: userStore.authHeader })
    });

    if (!response.ok) {
      throw new Error();
    }

    await userStore.logout();
    router.push({ name: "auth.login" });

    state.value = "success";
  } catch (error) {
    state.value = "error";
  }
};
</script>

<template>
  <div class="columns py-4">
    <div class="column is-one-half">
      <div class="box">
        <form @submit.prevent="onAccountUpdate()">
          <fieldset :disabled="state === 'loading'">
            <div v-if="state === 'error'" class="field">
              <p class="has-text-danger">Please try again.</p>
            </div>
            <div class="field">
              <label class="label">Email</label>
              <div class="control">
                <input v-model="form.email" required class="input" type="email" placeholder="e.g. alex@example.com" />
              </div>
            </div>
            <div class="field">
              <label class="label">Username</label>
              <div class="control">
                <input v-model="form.username" required class="input" type="text" placeholder="e.g. alex@example.com" />
              </div>
            </div>
            <div class="field">
              <label class="label">Change password (leave blank for unchanged)</label>
              <div class="control">
                <input v-model="form.password" class="input" type="password" placeholder="********" />
              </div>
            </div>

            <button type="submit" class="button is-primary">Update account</button>
          </fieldset>
        </form>
      </div>
    </div>
    <div class="column is-one-half">
      <div class="box">
        <form>
          <div class="field">
            <label class="label">Export user data</label>
            <div class="control">
              <button class="button is-info" :disabled="state === 'loading'" @click.prevent="onExport()">
                Export account
              </button>
            </div>
          </div>

          <div class="field">
            <label class="label">Request deletion</label>
            <div class="control">
              <button class="button is-danger" :disabled="state === 'loading'" @click.prevent="onErasure()">
                Delete account
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped></style>
