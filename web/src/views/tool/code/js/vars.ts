export const relationsType: { name: string; value: string }[] = [
  { name: '一对一[hasOne]', value: 'hasOne' },
  { name: '一对多[hasMany]', value: 'hasMany' },
  { name: '一对一（反向)[belongsTo]', value: 'belongsTo' },
  { name: '多对多[belongsToMany]', value: 'belongsToMany' }
]

export const queryType: { label: string; value: string }[] = [
  { label: '=', value: 'eq' },
  { label: '!=', value: 'neq' },
  { label: '>', value: 'gt' },
  { label: '>=', value: 'gte' },
  { label: '<', value: 'lt' },
  { label: '<=', value: 'lte' },
  { label: 'LIKE', value: 'like' },
  { label: 'IN', value: 'in' },
  { label: 'NOT IN', value: 'notin' },
  { label: 'BETWEEN', value: 'between' }
]

// 页面控件
export const viewComponent: { label: string; value: string }[] = [
  { label: '输入框', value: 'input' },
  { label: '密码框', value: 'password' },
  { label: '文本域', value: 'textarea' },
  { label: '数字输入框', value: 'inputNumber' },
  { label: '标签输入框', value: 'inputTag' },
  { label: '开关', value: 'switch' },
  { label: '滑块', value: 'slider' },
  { label: '数据下拉框', value: 'select' },
  { label: '字典下拉框', value: 'saSelect' },
  { label: '树形下拉框', value: 'treeSelect' },
  { label: '字典单选框', value: 'radio' },
  { label: '字典复选框', value: 'checkbox' },
  { label: '日期选择器', value: 'date' },
  { label: '时间选择器', value: 'time' },
  { label: '评分器', value: 'rate' },
  { label: '级联选择器', value: 'cascader' },
  { label: '用户选择器', value: 'userSelect' },
  { label: '图片上传', value: 'uploadImage' },
  { label: '图片选择', value: 'imagePicker' },
  { label: '文件上传', value: 'uploadFile' },
  { label: '大文件切片', value: 'chunkUpload' },
  { label: '富文本编辑器', value: 'editor' }
]
