import config from './config'

export default async function (
  key: string,
  replace: object = {},
  fallback: boolean = true,
  count: number|null = null
) {

  const langConfig = await config.localization,
        translations: object = langConfig.translations,
        keys: Array<string> = key.split('.')

  console.log(config, translations)

  let translation: string|null = null

  keys.map(k => translation = translations[k] || '')

  if (!translation && fallback) {
    for (var localeKey in translations) {
      keys.map(k => translation = translations[localeKey][k] || '')

      if (translation) {
        break
      }
    }
  }

  count = count !== null && (Array.isArray(count) || count === Object(count)) 
            ? Object.values(count).length 
            : (typeof count === 'number' ? count : null)
  
  if (count !== null) {
    // Support pluralization (https://laravel.com/docs/9.x/localization#pluralization)
  }

  if (!translation) {
    translation = key
  }

  for (var key in replace) {
    translation = translation.replace(':' + key, replace[key])
  }

  return translation
}