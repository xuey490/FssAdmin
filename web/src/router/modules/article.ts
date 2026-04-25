import { AppRouteRecord } from '@/types/router'

export const articleRoutes: AppRouteRecord = {
  path: '/article',
  name: 'Article',
  component: '/index/index',
  meta: {
    title: 'menus.article.title',
    icon: 'ri:article-line'
  },
  children: [
    {
      path: 'list',
      name: 'ArticleList',
      component: '/article/index',
      meta: {
        title: 'menus.article.list',
        keepAlive: true
      }
    }
  ]
}
