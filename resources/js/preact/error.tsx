import React from '@pckg/preact/compat'
// import { Button } from '@blazervel-ui/components'

interface Props {
  status: number
  heading?: string
  message?: string
}

export default function ({
  status,
  heading = "Uh oh! I think you're lost.",
  message = "It looks like the page you're looking for doesn't exist."
}: Props) {

  const lang: Function = (window as any).lang

  const bgImage = 'https://images.unsplash.com/photo-1545972154-9bb223aac798?ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&ixlib=rb-1.2.1&auto=format&fit=crop&w=3050&q=80&exp=8&con=-15&sat=-75'

  heading = heading || lang(`blazervel::errors.${status}.heading`)
  message = message || lang(`blazervel::errors.${status}.message`)

  return (
    <div className="relative h-screen bg-gradient-to-b from-theme-300 to-theme-100">
      <div className="absolute inset-0 bg-cover bg-bottom mix-blend-multiply opacity-30" style={{ backgroundImage: `url("${bgImage}")` }}></div>
      <main className="relative z-1">
        <div className="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 sm:py-24 lg:px-8 lg:py-48">
          <p className="text-base font-semibold text-chrome-900 text-opacity-50">
            {status}
          </p>
          <h1 className="mt-2 text-4xl font-bold tracking-tight text-chrome-50 sm:text-5xl">
            {heading}
          </h1>
          <p className="mt-2 text-lg font-medium text-chrome-900 text-opacity-50">
            {message}
          </p>
          {/* <div className="mt-6">
            <Button
              className="border-none"
              icon="home"
              iconType="outline"
              route="/"
              text={lang('blazervel::errors.take_me_home')} />
          </div> */}
        </div>
      </main>
    </div>
  )
}