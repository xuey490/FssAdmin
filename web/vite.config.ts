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
          // Vite 8 (Rolldown) 要求 manualChunks 为函数，不再支持对象写法
          manualChunks(id) {
            if (!id.includes('node_modules')) return
            if (/node_modules[\\/](vue|vue-router|pinia|@vueuse[\\/]core)/.test(id)) {
              return 'vue-vendor'
            }
            if (id.includes('node_modules/element-plus') || id.includes('element-plus@')) {
              return 'element-plus'
            }
            if (id.includes('node_modules/echarts') || id.includes('echarts@')) {
              return 'echarts'
            }
            if (id.includes('node_modules/xlsx') || id.includes('xlsx@')) {
              return 'xlsx'
            }
            if (/node_modules[\\/](axios|crypto-js|file-saver)/.test(id)) {
              return 'utils'
            }
          },
          chunkFileNames: 'js/[name]-[hash].js',
          entryFileNames: 'js/[name]-[hash].js',
          assetFileNames: 'assets/[name]-[hash].[ext]'
        }
      },
	  // Rolldown专属配置，不影响rollupOptions，底层Rust引擎生效
	  rolldownOptions: {
		  parallel: true, // 开启Rust多线程打包
		  treeshake: true,
	  },
      outDir: 'dist',
	  // Vite8 默认bundler: 'rolldown'，可显式写死确认
	  bundler: 'rolldown',
	  logLevel: 'debug', // 构建开启debug日志
      chunkSizeWarningLimit: 2000,
      // ✅ Rolldown 内置压缩，无需 terser
      minify: 'esbuild'
    },
    plugins: [
      vue(),
      tailwindcss(),
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
        'element-plus/es',
        'element-plus/es/components/*/style/css',
        'element-plus/es/components/*/style/index'
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