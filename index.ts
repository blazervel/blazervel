import { loadEnv, UserConfig } from 'vite'
import { homedir } from 'os'
import path from 'path'
import tailwindcss from 'tailwindcss'
import setupAliases from './resources/js/vite/setup-aliases'
import setupDevServer from './resources/js/vite/setup-dev-server'

import { BlazerelConfigProps } from '../types'

export default (options: BlazerelConfigProps) => ({

  name: 'blazervel',
  
  config: (config: UserConfig, { mode, command }: { mode: string, command: string }) => {

    if (!['build', 'serve'].includes(command)) {
      return config
    }

    const basePath = process.cwd()

    if (options.tailwind === true) {
      config.plugins = config.plugins || []
      
      config.plugins.push(
        tailwindcss()
      )
    }
  
    // Add default aliases (e.g. alias @ -> ./resources/js)
    config = setupAliases(
      config,
      basePath,
      path.resolve(__dirname)
    )

    if (mode !== 'development') {
      return config
    }
  
    console.log(loadEnv(mode, basePath, '').APP_URL || '')
    
    // Configure dev server (e.g. valet https, HMR, etc.)
    config = setupDevServer(
      config,
      loadEnv(mode, basePath, '').APP_URL || '',
      path.resolve(homedir(), '.config/valet/Certificates/')
    )
    
    return config
  }
})