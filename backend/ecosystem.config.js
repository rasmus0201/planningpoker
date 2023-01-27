module.exports = {
  apps: [
    {
      name: 'planningpoker',
      script: './build/server.js',
      autorestart: true,
      env: {
        "ENV_PATH": "/var/www/pp.rasmusbundsgaard.dk/backend/.env"
      }
    },
  ],
}

