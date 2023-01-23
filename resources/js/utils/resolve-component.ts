export function resolveComponent(name: string): any {

  let components,
      alias = '@'

  if (name.includes('@blazervel')) {

    components = import.meta.glob('@blazervel/**/*.*')
    alias = '@blazervel'

  } else {

    components = import.meta.glob('@/**/*.*')

  }

  const page = componentLookup(components, name.replace(alias, ''))

  if (page) {
    return page
  }

  return null
}

const componentLookup = (components, name) => {
  
  let page = null

  for (const path in components) {

    const ext = path.split('.').reverse()[0]
    
    if (!path.endsWith(`${name}.${ext}`)) {
      continue
    }

    page = components[path]
    page = typeof page === 'function' ? page() : page

    break
  }

  return page
}