import request from '@/utils/http'

/**
 * Redis 缓存浏览器 API
 */
export const RedisBrowserService = {
  /**
   * 获取第一级键（前缀）
   */
  getLevel1(pattern: string = '*') {
    return request.get<any>({
      url: '/api/core/server/redis/browser/level1',
      params: { pattern }
    })
  },

  /**
   * 获取第二级键
   */
  getLevel2(prefix: string) {
    return request.get<any>({
      url: '/api/core/server/redis/browser/level2',
      params: { prefix }
    })
  },

  /**
   * 获取第三级键
   */
  getLevel3(prefix: string) {
    return request.get<any>({
      url: '/api/core/server/redis/browser/level3',
      params: { prefix }
    })
  },

  /**
   * 获取键的详细信息
   */
  getKeyInfo(key: string) {
    return request.get<any>({
      url: '/api/core/server/redis/browser/key-info',
      params: { key }
    })
  },

  /**
   * 删除键
   */
  deleteKey(key: string) {
    return request.del<any>({
      url: '/api/core/server/redis/browser/delete',
      data: { key }
    })
  },

  /**
   * 批量删除键（按模式）
   */
  deleteByPattern(pattern: string) {
    return request.del<any>({
      url: '/api/core/server/redis/browser/delete',
      data: { pattern }
    })
  }
}

export default RedisBrowserService
