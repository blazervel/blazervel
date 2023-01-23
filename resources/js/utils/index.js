export { resolveComponent } from './resolve-component'

export const snake = str => (
  str
    .replace(/[A-Z]/g, char => ` ${char.toLowerCase()}`)
    .trim()
    .split(' ')
    .join('-')
)

export const ucfirst = str => (
  str.charAt(0).toUpperCase() + str.slice(1)
)

export const camelCase = str => (
  str
    .toLowerCase()
    .replace(/[^a-zA-Z0-9]+(.)/g, (m, char) => char.toUpperCase())
)

export const pascalCase = str => (
  ucfirst(
    camelCase(str)
  )
)