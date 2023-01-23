import { useEffect, useState } from '@pckg/preact/compat'
import blazervel from '../app/blazervel'
import { config, lang, route } from '@blazervel'

window.lang = lang(config.translations)
window.route = route(config.routes)

export default function () {

  const [page, setPage] = useState(null)

  useEffect(() => {

    const linkElements = document.querySelectorAll('a:not([target="_blank"])'),
          handleClick = (event, link) => {
            event.preventDefault()
    
            const state = {
              url: link.getAttribute('href')
            }
    
            // Update history state
            window.history.pushState(state, null, state.url)
    
            window.dispatchEvent(
              new PopStateEvent('popstate', {state})
            )
    
            return false
          },
          handlePopstate = (event) => {
            blazervel.Page.load(event.state.url).then(page => setPage(page))
          }

    linkElements.forEach(link => link.addEventListener('click', (event) => handleClick(event, link)))

    window.addEventListener('popstate', handlePopstate)

    if (!page) {
      blazervel.Page.load(window.location.href).then(setPage)
    }

    return () => {
      linkElements.forEach(link => link.removeEventListener('click', (event) => handleClick(event, link)))
      window.removeEventListener('popstate', handlePopstate)
    }

  }, [page])

  if (!page) {
    return <></>
  }

  const { Component, props } = page

  return (
    <div className="z-0 relative">
      <Component $b={blazervel} {...props} />
    </div>
  )
}