import BaseSchema from '@ioc:Adonis/Lucid/Schema'

export default class extends BaseSchema {
  protected tableName = 'games'

  public async up() {
    this.schema.createTable(this.tableName, (table) => {
      table.increments('id')
      table.integer('user_id').unsigned().references('id').inTable('users').onDelete('CASCADE')
      table.string('title')
      table.string('pin').unique()
      table.string('state')
      table.timestamp('created_at', { useTz: false })
      table.timestamp('updated_at', { useTz: false })
    })
  }

  public async down() {
    this.schema.dropTable(this.tableName)
  }
}
