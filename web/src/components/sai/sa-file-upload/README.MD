<!-- 
  sa-file-upload 组件使用示例
  
  基本用法：
  <sa-file-upload v-model="fileUrl" />
  
  多文件上传：
  <sa-file-upload v-model="fileUrls" :multiple="true" :limit="5" />
  
  拖拽上传：
  <sa-file-upload v-model="fileUrl" :drag="true" />
  
  限制文件类型：
  <sa-file-upload 
    v-model="fileUrl" 
    accept=".pdf,.doc,.docx"
    accept-hint="PDF、Word"
  />
-->

<template>
  <div class="demo-container">
    <h2>文件上传组件示例</h2>

    <!-- 示例1: 基本用法 -->
    <el-card header="基本用法" class="demo-card">
      <sa-file-upload v-model="singleFile" />
      <div class="result">当前文件: {{ singleFile }}</div>
    </el-card>

    <!-- 示例2: 多文件上传 -->
    <el-card header="多文件上传" class="demo-card">
      <sa-file-upload 
        v-model="multipleFiles" 
        :multiple="true" 
        :limit="5" 
      />
      <div class="result">当前文件: {{ multipleFiles }}</div>
    </el-card>

    <!-- 示例3: 拖拽上传 -->
    <el-card header="拖拽上传" class="demo-card">
      <sa-file-upload 
        v-model="dragFile" 
        :drag="true"
        button-text="点击或拖拽上传"
      />
      <div class="result">当前文件: {{ dragFile }}</div>
    </el-card>

    <!-- 示例4: 限制文件类型 - PDF/Word -->
    <el-card header="限制文件类型 (PDF、Word)" class="demo-card">
      <sa-file-upload 
        v-model="docFile" 
        accept=".pdf,.doc,.docx"
        accept-hint="PDF、Word"
        :max-size="20"
      />
      <div class="result">当前文件: {{ docFile }}</div>
    </el-card>

    <!-- 示例5: 限制文件类型 - Excel -->
    <el-card header="限制文件类型 (Excel)" class="demo-card">
      <sa-file-upload 
        v-model="excelFile" 
        accept=".xls,.xlsx"
        accept-hint="Excel"
      />
      <div class="result">当前文件: {{ excelFile }}</div>
    </el-card>

    <!-- 示例6: 压缩文件 -->
    <el-card header="压缩文件上传" class="demo-card">
      <sa-file-upload 
        v-model="zipFile" 
        accept=".zip,.rar,.7z"
        accept-hint="ZIP、RAR、7Z"
        :max-size="50"
        :drag="true"
      />
      <div class="result">当前文件: {{ zipFile }}</div>
    </el-card>
  </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue'

const singleFile = ref('')
const multipleFiles = ref<string[]>([])
const dragFile = ref('')
const docFile = ref('')
const excelFile = ref('')
const zipFile = ref('')
</script>

<style scoped lang="scss">
.demo-container {
  padding: 20px;

  h2 {
    margin-bottom: 20px;
  }

  .demo-card {
    margin-bottom: 20px;

    .result {
      margin-top: 15px;
      padding: 10px;
      background-color: #f5f7fa;
      border-radius: 4px;
      font-size: 12px;
      color: #606266;
      word-break: break-all;
    }
  }
}
</style>
