import { readdirSync, readFileSync } from 'node:fs'
import { dirname, join, normalize, relative } from 'node:path'
import { fileURLToPath } from 'node:url'
import { defineConfig, type DefaultTheme } from 'vitepress'

const docsDir = join(dirname(fileURLToPath(import.meta.url)), '..')
const repo = 'https://github.com/justpilot/billomat-php-api'
const branch = '4.x'

// Seitentitel aus der ersten H1-Überschrift (vor manchen steht ein Quellkommentar).
function title(file: string): string {
  const match = readFileSync(join(docsDir, file), 'utf8').match(/^# (.+)$/m)
  return match ? match[1].replace(/`/g, '') : file
}

// Sidebar-Gruppe aus allen Markdown-Dateien eines Ordners, alphabetisch nach Titel.
function group(text: string, dir: string, collapsed = false): DefaultTheme.SidebarItem {
  const items = readdirSync(join(docsDir, dir))
    .filter((file) => file.endsWith('.md') && file !== 'README.md')
    .map((file) => ({ text: title(join(dir, file)), link: `/${dir}/${file.replace(/\.md$/, '')}` }))
    .sort((a, b) => a.text.localeCompare(b.text))

  return { text, collapsed, items }
}

export default defineConfig({
  title: 'Billomat PHP SDK',
  description: 'Modern, fully typed PHP 8.5 SDK for the Billomat API',
  lang: 'en',
  cleanUrls: true,
  lastUpdated: true,
  // Interne Arbeitsdateien gehören nicht auf die Website.
  srcExclude: ['spec/**', 'audit.md', 'roadmap-v4.md', '**/node_modules/**'],
  rewrites: { 'resources/README.md': 'resources/index.md' },
  head: [['meta', { name: 'theme-color', content: '#3c8772' }]],

  markdown: {
    config(md) {
      // Links auf Dateien außerhalb von docs/ (z. B. ../../src/Model/Money.php) zeigen auf GitHub.
      const defaultRender = md.renderer.rules.link_open
      md.renderer.rules.link_open = (tokens, idx, options, env, self) => {
        const href = tokens[idx].attrGet('href')
        if (href && !/^[a-z]+:/i.test(href) && !href.startsWith('#') && env.relativePath) {
          const [path, hash] = href.split('#')
          const target = normalize(join('docs', dirname(env.relativePath), path))
          if (path !== '' && !target.startsWith('docs/')) {
            tokens[idx].attrSet('href', `${repo}/blob/${branch}/${relative('.', target)}${hash ? `#${hash}` : ''}`)
          }
        }

        return defaultRender ? defaultRender(tokens, idx, options, env, self) : self.renderToken(tokens, idx, options)
      }
    },
  },

  themeConfig: {
    nav: [
      { text: 'Guide', link: '/configuration' },
      { text: 'Resources', link: '/resources/' },
      { text: 'Upgrade to 4.0', link: `${repo}/blob/${branch}/UPGRADE-4.0.md` },
      { text: 'Changelog', link: `${repo}/blob/${branch}/CHANGELOG.md` },
    ],
    sidebar: [
      {
        text: 'Guide',
        items: [
          { text: 'Configuration', link: '/configuration' },
          { text: 'Error handling', link: '/error-handling' },
          { text: 'Testing', link: '/testing' },
          { text: 'Migration from phobetor/billomat', link: '/migration-from-phobetor' },
        ],
      },
      group('Concepts', 'concepts'),
      group('Advanced', 'advanced'),
      { ...group('Resources', 'resources', true), link: '/resources/' },
    ],
    search: { provider: 'local' },
    editLink: { pattern: `${repo}/edit/${branch}/docs/:path`, text: 'Edit this page on GitHub' },
    socialLinks: [{ icon: 'github', link: repo }],
    footer: {
      message: 'Unofficial SDK, not affiliated with Billomat GmbH. Released under the Apache License 2.0.',
      copyright: 'Copyright © 2025–present Dimitri Dovgan',
    },
  },
})
