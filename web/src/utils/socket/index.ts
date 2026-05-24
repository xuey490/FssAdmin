import request from '@/utils/http'

export default {
  startMeta(templateId: number | string) {
    return request.get<Api.Common.ApiData>({
      url: '/api/flow/instance/start-meta/' + templateId
    })
  },

  resubmitMeta(instanceId: number | string) {
    return request.get<Api.Common.ApiData>({
      url: '/api/flow/instance/resubmit-meta/' + instanceId
    })
  },

  start(params: Record<string, any>) {
    return request.post<any>({
      url: '/api/flow/instance/start',
      data: params
    })
  },

  resubmit(params: Record<string, any>) {
    return request.post<any>({
      url: '/api/flow/instance/resubmit',
      data: params
    })
  },

  myStarted(params: Record<string, any>) {
    return request.get<Api.Common.ApiPage>({
      url: '/api/flow/instance/my-started',
      params
    })
  },

  all(params: Record<string, any>) {
    return request.get<Api.Common.ApiPage>({
      url: '/api/flow/instance/all',
      params
    })
  },

  read(id: number | string) {
    return request.get<Api.Common.ApiData>({
      url: '/api/flow/instance/detail/' + id
    })
  },

  cancel(id: number | string) {
    return request.put<any>({
      url: '/api/flow/instance/cancel/' + id
    })
  },

  terminate(id: number | string) {
    return request.put<any>({
      url: '/api/flow/instance/terminate/' + id
    })
  }
}
