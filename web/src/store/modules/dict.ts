/**
 * 字典状态管理模块
 *
 * 提供字典数据的状态管理
 *
 *
 * @module store/modules/dict
 * @author saithink
 */
import { defineStore } from 'pinia'
import { ref } from 'vue'
import { fetchGetDictList } from '@/api/auth'

/**
 * 字典状态管理模块
 * - 负责全局字典的加载、缓存、查询
 * - 建议在菜单加载完成后调用 ensureLoaded() 进行初始化
 */
export const useDictStore = defineStore(
  'dictStore',
  () => {
    /** 字典是否已初始化加载 */
    const initialized = ref(false)
    /** 原始字典列表 */
    const dictList = ref<Api.Auth.DictData>()

    /**
     * 加载字典数据并建立索引
     */
    const refresh = async () => {
      try {
        const list = await fetchGetDictList()
        dictList.value = list
        initialized.value = true
      } catch (e) {
        // 保持状态一致：加载失败也标记为未初始化
        initialized.value = false
        throw e
      }
    }

    /** 根据 code 获取字典数据 */
    const getByCode = (code: any): Api.Auth.DictItem[] => {
      return dictList.value?.[code] || []
    }

    /** 根据 code 和 value 获取字典标签 */
    const getDataByValue = (code: any, value: any): Api.Auth.DictItem | undefined => {
      const dict = getByCode(code)
      if (!dict) return undefined

      const item = dict.find((item) => item.value == value)
      return item || undefined
    }

    /**
     * 设置字典列表
     * @param list 字典响应数组
     */
    const setDictList = (list: Api.Auth.DictData) => {
      dictList.value = list
      initialized.value = true
    }

    return {
      initialized,
      dictList,
      refresh,
      setDictList,
      getByCode,
      getDataByValue
    }
  },
  {
    persist: {
      key: 'dict',
      storage: localStorage
    }
  }
)
