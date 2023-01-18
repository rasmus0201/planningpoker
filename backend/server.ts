/*
|--------------------------------------------------------------------------
| AdonisJs Server
|--------------------------------------------------------------------------
|
| The contents in this file is meant to bootstrap the AdonisJs application
| and start the HTTP server to accept incoming connections. You must avoid
| making this file dirty and instead make use of `lifecycle hooks` provided
| by AdonisJs service providers for custom code.
|
*/

import 'reflect-metadata'
import sourceMapSupport from 'source-map-support'
import { createServer } from 'https'
import { Ignitor } from '@adonisjs/core/build/src/Ignitor'
import { resolve } from 'path'
import { readFileSync } from 'fs'
import os from 'os'

sourceMapSupport.install({ handleUncaughtExceptions: false })

const sslDir = resolve(os.homedir(), '.ssl')

const privateKey = readFileSync(resolve(sslDir, 'localhost-key.pem'), 'utf8')
const certificate = readFileSync(resolve(sslDir, 'localhost.pem'), 'utf8')
const options = { key: privateKey, cert: certificate }

sourceMapSupport.install({ handleUncaughtExceptions: false })

new Ignitor(__dirname).httpServer().start((handle) => {
  return createServer(options, handle)
})
