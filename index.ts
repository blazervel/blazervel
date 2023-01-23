import { loadEnv, UserConfig, searchForWorkspaceRoot } from 'vite'
import { homedir } from 'os'
import path from 'path'
import { exec } from 'node:child_process'
import setupDevServer from './resources/js/vite/setup-dev-server'

export interface BlazerelConfigProps {
  certsPath?: string
}

export default (options: BlazerelConfigProps) => ({

  name: 'blazervel',
  
  config: (config: UserConfig, { mode, command }: { mode: string, command: string }) => {

    if (!['build', 'serve'].includes(command)) {
      return config
    }
    
    // Generate static config/schema files
    exec('php artisan blazervel:build')

    // Add default aliases (e.g. alias @ -> ./resources/js)
    const basePath = searchForWorkspaceRoot(process.cwd()),
          packagePath = path.resolve(__dirname)

    config.server = config.server || {}
    config.server.fs = config.server.fs || {}

    config.server.fs.allow = [
      ...(config.server.fs.allow || []),
      path.relative(basePath, packagePath),
      basePath
    ]

    config.resolve = config.resolve || {}
    
    config.resolve.alias = {
      ...(config.resolve.alias || {}),
      '@tightenco/ziggy': `${basePath}/vendor/tightenco/ziggy/src/js`,
      '@blazervel': `${packagePath}/resources/js`,
      '@pckg': `${basePath}/node_modules`,
      '@': path.resolve('./resources/js'),
    }

    if (mode !== 'development') {
      return config
    }

    // Configure dev server (e.g. valet https, HMR, etc.)
    config = setupDevServer(
      config,
      loadEnv(mode, process.cwd(), '').APP_URL || '',
      path.resolve(homedir(), '.config/valet/Certificates/') // options.certsPath
    )
    
    return config
  }
})