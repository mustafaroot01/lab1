import{V as I,a as g}from"./VRow-Cw23LpgL.js";import{V as y}from"./VCheckbox-DR1Se1UM.js";import{d as b,g as u,l as k,o as l,f as n,c as x,i as R,q as f,b as c,e as p,x as w,b2 as v,z as W,t as V,v as C,F as $,r as h,m as d,b1 as _,_ as j,V as B}from"./main-BRN2JBrw.js";import{_ as z}from"./_plugin-vue_export-helper-DlAUqK2U.js";import{_ as U}from"./CustomRadiosWithImage-CabT_M0N.js";import{_ as G}from"./CustomRadiosWithIcon-DR0nE5qd.js";import{_ as F}from"./AppCardCode.vue_vue_type_style_index_0_lang-f1mNd_Lg.js";import{_ as E}from"./CustomRadios-CTTdwSVF.js";import"./VCheckboxBtn-BmEGxGs1.js";import"./VSelectionControl-C_9VPf01.js";import"./VRadioGroup-njJP9r64.js";import"./vue3-perfect-scrollbar-h9R898_l.js";import"./VCard-_AzuU6xP.js";import"./VAvatar-C5ZVrAt4.js";import"./VCardText-BORcZDbM.js";import"./VDivider-BJRlhygd.js";const P=["src"],N=b({__name:"CustomCheckboxesWithImage",props:{selectedCheckbox:{},checkboxContent:{},gridColumn:{}},emits:["update:selectedCheckbox"],setup(r,{emit:a}){const e=r,m=a,o=t=>{typeof t!="boolean"&&t!==null&&m("update:selectedCheckbox",t)};return(t,i)=>e.checkboxContent&&e.selectedCheckbox?(l(),u(I,{key:0,class:"custom-input-wrapper"},{default:n(()=>[(l(!0),x($,null,R(e.checkboxContent,s=>(l(),u(g,f({key:s.value},{ref_for:!0},r.gridColumn),{default:n(()=>[c(v,{class:w(["custom-input custom-checkbox rounded cursor-pointer w-100",e.selectedCheckbox.includes(s.value)?"active":""])},{default:n(()=>[p("div",null,[c(y,{id:`custom-checkbox-with-img-${s.value}`,"model-value":e.selectedCheckbox,value:s.value,"onUpdate:modelValue":o},null,8,["id","model-value","value"])]),p("img",{src:s.bgImage,alt:"bg-img",class:"custom-checkbox-image"},null,8,P)]),_:2},1032,["class"]),s.label||t.$slots.label?(l(),u(v,{key:0,for:`custom-checkbox-with-img-${s.value}`,class:"cursor-pointer"},{default:n(()=>[W(t.$slots,"label",{label:s.label},()=>[V(C(s.label),1)],!0)]),_:2},1032,["for"])):k("",!0)]),_:2},1040))),128))]),_:3})):k("",!0)}}),T=z(N,[["__scopeId","data-v-d7e1d38d"]]),L="/build/assets/custom-checkbox-img-1-CN62rwL8.png",J="/build/assets/custom-checkbox-img-2-CESZ7JlS.png",O="/build/assets/custom-checkbox-img-3-CFGEoD3K.png",A=b({__name:"DemoCustomInputCustomCheckboxesWithImage",setup(r){const a=[{bgImage:L,value:"basic"},{bgImage:J,value:"premium"},{bgImage:O,value:"enterprise"}],e=h(["basic"]);return(m,o)=>{const t=T;return l(),u(t,{"selected-checkbox":d(e),"onUpdate:selectedCheckbox":o[0]||(o[0]=i=>_(e)?e.value=i:null),"checkbox-content":a,"grid-column":{sm:"4",cols:"12"}},null,8,["selected-checkbox"])}}}),K="/build/assets/custom-radio-img-1-YrBKH0Sm.png",Y="/build/assets/custom-radio-img-2-Ph1YXgv1.png",q="/build/assets/custom-radio-img-3-Bg8gANbN.png",H=b({__name:"DemoCustomInputCustomRadiosWithImage",setup(r){const a=[{bgImage:K,value:"basic"},{bgImage:Y,value:"premium"},{bgImage:q,value:"enterprise"}],e=h("basic");return(m,o)=>{const t=U;return l(),u(t,{"selected-radio":d(e),"onUpdate:selectedRadio":o[0]||(o[0]=i=>_(e)?e.value=i:null),"radio-content":a,"grid-column":{sm:"4",cols:"12"}},null,8,["selected-radio"])}}}),X={class:"d-flex flex-column align-center text-center gap-2"},Z={class:"cr-title text-base"},M={class:"text-sm clamp-text mb-0"},Q=b({__name:"CustomCheckboxesWithIcon",props:{selectedCheckbox:{},checkboxContent:{},gridColumn:{}},emits:["update:selectedCheckbox"],setup(r,{emit:a}){const e=r,m=a,o=t=>{typeof t!="boolean"&&t!==null&&m("update:selectedCheckbox",t)};return(t,i)=>e.checkboxContent&&e.selectedCheckbox?(l(),u(I,{key:0,class:"custom-input-wrapper"},{default:n(()=>[(l(!0),x($,null,R(e.checkboxContent,s=>(l(),u(g,f({key:s.title},{ref_for:!0},r.gridColumn),{default:n(()=>[c(v,{class:w(["custom-input custom-checkbox-icon rounded cursor-pointer",e.selectedCheckbox.includes(s.value)?"active":""])},{default:n(()=>[W(t.$slots,"default",{item:s},()=>[p("div",X,[c(j,f({ref_for:!0},s.icon,{class:"text-high-emphasis"}),null,16),p("h6",Z,C(s.title),1),p("p",M,C(s.desc),1)])],!0),p("div",null,[c(y,{"model-value":e.selectedCheckbox,value:s.value,"onUpdate:modelValue":o},null,8,["model-value","value"])])]),_:2},1032,["class"])]),_:2},1040))),128))]),_:3})):k("",!0)}}),ee=z(Q,[["__scopeId","data-v-906b1216"]]),te=b({__name:"DemoCustomInputCustomCheckboxesWithIcon",setup(r){const a=[{title:"Backup",desc:"Backup every file from your project.",value:"backup",icon:{icon:"tabler-server-2",size:"28"}},{title:"Encrypt",desc:"Translate your data to encrypted text.",value:"encrypt",icon:{icon:"tabler-ban",size:"28"}},{title:"Site Lock",desc:"Security tool to protect your website.",value:"site-lock",icon:{icon:"tabler-lock",size:"28"}}],e=h(["backup"]);return(m,o)=>{const t=ee;return l(),u(t,{"selected-checkbox":d(e),"onUpdate:selectedCheckbox":o[0]||(o[0]=i=>_(e)?e.value=i:null),"checkbox-content":a,"grid-column":{sm:"4",cols:"12"}},null,8,["selected-checkbox"])}}}),oe=b({__name:"DemoCustomInputCustomRadiosWithIcon",setup(r){const a=[{title:"Starter",desc:"For freelancers who work with multiple clients",value:"starter",icon:{icon:"tabler-rocket",size:"28"}},{title:"Personal",desc:"Join our talented community of talented digital agencies",value:"personal",icon:{icon:"tabler-star",size:"28"}},{title:"Enterprise",desc:"Team plan for free upto 15 seats",value:"enterprise",icon:{icon:"tabler-crown",size:"28"}}],e=h("starter");return(m,o)=>{const t=G;return l(),u(t,{"selected-radio":d(e),"onUpdate:selectedRadio":o[0]||(o[0]=i=>_(e)?e.value=i:null),"radio-content":a,"grid-column":{sm:"4",cols:"12"}},null,8,["selected-radio"])}}}),se={class:"flex-grow-1"},ce={class:"d-flex align-center mb-2"},ne={class:"cr-title text-base"},ae={key:0,class:"text-disabled text-body-2"},le={class:"text-sm mb-0"},ie=b({__name:"CustomCheckboxes",props:{selectedCheckbox:{},checkboxContent:{},gridColumn:{}},emits:["update:selectedCheckbox"],setup(r,{emit:a}){const e=r,m=a,o=t=>{typeof t!="boolean"&&t!==null&&m("update:selectedCheckbox",t)};return(t,i)=>e.checkboxContent&&e.selectedCheckbox?(l(),u(I,{key:0,class:"custom-input-wrapper"},{default:n(()=>[(l(!0),x($,null,R(e.checkboxContent,s=>(l(),u(g,f({key:s.title},{ref_for:!0},r.gridColumn),{default:n(()=>[c(v,{class:w(["custom-input custom-checkbox rounded cursor-pointer",e.selectedCheckbox.includes(s.value)?"active":""])},{default:n(()=>[p("div",null,[c(y,{"model-value":e.selectedCheckbox,value:s.value,"onUpdate:modelValue":o},null,8,["model-value","value"])]),W(t.$slots,"default",{item:s},()=>[p("div",se,[p("div",ce,[p("h6",ne,C(s.title),1),c(B),s.subtitle?(l(),x("span",ae,C(s.subtitle),1)):k("",!0)]),p("p",le,C(s.desc),1)])],!0)]),_:2},1032,["class"])]),_:2},1040))),128))]),_:3})):k("",!0)}}),re=z(ie,[["__scopeId","data-v-030e20ac"]]),ue=b({__name:"DemoCustomInputCustomCheckboxes",setup(r){const a=[{title:"Discount",subtitle:"20%",desc:"Wow! Get 20% off on your next purchase!",value:"discount"},{title:"Updates",subtitle:"Free",desc:"Get Updates regarding related products.",value:"updates"}],e=h(["discount"]);return(m,o)=>{const t=re;return l(),u(t,{"selected-checkbox":d(e),"onUpdate:selectedCheckbox":o[0]||(o[0]=i=>_(e)?e.value=i:null),"checkbox-content":a,"grid-column":{sm:"6",cols:"12"}},null,8,["selected-checkbox"])}}}),me=b({__name:"DemoCustomInputCustomRadios",setup(r){const a=[{title:"Basic",subtitle:"Free",desc:"Get 1 project with 1 team member.",value:"basic"},{title:"Premium",subtitle:"$45.80",value:"premium",desc:"Get 5 projects with 5 team members."}],e=h("basic");return(m,o)=>{const t=E;return l(),u(t,{"selected-radio":d(e),"onUpdate:selectedRadio":o[0]||(o[0]=i=>_(e)?e.value=i:null),"radio-content":a,"grid-column":{sm:"6",cols:"12"}},null,8,["selected-radio"])}}}),de={ts:`<script setup lang="ts">
import type { CustomInputContent } from '@core/types'

const checkboxContent: CustomInputContent[] = [
  {
    title: 'Discount',
    subtitle: '20%',
    desc: 'Wow! Get 20% off on your next purchase!',
    value: 'discount',
  },
  {
    title: 'Updates',
    subtitle: 'Free',
    desc: 'Get Updates regarding related products.',
    value: 'updates',
  },
]

const selectedCheckbox = ref(['discount'])
<\/script>

<template>
  <CustomCheckboxes
    v-model:selected-checkbox="selectedCheckbox"
    :checkbox-content="checkboxContent"
    :grid-column="{ sm: '6', cols: '12' }"
  />
</template>
`,js:`<script setup>
const checkboxContent = [
  {
    title: 'Discount',
    subtitle: '20%',
    desc: 'Wow! Get 20% off on your next purchase!',
    value: 'discount',
  },
  {
    title: 'Updates',
    subtitle: 'Free',
    desc: 'Get Updates regarding related products.',
    value: 'updates',
  },
]

const selectedCheckbox = ref(['discount'])
<\/script>

<template>
  <CustomCheckboxes
    v-model:selected-checkbox="selectedCheckbox"
    :checkbox-content="checkboxContent"
    :grid-column="{ sm: '6', cols: '12' }"
  />
</template>
`},pe={ts:`<script setup lang="ts">
import type { CustomInputContent } from '@core/types'

const checkboxContent: CustomInputContent[] = [
  {
    title: 'Backup',
    desc: 'Backup every file from your project.',
    value: 'backup',
    icon: { icon: 'tabler-server-2', size: '28' },
  },
  {
    title: 'Encrypt',
    desc: 'Translate your data to encrypted text.',
    value: 'encrypt',
    icon: { icon: 'tabler-ban', size: '28' },
  },
  {
    title: 'Site Lock',
    desc: 'Security tool to protect your website.',
    value: 'site-lock',
    icon: { icon: 'tabler-lock', size: '28' },
  },
]

const selectedCheckbox = ref(['backup'])
<\/script>

<template>
  <CustomCheckboxesWithIcon
    v-model:selected-checkbox="selectedCheckbox"
    :checkbox-content="checkboxContent"
    :grid-column="{ sm: '4', cols: '12' }"
  />
</template>
`,js:`<script setup>
const checkboxContent = [
  {
    title: 'Backup',
    desc: 'Backup every file from your project.',
    value: 'backup',
    icon: {
      icon: 'tabler-server-2',
      size: '28',
    },
  },
  {
    title: 'Encrypt',
    desc: 'Translate your data to encrypted text.',
    value: 'encrypt',
    icon: {
      icon: 'tabler-ban',
      size: '28',
    },
  },
  {
    title: 'Site Lock',
    desc: 'Security tool to protect your website.',
    value: 'site-lock',
    icon: {
      icon: 'tabler-lock',
      size: '28',
    },
  },
]

const selectedCheckbox = ref(['backup'])
<\/script>

<template>
  <CustomCheckboxesWithIcon
    v-model:selected-checkbox="selectedCheckbox"
    :checkbox-content="checkboxContent"
    :grid-column="{ sm: '4', cols: '12' }"
  />
</template>
`},be={ts:`<script setup lang="ts">
import bg1 from '@images/pages/custom-checkbox-img-1.png'
import bg2 from '@images/pages/custom-checkbox-img-2.png'
import bg3 from '@images/pages/custom-checkbox-img-3.png'

const checkboxContent: { bgImage: string; value: string }[] = [
  {
    bgImage: bg1,
    value: 'basic',
  },
  {
    bgImage: bg2,
    value: 'premium',
  },
  {
    bgImage: bg3,
    value: 'enterprise',
  },
]

const selectedCheckbox = ref(['basic'])
<\/script>

<template>
  <CustomCheckboxesWithImage
    v-model:selected-checkbox="selectedCheckbox"
    :checkbox-content="checkboxContent"
    :grid-column="{ sm: '4', cols: '12' }"
  />
</template>
`,js:`<script setup>
import bg1 from '@images/pages/custom-checkbox-img-1.png'
import bg2 from '@images/pages/custom-checkbox-img-2.png'
import bg3 from '@images/pages/custom-checkbox-img-3.png'

const checkboxContent = [
  {
    bgImage: bg1,
    value: 'basic',
  },
  {
    bgImage: bg2,
    value: 'premium',
  },
  {
    bgImage: bg3,
    value: 'enterprise',
  },
]

const selectedCheckbox = ref(['basic'])
<\/script>

<template>
  <CustomCheckboxesWithImage
    v-model:selected-checkbox="selectedCheckbox"
    :checkbox-content="checkboxContent"
    :grid-column="{ sm: '4', cols: '12' }"
  />
</template>
`},ge={ts:`<script setup lang="ts">
import type { CustomInputContent } from '@core/types'

const radioContent: CustomInputContent[] = [
  {
    title: 'Basic',
    subtitle: 'Free',
    desc: 'Get 1 project with 1 team member.',
    value: 'basic',
  },
  {
    title: 'Premium',
    subtitle: '$45.80',
    value: 'premium',
    desc: 'Get 5 projects with 5 team members.',
  },
]

const selectedRadio = ref('basic')
<\/script>

<template>
  <CustomRadios
    v-model:selected-radio="selectedRadio"
    :radio-content="radioContent"
    :grid-column="{ sm: '6', cols: '12' }"
  />
</template>
`,js:`<script setup>
const radioContent = [
  {
    title: 'Basic',
    subtitle: 'Free',
    desc: 'Get 1 project with 1 team member.',
    value: 'basic',
  },
  {
    title: 'Premium',
    subtitle: '$45.80',
    value: 'premium',
    desc: 'Get 5 projects with 5 team members.',
  },
]

const selectedRadio = ref('basic')
<\/script>

<template>
  <CustomRadios
    v-model:selected-radio="selectedRadio"
    :radio-content="radioContent"
    :grid-column="{ sm: '6', cols: '12' }"
  />
</template>
`},Ce={ts:`<script setup lang="ts">
import type { CustomInputContent } from '@core/types'

const radioContent: CustomInputContent[] = [
  {
    title: 'Starter',
    desc: 'For freelancers who work with multiple clients',
    value: 'starter',
    icon: { icon: 'tabler-rocket', size: '28' },
  },
  {
    title: 'Personal',
    desc: 'Join our talented community of talented digital agencies',
    value: 'personal',
    icon: { icon: 'tabler-star', size: '28' },
  },
  {
    title: 'Enterprise',
    desc: 'Team plan for free upto 15 seats',
    value: 'enterprise',
    icon: { icon: 'tabler-crown', size: '28' },
  },
]

const selectedRadio = ref('starter')
<\/script>

<template>
  <CustomRadiosWithIcon
    v-model:selected-radio="selectedRadio"
    :radio-content="radioContent"
    :grid-column="{ sm: '4', cols: '12' }"
  />
</template>
`,js:`<script setup>
const radioContent = [
  {
    title: 'Starter',
    desc: 'For freelancers who work with multiple clients',
    value: 'starter',
    icon: {
      icon: 'tabler-rocket',
      size: '28',
    },
  },
  {
    title: 'Personal',
    desc: 'Join our talented community of talented digital agencies',
    value: 'personal',
    icon: {
      icon: 'tabler-star',
      size: '28',
    },
  },
  {
    title: 'Enterprise',
    desc: 'Team plan for free upto 15 seats',
    value: 'enterprise',
    icon: {
      icon: 'tabler-crown',
      size: '28',
    },
  },
]

const selectedRadio = ref('starter')
<\/script>

<template>
  <CustomRadiosWithIcon
    v-model:selected-radio="selectedRadio"
    :radio-content="radioContent"
    :grid-column="{ sm: '4', cols: '12' }"
  />
</template>
`},he={ts:`<script setup lang="ts">
import bg1 from '@images/pages/custom-radio-img-1.png'
import bg2 from '@images/pages/custom-radio-img-2.png'
import bg3 from '@images/pages/custom-radio-img-3.png'

const radioContent: { bgImage: string; value: string }[] = [
  {
    bgImage: bg1,
    value: 'basic',
  },
  {
    bgImage: bg2,
    value: 'premium',
  },
  {
    bgImage: bg3,
    value: 'enterprise',
  },
]

const selectedRadio = ref('basic')
<\/script>

<template>
  <CustomRadiosWithImage
    v-model:selected-radio="selectedRadio"
    :radio-content="radioContent"
    :grid-column="{ sm: '4', cols: '12' }"
  />
</template>
`,js:`<script setup>
import bg1 from '@images/pages/custom-radio-img-1.png'
import bg2 from '@images/pages/custom-radio-img-2.png'
import bg3 from '@images/pages/custom-radio-img-3.png'

const radioContent = [
  {
    bgImage: bg1,
    value: 'basic',
  },
  {
    bgImage: bg2,
    value: 'premium',
  },
  {
    bgImage: bg3,
    value: 'enterprise',
  },
]

const selectedRadio = ref('basic')
<\/script>

<template>
  <CustomRadiosWithImage
    v-model:selected-radio="selectedRadio"
    :radio-content="radioContent"
    :grid-column="{ sm: '4', cols: '12' }"
  />
</template>
`},Be=b({__name:"custom-input",setup(r){return(a,e)=>{const m=me,o=F,t=ue,i=oe,s=te,S=H,D=A;return l(),u(I,null,{default:n(()=>[c(g,{cols:"12",md:"6"},{default:n(()=>[c(o,{title:"Custom Radios",code:d(ge)},{default:n(()=>[c(m)]),_:1},8,["code"])]),_:1}),c(g,{cols:"12",md:"6"},{default:n(()=>[c(o,{title:"Custom Checkboxes",code:d(de)},{default:n(()=>[c(t)]),_:1},8,["code"])]),_:1}),c(g,{cols:"12",md:"6"},{default:n(()=>[c(o,{title:"Custom Radios With Icon",code:d(Ce)},{default:n(()=>[c(i)]),_:1},8,["code"])]),_:1}),c(g,{cols:"12",md:"6"},{default:n(()=>[c(o,{title:"Custom Checkboxes With Icon",code:d(pe)},{default:n(()=>[c(s)]),_:1},8,["code"])]),_:1}),c(g,{cols:"12",md:"6"},{default:n(()=>[c(o,{title:"Custom Radios With Image",code:d(he)},{default:n(()=>[c(S)]),_:1},8,["code"])]),_:1}),c(g,{cols:"12",md:"6"},{default:n(()=>[c(o,{title:"Custom Checkboxes With Image",code:d(be)},{default:n(()=>[c(D)]),_:1},8,["code"])]),_:1})]),_:1})}}});export{Be as default};
