import { AppRouteRecord } from '@/types/router'

export const flowRoutes: AppRouteRecord = {
  path: '/flow',
  name: 'Flow',
  component: '/index/index',
  meta: {
    title: '工作流管理',
    icon: 'ri:flow-chart'
  },
  children: [
    {
      path: 'center',
      name: 'FlowCenter',
      component: '/flow/center/index',
      meta: {
        title: '流程中心',
        keepAlive: true
      }
    },
    {
      path: 'category',
      name: 'FlowCategory',
      component: '/flow/category/index',
      meta: {
        title: '流程分类',
        keepAlive: true
      }
    },
    {
      path: 'template',
      name: 'FlowTemplate',
      component: '/flow/template/index',
      meta: {
        title: '流程模板',
        keepAlive: true
      }
    },
    {
      path: 'template/design/:id',
      name: 'FlowTemplateDesign',
      component: '/flow/template/design',
      meta: {
        title: '流程设计',
        isHide: true
      }
    },
    {
      path: 'template/form-design/:id',
      name: 'FlowFormDesign',
      component: '/flow/template/form-design',
      meta: {
        title: '表单设计',
        isHide: true
      }
    },
    {
      path: 'instance/start',
      name: 'FlowInstanceStart',
      component: '/flow/instance/start-entry',
      meta: {
        title: '发起流程',
        isHide: true
      }
    },
    {
      path: 'instance/start/:templateId',
      name: 'FlowInstanceStartByTemplate',
      component: '/flow/instance/start-entry',
      meta: {
        title: '发起流程',
        isHide: true
      }
    },
    {
      path: 'instance/resubmit/:instanceId',
      name: 'FlowInstanceResubmit',
      component: '/flow/instance/start-entry',
      meta: {
        title: '修改并重提',
        isHide: true
      }
    },
    {
      path: 'instance/start-legacy',
      name: 'FlowInstanceStartLegacy',
      component: '/flow/instance/start',
      meta: {
        title: '发起流程',
        isHide: true
      }
    },
    {
      path: 'instance/start-legacy/:templateId',
      name: 'FlowInstanceStartLegacyByTemplate',
      component: '/flow/instance/start',
      meta: {
        title: '发起流程',
        isHide: true
      }
    },
    {
      path: 'instance/start-fixed',
      name: 'FlowInstanceStartFixed',
      component: '/flow/instance/start-fixed',
      meta: {
        title: '指定表单发起',
        isHide: true
      }
    },
    {
      path: 'instance/start-fixed/:templateId',
      name: 'FlowInstanceStartFixedByTemplate',
      component: '/flow/instance/start-fixed',
      meta: {
        title: '指定表单发起',
        isHide: true
      }
    },
    {
      path: 'delegate',
      name: 'FlowDelegate',
      component: '/flow/delegate/index',
      meta: {
        title: '流程委托',
        keepAlive: true
      }
    },
    {
      path: 'message',
      name: 'FlowMessage',
      component: '/flow/message/index',
      meta: {
        title: '消息中心',
        keepAlive: true
      }
    },
    {
      path: 'fixed-form',
      name: 'FlowFixedForm',
      component: '/flow/fixed-form/index',
      meta: {
        title: '固定表单',
        keepAlive: true
      }
    },
    {
      path: 'instance/my-started',
      name: 'FlowMyStarted',
      component: '/flow/instance/my-started',
      meta: {
        title: '我的发起',
        keepAlive: true
      }
    },
    {
      path: 'instance/template/:templateId',
      name: 'FlowTemplateInstanceList',
      component: '/flow/instance/template-list',
      meta: {
        title: '模板流程实例',
        isHide: true
      }
    },
    {
      path: 'instance/detail/:id',
      name: 'FlowInstanceDetail',
      component: '/flow/instance/detail',
      meta: {
        title: '流程详情',
        isHide: true
      }
    },
    {
      path: 'task/pending',
      name: 'FlowTaskPending',
      component: '/flow/task/pending',
      meta: {
        title: '待办审批',
        keepAlive: true
      }
    },
    {
      path: 'task/completed',
      name: 'FlowTaskCompleted',
      component: '/flow/task/completed',
      meta: {
        title: '已办审批',
        keepAlive: true
      }
    },
    {
      path: 'task/reverted-canceled',
      name: 'FlowTaskRevertedCanceled',
      component: '/flow/task/reverted-canceled',
      meta: {
        title: '撤销退回',
        keepAlive: true
      }
    },
    {
      path: 'task/copy-me',
      name: 'FlowTaskCopyMe',
      component: '/flow/task/copy-me',
      meta: {
        title: '抄送给我',
        keepAlive: true
      }
    },
    {
      path: 'form/leave-request',
      name: 'FlowFormLeaveRequest',
      component: '/flow/form/leave-request',
      meta: {
        title: '请假申请',
        isHide: true
      }
    },
    {
      path: 'form/expense-claim',
      name: 'FlowFormExpenseClaim',
      component: '/flow/form/expense-claim',
      meta: {
        title: '报销申请',
        isHide: true
      }
    },
    {
      path: 'form/purchase-request',
      name: 'FlowFormPurchaseRequest',
      component: '/flow/form/purchase-request',
      meta: {
        title: '采购申请',
        isHide: true
      }
    }
  ]
}
