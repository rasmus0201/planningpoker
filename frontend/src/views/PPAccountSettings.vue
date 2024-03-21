<script lang="ts" setup>
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";

import { useApi } from "@/composables";
import { useUserStore } from "@/pinia/user";

const router = useRouter();
const userStore = useUserStore();
const api = useApi();

const state = ref<"init" | "loading" | "success" | "error">("init");
const form = reactive({
  email: userStore.user.email,
  username: userStore.user.username,
  password: ""
});

const onAccountUpdate = async () => {
  state.value = "loading";
  try {
    const response = await api.patch("/me", form);

    const json = response.data;
    userStore.user = json.data;

    form.username = userStore.user.username;
    form.email = userStore.user.email;
    form.password = "";

    state.value = "success";
  } catch (error) {
    state.value = "error";
  }
};

const exportedData = ref<string>("");
const onExport = async () => {
  state.value = "loading";
  try {
    const response = await api.post("/me/export");

    exportedData.value =
      "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(response.data.data, null, 2));

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
    await api.delete("/me");

    // await userStore.logout();
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
            <div v-if="state === 'success'" class="field">
              <p class="has-text-success">Changed made successfully.</p>
            </div>
            <div class="field">
              <label class="label">Display name (readonly)</label>
              <div class="control">
                <input v-model="form.username" readonly disabled class="input" type="text" />
              </div>
            </div>
            <div class="field">
              <label class="label">Email</label>
              <div class="control">
                <input v-model="form.email" required class="input" type="email" placeholder="e.g. alex@example.com" />
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
              <a v-if="exportedData" :href="exportedData" download="user.json">Download export</a>
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
