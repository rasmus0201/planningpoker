import BaseSchema from '@ioc:Adonis/Lucid/Schema'

export default class extends BaseSchema {
  protected tableName = 'game_votes'

  public async up() {
    this.schema.createTable(this.tableName, (table) => {
      table.increments('id')
      table.string('round_id')
      table.integer('user_id')
      table.string('vote')
      table.timestamp('created_at', { useTz: false })
      table.timestamp('updated_at', { useTz: false })

      table.unique(['round_id', 'user_id'])
    })
  }

  public async down() {
    this.schema.dropTable(this.tableName)
  }
}
