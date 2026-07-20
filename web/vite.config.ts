import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'
import { fileURLToPath } from 'url'
import vueDevTools from 'vite-plugin-vue-devtools'
import viteCompression from 'vite-plugin-compression'
import Components from 'unplugin-vue-components/vite'
import AutoImport from 'unplugin-auto-import/vite'
import ElementPlus from 'unplugin-element-plus/vite'
import { ElementPlusResolver } from 'unplugin-vue-components/resolvers'
import tailwindcss from '@tailwindcss/vite'
import IconifyOffline from 'vite-plugin-iconify-offline'
import { resolve } from 'path'



const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)

export default ({ mode }: { mode: string }) => {
  const root = process.cwd()
  const env = loadEnv(mode, root)
  const { VITE_VERSION, VITE_BASE_URL, VITE_API_URL, VITE_API_PROXY_URL } = env

  console.log(`🚀 API_URL = ${VITE_API_URL}`)
  console.log(`🚀 VERSION = ${VITE_VERSION}`)

  return defineConfig({
    define: {
      __APP_VERSION__: JSON.stringify(VITE_VERSION)
    },
    base: VITE_BASE_URL,
    server: {
      host: '0.0.0.0',
      port: 5730,
      hmr: true,
      open: true,
      proxy: {
        [env.VITE_API_URL]: {
          target: env.VITE_API_PROXY_URL,
          rewrite: (path) => path.replace(new RegExp('^' + env.VITE_API_URL), ''),
          changeOrigin: true
        }
      }
    },
    resolve: {
      // 避免多份 Vue 实例导致 vue-i18n 与 runtime 初始化不一致
      dedupe: ['vue'],
      alias: {
        '@': fileURLToPath(new URL('./src', import.meta.url)),
        '@views': resolvePath('src/views'),
        '@imgs': resolvePath('src/assets/images'),
        '@icons': resolvePath('src/assets/icons'),
        '@utils': resolvePath('src/utils'),
        '@stores': resolvePath('src/store'),
        '@styles': resolvePath('src/assets/styles')
      }
    },
    build: {
      target: 'es2015',
      dynamicImportVarsOptions: {
        warnOnError: true,
        exclude: [],
        include: ['src/views/**/*.vue']
      },
      rollupOptions: {
        output: {
          // Vite 8 使用 Rolldown：manualChunks 已废弃，需用 codeSplitting.groups
          // vue-i18n 必须与 vue 同 chunk，否则 Rolldown 会丢失 init_runtime_dom_esm_bundler 引用
          codeSplitting: {
            groups: [
              {
                name: 'vue-vendor',
                test: /node_modules[\\/](vue|vue-router|pinia|vue-i18n|@intlify|@vueuse[\\/])/
              },
              {
                name: 'element-plus',
                test: /node_modules[\\/]element-plus/
              },
              {
                name: 'echarts',
                test: /node_modules[\\/]echarts/
              },
              {
                name: 'xlsx',
                test: /node_modules[\\/]xlsx/
              },
              {
                name: 'utils',
                test: /node_modules[\\/](axios|crypto-js|file-saver)/
              }
            ]
          },
          chunkFileNames: 'assets/[name]-[hash].js',
          entryFileNames: 'assets/[name]-[hash].js',
          assetFileNames: 'assets/[name]-[hash].[ext]'
        }
      },
      outDir: 'dist',
      chunkSizeWarningLimit: 2000
    },
    plugins: [
      vue(),
      tailwindcss(),
      IconifyOffline({ package: '@iconify/vue' }),
      AutoImport({
        imports: ['vue', 'vue-router', 'pinia', '@vueuse/core'],
        dts: 'src/types/import/auto-imports.d.ts',
        resolvers: [ElementPlusResolver()],
        eslintrc: {
          enabled: true,
          filepath: './.auto-import.json',
          globalsPropValue: true
        }
      }),
      Components({
        dts: 'src/types/import/components.d.ts',
        resolvers: [ElementPlusResolver()]
      }),
      ElementPlus({ useSource: true }),
      viteCompression({
        verbose: false,
        algorithm: 'gzip',
        ext: '.gz',
        threshold: 10240,
        deleteOriginFile: false
      }),
      vueDevTools()
    ],
    optimizeDeps: {
      // Rolldown 1.0.2+ 预构建 vue-i18n 时会丢失 init_runtime_dom_esm_bundler 导入
      // 排除后由 Vite 直接加载源码，规避该回归
      exclude: ['vue-i18n'],
      include: [
        'vue',
        'echarts/core',
        'echarts/charts',
        'echarts/components',
        'echarts/renderers',
        'xlsx',
        'xgplayer',
        'crypto-js',
        'file-saver',
        'vue-img-cutter',
        '@wangeditor/editor',
        '@wangeditor/editor-for-vue',
      ]
    },
    css: {
      preprocessorOptions: {
        scss: {
          additionalData: `
            @use "@styles/core/el-light.scss" as *; 
            @use "@styles/core/mixin.scss" as *;
          `
        }
      },
      postcss: {
        plugins: [
          {
            postcssPlugin: 'internal:charset-removal',
            AtRule: {
              charset: (atRule) => {
                if (atRule.name === 'charset') atRule.remove()
              }
            }
          }
        ]
      }
    }
  })
}

function resolvePath(paths: string) {
  return path.resolve(__dirname, paths)
}