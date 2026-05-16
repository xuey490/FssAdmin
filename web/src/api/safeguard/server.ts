import request from '@/utils/http'

/**
 * 服务器信息API
 */
export default {
  /**
   * 服务监控
   * @param params 搜索参数
   * @returns 数据列表
   */
  monitor(params: Record<string, any>) {
    return request.get<any>({
      url: '/api/core/server/monitor',
      params
    })
  },

  /**
   * 缓存列表
   * @param params 搜索参数
   * @returns 数据列表
   */
  cache(params: Record<string, any>) {
    return request.get<any>({
      url: '/core/server/cache',
      params
    })
  },

  /**
   * 清理缓存
   * @param params 搜索参数
   * @returns 数据列表
   */
  clear(params: Record<string, any>) {
    return request.post<any>({
      url: '/core/server/clear',
      data: params
    })
  },

  /**
   * Redis 信息
   * @returns Redis 信息
   */
  redisInfo() {
    return request.get<any>({
      url: '/core/redis/info'
    })
  },

  /**
   * Redis 操作列表
   * @returns 操作列表
   */
  redisOperations() {
    return request.get<any>({
      url: '/core/redis/operations'
    })
  },

  /**
   * Redis 键列表
   * @param pattern 键模式
   * @returns 键列表
   */
  redisKeys(params: Record<string, any> = {}) {
    return request.get<any>({
      url: '/core/redis/keys',
      params
    })
  },

  /**
   * 删除 Redis 键
   * @param keys 键列表
   * @returns 结果
   */
  deleteRedisKeys(params: Record<string, any>) {
    return request.del<any>({
      url: '/core/redis/deleteKeys',
      data: params
    })
  }
}
