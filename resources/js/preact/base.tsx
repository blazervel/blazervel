import React, { useEffect, useState } from '@pckg/preact/compat'
import { pascalCase, resolveComponent } from '../utils'
import { controllers } from '../../../dist/config/blazervel-controllers.json'

interface ControllerConfig {
  key: string
  view: string
  route: string
  methods: Array<object>
}

const controllerByUrl = (url: string): ControllerConfig|null => {

  const matchesRoute = (route): boolean => {
          return url === route // Need to account for {args}
        },
        lookup = (
          Object.entries(controllers)
            .filter(([controllerClass, config]) => matchesRoute(config.route))
            .map(([controllerClass, config]) => config)
        )

  return lookup[0] || null
}

export default function () {

  const [page, setPage] = useState(null)

  const loadPage = async () => {

    const controller = controllerByUrl(window.location.pathname)

    let resolvePage, rejectPage

    const promise = new Promise((resolve, reject) => {
      resolvePage = resolve
      rejectPage = reject
    })

    if (!controller) {
      rejectPage()
      return
    }

    const component = await resolveComponent(controller.view)

    resolvePage({
      Component: component.default || component,
      props: {
        controller: new Proxy({
          ...controller,
          boot() {
            // Make request to load instance with current page state
            /*
            fetch(`api/blazervel/controllers/${this.name}`, {
              url: window.location.pathname,
              state: JSON.stringify(this.instance)
            })
            .then(response => this.instance = response.data)
            */
          },
          call(method: string, ...params) {
            console.log(method, params, this.methods)
          }
        }, {get: (target: object, prop: string, receiver: ProxyConstructor) => {

          const controller = target,
                controllerMethods = target.methods,
                controllerProperties = target.properties,
                context = receiver === this ? target : receiver

          if (typeof controllerMethods[prop] !== 'undefined') {
            return (...params) => controllerMethods[prop].call(context, params)
          }

          if (typeof controllerProperties[prop] !== 'undefined') {
            const controllerProperty = controllerProperties[prop]

            if (controllerProperty.value === null && !controllerProperty.allowsNull) {
              return undefined
            }

            return controllerProperty.value
          }

          throw new Error(`[${prop}] is undefined on ${controller.name} controller`)
        }})
      }
    })

    return promise
  }

  useEffect(() => {
    if (!page) {
      loadPage().then(setPage)
    }
  }, [])

  if (!page) {
    return <>Loading...</>
  }

  const { Component, props } = page

  return (
    <div className="z-0 relative">
      <Component {...props} />
    </div>
  )
}