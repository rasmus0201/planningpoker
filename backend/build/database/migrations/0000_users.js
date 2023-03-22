"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const Schema_1 = __importDefault(global[Symbol.for('ioc.use')]("Adonis/Lucid/Schema"));
class default_1 extends Schema_1.default {
    constructor() {
        super(...arguments);
        this.tableName = 'users';
    }
    async up() {
        this.schema.createTable(this.tableName, (table) => {
            table.increments('id').primary();
            table.string('email', 255).notNullable().unique();
            table.string('username', 255).notNullable().unique();
            table.string('password', 180).notNullable();
            table.string('remember_me_token').nullable();
            table.timestamp('last_active_at').nullable();
            table.timestamp('created_at').notNullable().defaultTo(this.now());
            table.timestamp('updated_at').notNullable().defaultTo(this.now());
            table.timestamp('deleted_at').nullable();
        });
    }
    async down() {
        this.schema.dropTable(this.tableName);
    }
}
exports.default = default_1;
//# sourceMappingURL=0000_users.js.map