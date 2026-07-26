import{d as f,r as V,Y as z,c as h,o as v,e as a,b as e,v as D,m as n,f as s,g as x,l as L,I as j,cu as O,_ as Y,ah as S,b1 as c,ao as p,F as $,dA as E,t as u}from"./main-BRN2JBrw.js";import{V as N}from"./VAvatar-C5ZVrAt4.js";import{_ as g}from"./_plugin-vue_export-helper-DlAUqK2U.js";import{a as m,V as w}from"./VRow-Cw23LpgL.js";import{V as k}from"./VTextField-B-SafXsh.js";import{_ as G}from"./AppTextField.vue_vue_type_script_setup_true_lang-Dprt9PnY.js";import{_ as q}from"./AppCardCode.vue_vue_type_style_index_0_lang-f1mNd_Lg.js";import"./autofocus-Db38RUhk.js";import"./VCounter-BXsWeC_3.js";import"./VField-CXZn0NIc.js";import"./vue3-perfect-scrollbar-h9R898_l.js";import"./VCard-_AzuU6xP.js";import"./VCardText-BORcZDbM.js";import"./VDivider-BJRlhygd.js";const H={class:"d-flex justify-space-between ma-4"},J=["textContent"],R=40,T=218,K=f({__name:"DemoSliderAppendAndPrepend",setup(b){const o=V(40),l=V(!1),r=z(()=>o.value<100?"primary":o.value<125?"success":o.value<140?"info":o.value<175?"warning":"error"),i=z(()=>`${60/o.value}s`),t=()=>{o.value>R&&(o.value-=1)},d=()=>{o.value<T&&(o.value+=1)};return(y,C)=>(v(),h($,null,[a("div",H,[a("div",null,[a("span",{class:"text-6xl font-weight-light",textContent:D(n(o))},null,8,J),C[2]||(C[2]=a("span",{class:"subheading font-weight-light me-1"},"BPM",-1)),e(O,null,{default:s(()=>[n(l)?(v(),x(N,{key:0,color:n(r),style:j({animationDuration:n(i)}),class:"mb-1 v-avatar--metronome",size:"12"},null,8,["color","style"])):L("",!0)]),_:1})]),a("div",null,[e(S,{color:n(r),icon:"",elevation:"0",onClick:C[0]||(C[0]=_=>l.value=!n(l))},{default:s(()=>[e(Y,{size:"large",icon:n(l)?"tabler-pause":"tabler-play"},null,8,["icon"])]),_:1},8,["color"])])]),e(p,{modelValue:n(o),"onUpdate:modelValue":C[1]||(C[1]=_=>c(o)?o.value=_:null),color:n(r),step:1,min:R,max:T,"track-color":"secondary"},{prepend:s(()=>[e(S,{size:"small",variant:"text",icon:"tabler-minus",color:n(r),onClick:t},null,8,["color"])]),append:s(()=>[e(S,{size:"small",variant:"text",icon:"tabler-plus",color:n(r),onClick:d},null,8,["color"])]),_:1},8,["modelValue","color"])],64))}}),Q=g(K,[["__scopeId","data-v-b105f5d8"]]),W={class:"d-flex justify-space-between"},X={class:"d-flex justify-space-between"},Z={class:"d-flex justify-space-between"},ee=f({__name:"DemoSliderAppendTextField",setup(b){const o=V(161),l=V(105),r=V(225);return(i,t)=>(v(),h($,null,[e(E,{style:j({background:`rgb(${n(o)}, ${n(l)}, ${n(r)})`}),height:"150px"},null,8,["style"]),e(w,{class:"mt-5"},{default:s(()=>[e(m,{cols:"12"},{default:s(()=>[a("div",W,[e(p,{modelValue:n(o),"onUpdate:modelValue":t[0]||(t[0]=d=>c(o)?o.value=d:null),max:255,step:1,"prepend-icon":"tabler-letter-r"},null,8,["modelValue"]),e(k,{modelValue:n(o),"onUpdate:modelValue":t[1]||(t[1]=d=>c(o)?o.value=d:null),type:"number",placeholder:"10",max:255,style:{"max-inline-size":"5rem"}},null,8,["modelValue"])])]),_:1}),e(m,{cols:"12"},{default:s(()=>[a("div",X,[e(p,{modelValue:n(l),"onUpdate:modelValue":t[2]||(t[2]=d=>c(l)?l.value=d:null),max:255,step:1,"prepend-icon":"tabler-letter-g"},null,8,["modelValue"]),e(k,{modelValue:n(l),"onUpdate:modelValue":t[3]||(t[3]=d=>c(l)?l.value=d:null),type:"number",placeholder:"20",max:255,style:{"max-inline-size":"5rem"}},null,8,["modelValue"])])]),_:1}),e(m,{cols:"12"},{default:s(()=>[a("div",Z,[e(p,{modelValue:n(r),"onUpdate:modelValue":t[4]||(t[4]=d=>c(r)?r.value=d:null),max:255,step:1,"prepend-icon":"tabler-letter-b"},null,8,["modelValue"]),e(k,{modelValue:n(r),"onUpdate:modelValue":t[5]||(t[5]=d=>c(r)?r.value=d:null),type:"number",placeholder:"30",max:255,style:{"max-inline-size":"5rem"}},null,8,["modelValue"])])]),_:1})]),_:1})],64))}}),le=f({__name:"DemoSliderVertical",setup(b){const o=V(10);return(l,r)=>(v(),x(p,{modelValue:n(o),"onUpdate:modelValue":r[0]||(r[0]=i=>c(o)?o.value=i:null),direction:"vertical"},null,8,["modelValue"]))}}),te=f({__name:"DemoSliderTicks",setup(b){const o=V(0),l=V(1),r={0:"Figs",1:"Lemon",2:"Pear",3:"Apple"};return(i,t)=>(v(),x(w,null,{default:s(()=>[e(m,{cols:"12"},{default:s(()=>[t[4]||(t[4]=a("div",{class:"text-caption"}," Show ticks when using slider ",-1)),e(p,{modelValue:n(o),"onUpdate:modelValue":t[0]||(t[0]=d=>c(o)?o.value=d:null),step:10,"show-ticks":""},null,8,["modelValue"])]),_:1}),e(m,{cols:"12"},{default:s(()=>[t[5]||(t[5]=a("div",{class:"text-caption"}," Always show ticks ",-1)),e(p,{modelValue:n(o),"onUpdate:modelValue":t[1]||(t[1]=d=>c(o)?o.value=d:null),step:10,"show-ticks":"always"},null,8,["modelValue"])]),_:1}),e(m,{cols:"12"},{default:s(()=>[t[6]||(t[6]=a("div",{class:"text-caption"}," Tick size ",-1)),e(p,{modelValue:n(o),"onUpdate:modelValue":t[2]||(t[2]=d=>c(o)?o.value=d:null),step:10,"show-ticks":"always","tick-size":"4"},null,8,["modelValue"])]),_:1}),e(m,{cols:"12"},{default:s(()=>[t[7]||(t[7]=a("div",{class:"text-caption"}," Tick labels ",-1)),e(p,{modelValue:n(l),"onUpdate:modelValue":t[3]||(t[3]=d=>c(l)?l.value=d:null),ticks:r,max:3,step:"1","show-ticks":"always","tick-size":"4"},null,8,["modelValue"])]),_:1})]),_:1}))}}),oe=f({__name:"DemoSliderThumb",setup(b){const o=["😭","😢","😔","🙁","😐","🙂","😊","😁","😄","😍"],l=V(45);return(r,i)=>(v(),x(w,null,{default:s(()=>[e(m,{cols:"12"},{default:s(()=>[i[4]||(i[4]=a("div",{class:"text-caption"}," Show thumb when using slider ",-1)),e(p,{modelValue:n(l),"onUpdate:modelValue":i[0]||(i[0]=t=>c(l)?l.value=t:null),"thumb-label":""},null,8,["modelValue"])]),_:1}),e(m,{cols:"12"},{default:s(()=>[i[5]||(i[5]=a("div",{class:"text-caption"}," Always show thumb label ",-1)),e(p,{modelValue:n(l),"onUpdate:modelValue":i[1]||(i[1]=t=>c(l)?l.value=t:null),"thumb-label":"always"},null,8,["modelValue"])]),_:1}),e(m,{cols:"12"},{default:s(()=>[i[6]||(i[6]=a("div",{class:"text-caption"}," Custom thumb size ",-1)),e(p,{modelValue:n(l),"onUpdate:modelValue":i[2]||(i[2]=t=>c(l)?l.value=t:null),"thumb-size":30,"thumb-label":"always"},null,8,["modelValue"])]),_:1}),e(m,{cols:"12"},{default:s(()=>[i[7]||(i[7]=a("div",{class:"text-caption"}," Custom thumb label ",-1)),e(p,{modelValue:n(l),"onUpdate:modelValue":i[3]||(i[3]=t=>c(l)?l.value=t:null),"thumb-label":"always"},{"thumb-label":s(({modelValue:t})=>[u(D(o[Math.min(Math.floor(t/10),9)]),1)]),_:1},8,["modelValue"])]),_:1})]),_:1}))}}),se={};function ae(b,o){return v(),x(p,{step:10,"show-ticks":"","thumb-size":18,"tick-size":3,"track-size":2})}const ne=g(se,[["render",ae]]),ie={class:"d-flex justify-space-between"},re=f({__name:"DemoSliderMinAndMax",setup(b){const o=V(-50),l=V(90),r=V(40);return(i,t)=>{const d=G;return v(),h("div",ie,[e(p,{modelValue:n(r),"onUpdate:modelValue":t[0]||(t[0]=y=>c(r)?r.value=y:null),max:n(l),min:n(o),step:1},null,8,["modelValue","max","min"]),e(d,{modelValue:n(r),"onUpdate:modelValue":t[1]||(t[1]=y=>c(r)?r.value=y:null),type:"number",placeholder:"10",style:{"max-inline-size":"5rem"}},null,8,["modelValue"])])}}}),de=f({__name:"DemoSliderValidation",setup(b){const o=V(30),l=[r=>r<=40||"Only 40 in stock"];return(r,i)=>(v(),x(p,{modelValue:n(o),"onUpdate:modelValue":i[0]||(i[0]=t=>c(o)?o.value=t:null),error:n(o)>40,rules:l,step:10,"thumb-label":"always","show-ticks":""},null,8,["modelValue","error"]))}}),ue=f({__name:"DemoSliderStep",setup(b){const o=V(0);return(l,r)=>(v(),x(p,{modelValue:n(o),"onUpdate:modelValue":r[0]||(r[0]=i=>c(o)?o.value=i:null),min:0,max:1,step:.2,"thumb-label":""},null,8,["modelValue"]))}}),me=f({__name:"DemoSliderIcons",setup(b){const o=V(0),l=V(0),r=V(10);return(i,t)=>(v(),x(w,null,{default:s(()=>[e(m,{cols:"12"},{default:s(()=>[e(p,{modelValue:n(o),"onUpdate:modelValue":t[0]||(t[0]=d=>c(o)?o.value=d:null),"prepend-icon":"tabler-volume"},null,8,["modelValue"])]),_:1}),e(m,{cols:"12"},{default:s(()=>[e(p,{modelValue:n(l),"onUpdate:modelValue":t[1]||(t[1]=d=>c(l)?l.value=d:null),"append-icon":"tabler-alarm"},null,8,["modelValue"])]),_:1}),e(m,{cols:"12"},{default:s(()=>[e(p,{modelValue:n(r),"onUpdate:modelValue":t[2]||(t[2]=d=>c(r)?r.value=d:null),"append-icon":"tabler-minus","prepend-icon":"tabler-plus"},null,8,["modelValue"])]),_:1})]),_:1}))}}),ce=f({__name:"DemoSliderColors",setup(b){const o=V(25),l=V(75),r=V(50);return(i,t)=>(v(),x(w,null,{default:s(()=>[e(m,{cols:"12"},{default:s(()=>[t[3]||(t[3]=a("div",{class:"text-caption"}," color ",-1)),e(p,{modelValue:n(o),"onUpdate:modelValue":t[0]||(t[0]=d=>c(o)?o.value=d:null),color:"error"},null,8,["modelValue"])]),_:1}),e(m,{cols:"12"},{default:s(()=>[t[4]||(t[4]=a("div",{class:"text-caption"}," track-color ",-1)),e(p,{modelValue:n(l),"onUpdate:modelValue":t[1]||(t[1]=d=>c(l)?l.value=d:null),"track-color":"error"},null,8,["modelValue"])]),_:1}),e(m,{cols:"12"},{default:s(()=>[t[5]||(t[5]=a("div",{class:"text-caption"}," thumb-color ",-1)),e(p,{modelValue:n(r),"onUpdate:modelValue":t[2]||(t[2]=d=>c(r)?r.value=d:null),"thumb-color":"error","thumb-label":"always"},null,8,["modelValue"])]),_:1})]),_:1}))}}),pe={};function Ve(b,o){return v(),x(w,null,{default:s(()=>[e(m,{cols:"12"},{default:s(()=>[o[0]||(o[0]=a("div",{class:"text-caption"}," Disabled ",-1)),e(p,{disabled:"",label:"Disabled","model-value":30})]),_:1}),e(m,{cols:"12"},{default:s(()=>[o[1]||(o[1]=a("div",{class:"text-caption"}," Readonly ",-1)),e(p,{readonly:"",label:"Readonly","model-value":30})]),_:1})]),_:1})}const ve=g(pe,[["render",Ve]]),be=f({__name:"DemoSliderBasic",setup(b){const o=V(30);return(l,r)=>(v(),x(w,null,{default:s(()=>[e(m,{cols:"12"},{default:s(()=>[e(p)]),_:1}),e(m,{cols:"12"},{default:s(()=>[e(p,{modelValue:n(o),"onUpdate:modelValue":r[0]||(r[0]=i=>c(o)?o.value=i:null)},null,8,["modelValue"])]),_:1})]),_:1}))}}),fe={ts:`<script lang="ts" setup>
const bpm = ref(40)
const min = 40
const max = 218
const isPlaying = ref(false)

const color = computed(() => {
  if (bpm.value < 100)
    return 'primary'
  if (bpm.value < 125)
    return 'success'
  if (bpm.value < 140)
    return 'info'
  if (bpm.value < 175)
    return 'warning'

  return 'error'
})

const animationDuration = computed(() => {
  return \`\${60 / bpm.value}s\`
})

const decrement = () => {
  if (bpm.value > min)
    bpm.value -= 1
}

const increment = () => {
  if (bpm.value < max)
    bpm.value += 1
}
<\/script>

<template>
  <div class="d-flex justify-space-between ma-4">
    <div>
      <span
        class="text-6xl font-weight-light"
        v-text="bpm"
      />
      <span class="subheading font-weight-light me-1">BPM</span>

      <VFadeTransition>
        <VAvatar
          v-if="isPlaying"
          :color="color"
          :style="{
            animationDuration,
          }"
          class="mb-1 v-avatar--metronome"
          size="12"
        />
      </VFadeTransition>
    </div>

    <div>
      <VBtn
        :color="color"
        icon
        elevation="0"
        @click="isPlaying = !isPlaying"
      >
        <VIcon
          size="large"
          :icon="isPlaying ? 'tabler-pause' : 'tabler-play'"
        />
      </VBtn>
    </div>
  </div>

  <VSlider
    v-model="bpm"
    :color="color"
    :step="1"
    :min="min"
    :max="max"
    track-color="secondary"
  >
    <template #prepend>
      <VBtn
        size="small"
        variant="text"
        icon="tabler-minus"
        :color="color"
        @click="decrement"
      />
    </template>

    <template #append>
      <VBtn
        size="small"
        variant="text"
        icon="tabler-plus"
        :color="color"
        @click="increment"
      />
    </template>
  </VSlider>
</template>

<style lang="scss" scoped>
  @keyframes metronome-example {
    from {
      transform: scale(0.5);
    }

    to {
      transform: scale(1);
    }
  }

  .v-avatar--metronome {
    animation-direction: alternate;
    animation-iteration-count: infinite;
    animation-name: metronome-example;
  }
</style>
`,js:`<script setup>
const bpm = ref(40)
const min = 40
const max = 218
const isPlaying = ref(false)

const color = computed(() => {
  if (bpm.value < 100)
    return 'primary'
  if (bpm.value < 125)
    return 'success'
  if (bpm.value < 140)
    return 'info'
  if (bpm.value < 175)
    return 'warning'
  
  return 'error'
})

const animationDuration = computed(() => {
  return \`\${ 60 / bpm.value }s\`
})

const decrement = () => {
  if (bpm.value > min)
    bpm.value -= 1
}

const increment = () => {
  if (bpm.value < max)
    bpm.value += 1
}
<\/script>

<template>
  <div class="d-flex justify-space-between ma-4">
    <div>
      <span
        class="text-6xl font-weight-light"
        v-text="bpm"
      />
      <span class="subheading font-weight-light me-1">BPM</span>

      <VFadeTransition>
        <VAvatar
          v-if="isPlaying"
          :color="color"
          :style="{
            animationDuration,
          }"
          class="mb-1 v-avatar--metronome"
          size="12"
        />
      </VFadeTransition>
    </div>

    <div>
      <VBtn
        :color="color"
        icon
        elevation="0"
        @click="isPlaying = !isPlaying"
      >
        <VIcon
          size="large"
          :icon="isPlaying ? 'tabler-pause' : 'tabler-play'"
        />
      </VBtn>
    </div>
  </div>

  <VSlider
    v-model="bpm"
    :color="color"
    :step="1"
    :min="min"
    :max="max"
    track-color="secondary"
  >
    <template #prepend>
      <VBtn
        size="small"
        variant="text"
        icon="tabler-minus"
        :color="color"
        @click="decrement"
      />
    </template>

    <template #append>
      <VBtn
        size="small"
        variant="text"
        icon="tabler-plus"
        :color="color"
        @click="increment"
      />
    </template>
  </VSlider>
</template>

<style lang="scss" scoped>
  @keyframes metronome-example {
    from {
      transform: scale(0.5);
    }

    to {
      transform: scale(1);
    }
  }

  .v-avatar--metronome {
    animation-direction: alternate;
    animation-iteration-count: infinite;
    animation-name: metronome-example;
  }
</style>
`},xe={ts:`<script lang="ts" setup>
const redColorValue = ref(161)
const greenColorValue = ref(105)
const blueColorValue = ref(225)
<\/script>

<template>
  <VResponsive
    :style="{ background: \`rgb(\${redColorValue}, \${greenColorValue}, \${blueColorValue})\` }"
    height="150px"
  />

  <VRow class="mt-5">
    <VCol cols="12">
      <!-- R -->
      <div class="d-flex justify-space-between">
        <VSlider
          v-model="redColorValue"
          :max="255"
          :step="1"
          prepend-icon="tabler-letter-r"
        />

        <VTextField
          v-model="redColorValue"
          type="number"
          placeholder="10"
          :max="255"
          style="max-inline-size: 5rem;"
        />
      </div>
    </VCol>

    <VCol cols="12">
      <!-- G -->
      <div class="d-flex justify-space-between">
        <VSlider
          v-model="greenColorValue"
          :max="255"
          :step="1"
          prepend-icon="tabler-letter-g"
        />

        <VTextField
          v-model="greenColorValue"
          type="number"
          placeholder="20"
          :max="255"
          style="max-inline-size: 5rem;"
        />
      </div>
    </VCol>

    <VCol cols="12">
      <!-- B -->
      <div class="d-flex justify-space-between">
        <VSlider
          v-model="blueColorValue"
          :max="255"
          :step="1"
          prepend-icon="tabler-letter-b"
        />
        <VTextField
          v-model="blueColorValue"
          type="number"
          placeholder="30"
          :max="255"
          style="max-inline-size: 5rem;"
        />
      </div>
    </VCol>
  </VRow>
</template>
`,js:`<script setup>
const redColorValue = ref(161)
const greenColorValue = ref(105)
const blueColorValue = ref(225)
<\/script>

<template>
  <VResponsive
    :style="{ background: \`rgb(\${redColorValue}, \${greenColorValue}, \${blueColorValue})\` }"
    height="150px"
  />

  <VRow class="mt-5">
    <VCol cols="12">
      <!-- R -->
      <div class="d-flex justify-space-between">
        <VSlider
          v-model="redColorValue"
          :max="255"
          :step="1"
          prepend-icon="tabler-letter-r"
        />

        <VTextField
          v-model="redColorValue"
          type="number"
          placeholder="10"
          :max="255"
          style="max-inline-size: 5rem;"
        />
      </div>
    </VCol>

    <VCol cols="12">
      <!-- G -->
      <div class="d-flex justify-space-between">
        <VSlider
          v-model="greenColorValue"
          :max="255"
          :step="1"
          prepend-icon="tabler-letter-g"
        />

        <VTextField
          v-model="greenColorValue"
          type="number"
          placeholder="20"
          :max="255"
          style="max-inline-size: 5rem;"
        />
      </div>
    </VCol>

    <VCol cols="12">
      <!-- B -->
      <div class="d-flex justify-space-between">
        <VSlider
          v-model="blueColorValue"
          :max="255"
          :step="1"
          prepend-icon="tabler-letter-b"
        />
        <VTextField
          v-model="blueColorValue"
          type="number"
          placeholder="30"
          :max="255"
          style="max-inline-size: 5rem;"
        />
      </div>
    </VCol>
  </VRow>
</template>
`},Ce={ts:`<script setup lang="ts">
const sliderValue = ref(30)
<\/script>

<template>
  <VRow>
    <VCol cols="12">
      <VSlider />
    </VCol>

    <VCol cols="12">
      <VSlider v-model="sliderValue" />
    </VCol>
  </VRow>
</template>
`,js:`<script setup>
const sliderValue = ref(30)
<\/script>

<template>
  <VRow>
    <VCol cols="12">
      <VSlider />
    </VCol>

    <VCol cols="12">
      <VSlider v-model="sliderValue" />
    </VCol>
  </VRow>
</template>
`},we={ts:`<script lang="ts" setup>
const sliderColorValue = ref(25)
const sliderTrackColorValue = ref(75)
const sliderThumbColorValue = ref(50)
<\/script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="text-caption">
        color
      </div>
      <VSlider
        v-model="sliderColorValue"
        color="error"
      />
    </VCol>

    <VCol cols="12">
      <div class="text-caption">
        track-color
      </div>
      <VSlider
        v-model="sliderTrackColorValue"
        track-color="error"
      />
    </VCol>

    <VCol cols="12">
      <div class="text-caption">
        thumb-color
      </div>
      <VSlider
        v-model="sliderThumbColorValue"
        thumb-color="error"
        thumb-label="always"
      />
    </VCol>
  </VRow>
</template>
`,js:`<script setup>
const sliderColorValue = ref(25)
const sliderTrackColorValue = ref(75)
const sliderThumbColorValue = ref(50)
<\/script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="text-caption">
        color
      </div>
      <VSlider
        v-model="sliderColorValue"
        color="error"
      />
    </VCol>

    <VCol cols="12">
      <div class="text-caption">
        track-color
      </div>
      <VSlider
        v-model="sliderTrackColorValue"
        track-color="error"
      />
    </VCol>

    <VCol cols="12">
      <div class="text-caption">
        thumb-color
      </div>
      <VSlider
        v-model="sliderThumbColorValue"
        thumb-color="error"
        thumb-label="always"
      />
    </VCol>
  </VRow>
</template>
`},ye={ts:`<template>
  <VRow>
    <VCol cols="12">
      <div class="text-caption">
        Disabled
      </div>
      <VSlider
        disabled
        label="Disabled"
        :model-value="30"
      />
    </VCol>

    <VCol cols="12">
      <div class="text-caption">
        Readonly
      </div>
      <VSlider
        readonly
        label="Readonly"
        :model-value="30"
      />
    </VCol>
  </VRow>
</template>
`,js:`<template>
  <VRow>
    <VCol cols="12">
      <div class="text-caption">
        Disabled
      </div>
      <VSlider
        disabled
        label="Disabled"
        :model-value="30"
      />
    </VCol>

    <VCol cols="12">
      <div class="text-caption">
        Readonly
      </div>
      <VSlider
        readonly
        label="Readonly"
        :model-value="30"
      />
    </VCol>
  </VRow>
</template>
`},_e={ts:`<script lang="ts" setup>
const mediaSlider = ref(0)
const alarmSlider = ref(0)
const zoomInOut = ref(10)
<\/script>

<template>
  <VRow>
    <VCol cols="12">
      <VSlider
        v-model="mediaSlider"
        prepend-icon="tabler-volume"
      />
    </VCol>

    <VCol cols="12">
      <VSlider
        v-model="alarmSlider"
        append-icon="tabler-alarm"
      />
    </VCol>

    <VCol cols="12">
      <VSlider
        v-model="zoomInOut"
        append-icon="tabler-minus"
        prepend-icon="tabler-plus"
      />
    </VCol>
  </VRow>
</template>
`,js:`<script setup>
const mediaSlider = ref(0)
const alarmSlider = ref(0)
const zoomInOut = ref(10)
<\/script>

<template>
  <VRow>
    <VCol cols="12">
      <VSlider
        v-model="mediaSlider"
        prepend-icon="tabler-volume"
      />
    </VCol>

    <VCol cols="12">
      <VSlider
        v-model="alarmSlider"
        append-icon="tabler-alarm"
      />
    </VCol>

    <VCol cols="12">
      <VSlider
        v-model="zoomInOut"
        append-icon="tabler-minus"
        prepend-icon="tabler-plus"
      />
    </VCol>
  </VRow>
</template>
`},Se={ts:`<script lang="ts" setup>
const min = ref(-50)
const max = ref(90)
const slider = ref(40)
<\/script>

<template>
  <div class="d-flex justify-space-between">
    <VSlider
      v-model="slider"
      :max="max"
      :min="min"
      :step="1"
    />

    <AppTextField
      v-model="slider"
      type="number"
      placeholder="10"
      style="max-inline-size: 5rem;"
    />
  </div>
</template>
`,js:`<script setup>
const min = ref(-50)
const max = ref(90)
const slider = ref(40)
<\/script>

<template>
  <div class="d-flex justify-space-between">
    <VSlider
      v-model="slider"
      :max="max"
      :min="min"
      :step="1"
    />

    <AppTextField
      v-model="slider"
      type="number"
      placeholder="10"
      style="max-inline-size: 5rem;"
    />
  </div>
</template>
`},ke={ts:`<template>
  <VSlider
    :step="10"
    show-ticks
    :thumb-size="18"
    :tick-size="3"
    :track-size="2"
  />
</template>
`,js:`<template>
  <VSlider
    :step="10"
    show-ticks
    :thumb-size="18"
    :tick-size="3"
    :track-size="2"
  />
</template>
`},he={ts:`<script lang="ts" setup>
const value = ref(0)
<\/script>

<template>
  <VSlider
    v-model="value"
    :min="0"
    :max="1"
    :step="0.2"
    thumb-label
  />
</template>
`,js:`<script setup>
const value = ref(0)
<\/script>

<template>
  <VSlider
    v-model="value"
    :min="0"
    :max="1"
    :step="0.2"
    thumb-label
  />
</template>
`},ge={ts:`<script lang="ts" setup>
const satisfactionEmojis = ['😭', '😢', '😔', '🙁', '😐', '🙂', '😊', '😁', '😄', '😍']
const slider = ref(45)
<\/script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="text-caption">
        Show thumb when using slider
      </div>
      <VSlider
        v-model="slider"
        thumb-label
      />
    </VCol>

    <VCol cols="12">
      <div class="text-caption">
        Always show thumb label
      </div>
      <VSlider
        v-model="slider"
        thumb-label="always"
      />
    </VCol>

    <VCol cols="12">
      <div class="text-caption">
        Custom thumb size
      </div>
      <VSlider
        v-model="slider"
        :thumb-size="30"
        thumb-label="always"
      />
    </VCol>

    <VCol cols="12">
      <div class="text-caption">
        Custom thumb label
      </div>
      <VSlider
        v-model="slider"
        thumb-label="always"
      >
        <template #thumb-label="{ modelValue }">
          {{ satisfactionEmojis[Math.min(Math.floor(modelValue / 10), 9)] }}
        </template>
      </VSlider>
    </VCol>
  </VRow>
</template>
`,js:`<script setup>
const satisfactionEmojis = [
  '😭',
  '😢',
  '😔',
  '🙁',
  '😐',
  '🙂',
  '😊',
  '😁',
  '😄',
  '😍',
]

const slider = ref(45)
<\/script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="text-caption">
        Show thumb when using slider
      </div>
      <VSlider
        v-model="slider"
        thumb-label
      />
    </VCol>

    <VCol cols="12">
      <div class="text-caption">
        Always show thumb label
      </div>
      <VSlider
        v-model="slider"
        thumb-label="always"
      />
    </VCol>

    <VCol cols="12">
      <div class="text-caption">
        Custom thumb size
      </div>
      <VSlider
        v-model="slider"
        :thumb-size="30"
        thumb-label="always"
      />
    </VCol>

    <VCol cols="12">
      <div class="text-caption">
        Custom thumb label
      </div>
      <VSlider
        v-model="slider"
        thumb-label="always"
      >
        <template #thumb-label="{ modelValue }">
          {{ satisfactionEmojis[Math.min(Math.floor(modelValue / 10), 9)] }}
        </template>
      </VSlider>
    </VCol>
  </VRow>
</template>
`},ze={ts:`<script lang="ts" setup>
const value = ref(0)
const fruits = ref(1)
const ticksLabels = { 0: 'Figs', 1: 'Lemon', 2: 'Pear', 3: 'Apple' }
<\/script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="text-caption">
        Show ticks when using slider
      </div>
      <VSlider
        v-model="value"
        :step="10"
        show-ticks
      />
    </VCol>

    <VCol cols="12">
      <div class="text-caption">
        Always show ticks
      </div>
      <VSlider
        v-model="value"
        :step="10"
        show-ticks="always"
      />
    </VCol>

    <VCol cols="12">
      <div class="text-caption">
        Tick size
      </div>
      <VSlider
        v-model="value"
        :step="10"
        show-ticks="always"
        tick-size="4"
      />
    </VCol>

    <VCol cols="12">
      <div class="text-caption">
        Tick labels
      </div>
      <VSlider
        v-model="fruits"
        :ticks="ticksLabels"
        :max="3"
        step="1"
        show-ticks="always"
        tick-size="4"
      />
    </VCol>
  </VRow>
</template>
`,js:`<script setup>
const value = ref(0)
const fruits = ref(1)

const ticksLabels = {
  0: 'Figs',
  1: 'Lemon',
  2: 'Pear',
  3: 'Apple',
}
<\/script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="text-caption">
        Show ticks when using slider
      </div>
      <VSlider
        v-model="value"
        :step="10"
        show-ticks
      />
    </VCol>

    <VCol cols="12">
      <div class="text-caption">
        Always show ticks
      </div>
      <VSlider
        v-model="value"
        :step="10"
        show-ticks="always"
      />
    </VCol>

    <VCol cols="12">
      <div class="text-caption">
        Tick size
      </div>
      <VSlider
        v-model="value"
        :step="10"
        show-ticks="always"
        tick-size="4"
      />
    </VCol>

    <VCol cols="12">
      <div class="text-caption">
        Tick labels
      </div>
      <VSlider
        v-model="fruits"
        :ticks="ticksLabels"
        :max="3"
        step="1"
        show-ticks="always"
        tick-size="4"
      />
    </VCol>
  </VRow>
</template>
`},Re={ts:`<script lang="ts" setup>
const value = ref(30)
const rules = [(v: number) => v <= 40 || 'Only 40 in stock']
<\/script>

<template>
  <VSlider
    v-model="value"
    :error="value > 40"
    :rules="rules"
    :step="10"
    thumb-label="always"
    show-ticks
  />
</template>
`,js:`<script setup>
const value = ref(30)
const rules = [v => v <= 40 || 'Only 40 in stock']
<\/script>

<template>
  <VSlider
    v-model="value"
    :error="value > 40"
    :rules="rules"
    :step="10"
    thumb-label="always"
    show-ticks
  />
</template>
`},Te={ts:`<script lang="ts" setup>
const value = ref(10)
<\/script>

<template>
  <VSlider
    v-model="value"
    direction="vertical"
  />
</template>
`,js:`<script setup>
const value = ref(10)
<\/script>

<template>
  <VSlider
    v-model="value"
    direction="vertical"
  />
</template>
`},Ne=f({__name:"slider",setup(b){return(o,l)=>{const r=be,i=q,t=ve,d=ce,y=me,C=ue,_=de,A=re,U=ne,F=oe,B=te,P=le,M=ee,I=Q;return v(),x(w,{class:"match-height"},{default:s(()=>[e(m,{cols:"12",md:"6"},{default:s(()=>[e(i,{title:"Basic",code:n(Ce)},{default:s(()=>[l[0]||(l[0]=a("p",null,[u("The "),a("code",null,"v-slider"),u(" component is a better visualization of the number input.")],-1)),e(r)]),_:1},8,["code"])]),_:1}),e(m,{cols:"12",md:"6"},{default:s(()=>[e(i,{title:"Disabled and Readonly",code:n(ye)},{default:s(()=>[l[1]||(l[1]=a("p",null,[u("You cannot interact with "),a("code",null,"disabled"),u(" and "),a("code",null,"readonly"),u(" sliders.")],-1)),e(t)]),_:1},8,["code"])]),_:1}),e(m,{cols:"12",md:"6"},{default:s(()=>[e(i,{title:"Colors",code:n(we)},{default:s(()=>[l[2]||(l[2]=a("p",null,[u("You can set the colors of the slider using the props "),a("code",null,"color"),u(", "),a("code",null,"track-color"),u(" and "),a("code",null,"thumb-color"),u(".")],-1)),e(d)]),_:1},8,["code"])]),_:1}),e(m,{cols:"12",md:"6"},{default:s(()=>[e(i,{title:"Icons",code:n(_e)},{default:s(()=>[l[3]||(l[3]=a("p",null,[u("You can add icons to the slider with the "),a("code",null,"append-icon"),u(" and "),a("code",null,"prepend-icon"),u(" props.")],-1)),e(y)]),_:1},8,["code"])]),_:1}),e(m,{cols:"12",md:"6"},{default:s(()=>[e(i,{title:"Step",code:n(he)},{default:s(()=>[l[4]||(l[4]=a("p",null,[u("Using the "),a("code",null,"step"),u(" prop you can control the precision of the slider, and how much it should move each step.")],-1)),e(C)]),_:1},8,["code"])]),_:1}),e(m,{cols:"12",md:"6"},{default:s(()=>[e(i,{title:"Validation",code:n(Re)},{default:s(()=>[l[5]||(l[5]=a("p",null,[u("Vuetify includes simple validation through the "),a("code",null,"rules"),u(" prop.")],-1)),e(_)]),_:1},8,["code"])]),_:1}),e(m,{cols:"12",md:"6"},{default:s(()=>[e(i,{title:"Min and Max",code:n(Se)},{default:s(()=>[l[6]||(l[6]=a("p",null,[u("You can set "),a("code",null,"min"),u(" and "),a("code",null,"max"),u(" values of sliders.")],-1)),e(A)]),_:1},8,["code"])]),_:1}),e(m,{cols:"12",md:"6"},{default:s(()=>[e(i,{title:"Size",code:n(ke)},{default:s(()=>[l[7]||(l[7]=a("p",null,[u("Use "),a("code",null,"thumb-size"),u(", "),a("code",null,"tick-size"),u(", and "),a("code",null,"track-size"),u(" prop to increase and decrease the size of thumb, tick and track. ")],-1)),e(U)]),_:1},8,["code"])]),_:1}),e(m,{cols:"12",md:"6"},{default:s(()=>[e(i,{title:"Thumb",code:n(ge)},{default:s(()=>[l[8]||(l[8]=a("p",null,[u("You can display a thumb label while sliding or always with the "),a("code",null,"thumb-label"),u(" prop.")],-1)),e(F)]),_:1},8,["code"])]),_:1}),e(m,{cols:"12",md:"6"},{default:s(()=>[e(i,{title:"Ticks",code:n(ze)},{default:s(()=>[l[9]||(l[9]=a("p",null,"Tick marks represent predetermined values to which the user can move the slider.",-1)),e(B)]),_:1},8,["code"])]),_:1}),e(m,{cols:"12",md:"6"},{default:s(()=>[e(i,{title:"Vertical",code:n(Te)},{default:s(()=>[l[10]||(l[10]=a("p",null,[u(" You can use the "),a("code",null,"vertical"),u(" prop to switch sliders to a vertical orientation. ")],-1)),e(P)]),_:1},8,["code"])]),_:1}),e(m,{cols:"12",md:"6"},{default:s(()=>[e(i,{title:"Append text field",code:n(xe)},{default:s(()=>[l[11]||(l[11]=a("p",null,[u("Sliders can be combined with other components in its "),a("code",null,"append"),u(" slot, such as "),a("code",null,"v-text-field"),u(", to add additional functionality to the component.")],-1)),e(M)]),_:1},8,["code"])]),_:1}),e(m,{cols:"12",md:"6"},{default:s(()=>[e(i,{title:"Append and prepend",code:n(fe)},{default:s(()=>[l[12]||(l[12]=a("p",null,[u("Use slots such as "),a("code",null,"append"),u(" and "),a("code",null,"prepend"),u(" to easily customize the "),a("code",null,"v-slider"),u(" to fit any situation.")],-1)),e(I)]),_:1},8,["code"])]),_:1})]),_:1})}}});export{Ne as default};
