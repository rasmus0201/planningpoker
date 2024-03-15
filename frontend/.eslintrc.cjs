/* eslint-env node */
require("@rushstack/eslint-patch/modern-module-resolution");

module.exports = {
  root: true,
  settings: {
    "import/resolver": {
      alias: {
        map: [["@", "./src"]]
      }
    }
  },
  plugins: ["simple-import-sort"],
  extends: [
    "plugin:promise/recommended",
    "plugin:vue/vue3-recommended",
    "eslint:recommended",
    "@vue/eslint-config-typescript/recommended",
    "@vue/eslint-config-prettier"
  ],
  rules: {
    "@typescript-eslint/array-type": ["warn", { default: "array" }],
    "@typescript-eslint/no-shadow": "warn",
    "arrow-body-style": ["warn", "as-needed"],
    "no-restricted-imports": [
      "warn",
      {
        patterns: [
          {
            group: ["../*"],
            message: "You should avoid imports using relative paths. Use @/* instead."
          },
          {
            group: ["@/api/*", "@/composables/*", "@/services/*", "@/types/*", "@/stores/*", "@/helpers/*"],
            message: "You should avoid using deep imports."
          }
        ]
      }
    ],
    "no-return-await": "warn",
    "no-shadow": "off",
    "require-await": "warn",
    "simple-import-sort/exports": "warn",
    "simple-import-sort/imports": "warn",
    "vue/attribute-hyphenation": ["warn", "always"],
    "vue/no-boolean-default": ["warn", "default-false"],
    "vue/component-name-in-template-casing": [
      "warn",
      "PascalCase",
      {
        registeredComponentsOnly: false,
        ignores: ["i18n-t"]
      }
    ],
    "vue/component-tags-order": [
      "warn",
      { order: ["script:not([setup])", "script[setup]", "template", "style", "i18n"] }
    ],
    "vue/define-macros-order": [
      "warn",
      {
        order: ["defineProps", "defineEmits"]
      }
    ],
    "vue/custom-event-name-casing": ["warn", "camelCase"],
    "vue/no-unused-components": "warn",
    "vue/no-undef-components": "warn",
    "vue/v-bind-style": ["warn", "shorthand"],
    "vue/v-on-function-call": ["warn", "always"]
  }
};
