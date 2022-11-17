export interface BlazervelLoaderProps {
  certsPath: string
  packagePath: string
  basePath: string
  loadEnv: Function
  lodash: any
}

export interface BlazerelConfigProps {
  certsPath?: string
  inertia?: boolean
}

export interface BlazervelComposerConfigProps {
  type: string
  url: string
  path: string
  options: {
    symlink: boolean
  }
}

export interface ComposerConfigProps {
  repositories: {
    blazervel: BlazervelComposerConfigProps
  }
}

export interface AuthAttemptProps {
  email: string
  password: string
}