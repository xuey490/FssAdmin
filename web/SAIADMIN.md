## 前端开发

[module] 代表一个应用模块

[business] 代表一个功能模块分组

[table] 代表一个具体的功能

### 前端开发规范

```
src/views/plugin/[module]/
├── api
│   ├── [business]/                    # 功能模块接口分组
|   │   ├── [table].ts                 # 具体功能接口
├── [business]/                        # 功能模块分组
│   ├── [table]/                       # 具体功能目录
│   │   ├── index.vue                  # 功能主页面（列表页）
│   │   └── modules/                   # 子组件目录
│   │       ├── table-search.vue       # 搜索表单组件
│   │       └── edit-dialog.vue        # 编辑弹窗组件
```

### 组件说明

| 组件文件 | 说明 |
|---------|------|
| `table.ts`  | 具体功能接口
| `index.vue` | 功能主页面，包含列表展示、操作按钮等 |
| `modules/table-search.vue` | 搜索表单组件，用于筛选列表数据 |
| `modules/edit-dialog.vue` | 编辑弹窗组件，用于新增/编辑数据 |