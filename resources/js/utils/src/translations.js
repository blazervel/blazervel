export const lang = ( key, count, language ) => {

  const { translations } = typeof BlazervelLang !== 'undefined' ? BlazervelLang : globalThis?.BlazervelLang
  
  const keys = key.split('.')
  
  let translation = translations

  keys.map(k => translation = translation[k] || key)

  return translation
}