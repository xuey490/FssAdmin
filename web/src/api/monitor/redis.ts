import request from '@/utils/http'

/**
 * Redis监控API
 */
export const RedisService = {
  /**
   * 获取Redis监控信息
   * @returns Redis监控数据
   */
  list() {
    return request.get<any>({
      url: '/api/core/server/redis'
    })
  }
}

export default RedisService
