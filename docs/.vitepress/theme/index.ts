import DefaultTheme from 'vitepress/theme'
import { h } from 'vue'
import './custom.css'

// Hinweis-Leiste über der Navigation: Die Website beschreibt 4.x, `main` ist bis zum 4.0-Release noch 3.x.
export default {
  extends: DefaultTheme,
  Layout: () =>
    h(DefaultTheme.Layout, null, {
      'layout-top': () =>
        h('div', { class: 'version-banner' }, [
          h('span', [
            'Documentation for 4.0 (pre-release). Using 3.x? See the ',
            h('a', { href: 'https://github.com/justpilot/billomat-php-api/tree/main#readme' }, 'README on main'),
            '.',
          ]),
        ]),
    }),
}
