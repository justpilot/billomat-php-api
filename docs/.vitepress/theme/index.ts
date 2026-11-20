import DefaultTheme from 'vitepress/theme'
import { h } from 'vue'
import './custom.css'

// Hinweis-Leiste über der Navigation: Die Website beschreibt 4.x; 3.x ist nur noch im README dokumentiert.
export default {
  extends: DefaultTheme,
  Layout: () =>
    h(DefaultTheme.Layout, null, {
      'layout-top': () =>
        h('div', { class: 'version-banner' }, [
          h('span', [
            'Documentation for 4.x. Using 3.x? See the ',
            h('a', { href: 'https://github.com/justpilot/billomat-php-api/tree/3.x#readme' }, '3.x README'),
            ' and the ',
            h('a', { href: 'https://github.com/justpilot/billomat-php-api/blob/main/UPGRADE-4.0.md' }, 'upgrade guide'),
            '.',
          ]),
        ]),
    }),
}
