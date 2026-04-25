<template>
  <pre class="code-pre"><code class="hljs" v-html="highlightedCode"></code></pre>
</template>

<script type="ts" setup>
  import { ref, watch, onMounted } from 'vue'
  import hljs from 'highlight.js/lib/core' // 核心
  import 'highlight.js/styles/github-dark.css' // 主题示例
  
  // 引入所需语言
  import javascript from 'highlight.js/lib/languages/javascript'
  import typescript from 'highlight.js/lib/languages/typescript'
  import php from 'highlight.js/lib/languages/php'
  import xml from 'highlight.js/lib/languages/xml' // Vue 模板依赖 xml
  
  // 注册语言
  hljs.registerLanguage('javascript', javascript)
  hljs.registerLanguage('typescript', typescript)
  hljs.registerLanguage('php', php)
  hljs.registerLanguage('xml', xml)
  // highlight.js 中通常将 vue 当作 xml (HTML) 来高亮，或者显式注册为 vue
  hljs.registerLanguage('vue', xml)
  hljs.registerLanguage('html', xml)

  const props = defineProps({
    code: {
      type: String,
      required: true
    },
    language: {
      type: String,
      default: 'javascript' // 默认语言，可传入 'vue' 或 'php'
    }
  })

  const highlightedCode = ref('')

  const doHighlight = () => {
    if (!props.code) {
      highlightedCode.value = ''
      return
    }
    try {
      // ignoreIllegals 防止非法语法报错
      highlightedCode.value = hljs.highlight(props.code, {
        language: props.language,
        ignoreIllegals: true
      }).value
    } catch (__) {
      console.error('代码高亮失败', __)
      // 降级：语法不支持时纯文本显示，必须转义 HTML 实体防止 XSS 和渲染异常
      highlightedCode.value = props.code
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;')
    }
  }

  watch(() => [props.code, props.language], doHighlight)
  onMounted(doHighlight)
</script>

<style scoped>
  .code-pre {
    border-radius: 8px;
    overflow-x: auto;
    font-size: 14px;
    line-height: 1.5;
  }
</style>
