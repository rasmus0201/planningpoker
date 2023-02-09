module.exports = {
  apps: [
    {
      name: 'planningpoker',
      script: './build/server.js',
      autorestart: true,
      env: {
        ENV_PATH: '/Users/rso/http/planningpoker/backend/.env',
      },
      env_production: {
        ENV_PATH: '/var/www/pp.rasmusbundsgaard.dk/backend/.env',
      },
    },
    {
      name: 'planningpoker-schedule',
      script: 'node ./ace schedule',
      autorestart: true,
      env: {
        ENV_PATH: '/Users/rso/http/planningpoker/backend/.env',
      },
      env_production: {
        ENV_PATH: '/var/www/pp.rasmusbundsgaard.dk/backend/.env',
      },
    },
  ],
}
