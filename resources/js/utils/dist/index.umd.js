!function(e,n){"object"==typeof exports&&"undefined"!=typeof module?n(exports):"function"==typeof define&&define.amd?define(["exports"],n):n((e=e||self).Blazervel={})}(this,function(e){e.conditionalClassNames=function(){return[].slice.call(arguments).filter(Boolean).join(" ")},e.lang=function(e,n,t){var i=("undefined"!=typeof BlazervelLang?BlazervelLang:null==globalThis?void 0:globalThis.BlazervelLang).translations,o=e.split("."),l=i;return o.map(function(n){return l=l[n]||e}),l},e.mergeCssClasses=function(){var e=[];return[].slice.call(arguments).filter(function(e){return["object","array","string"].indexOf(typeof e)>=0}).forEach(function(n){"string"==typeof n&&(n=n.split(" ")),e=e.concat(n)}),e.join(" ").trim()}});
//# sourceMappingURL=index.umd.js.map