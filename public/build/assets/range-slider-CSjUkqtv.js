import{au as H,a1 as J,r as V,aN as K,du as Q,aM as X,dv as ee,bU as le,Y as N,a6 as ae,bY as Y,b as u,q as te,bZ as se,dw as ue,b_ as oe,cC as ne,dx as A,e as n,dy as re,dz as O,b2 as ie,F as de,d as h,g as R,o as k,m as v,b1 as w,f as d,_ as ce,t as p}from"./main-BRN2JBrw.js";import{_ as me}from"./AppCardCode.vue_vue_type_style_index_0_lang-f1mNd_Lg.js";import{V as pe,a as x}from"./VRow-Cw23LpgL.js";import"./vue3-perfect-scrollbar-h9R898_l.js";import"./VCard-_AzuU6xP.js";import"./VAvatar-C5ZVrAt4.js";import"./VCardText-BORcZDbM.js";import"./VDivider-BJRlhygd.js";const ve=J({...ne(),...oe(),...ue(),strict:Boolean,modelValue:{type:Array,default:()=>[0,0]}},"VRangeSlider"),y=H()({name:"VRangeSlider",props:ve(),emits:{"update:focused":l=>!0,"update:modelValue":l=>!0,end:l=>!0,start:l=>!0},setup(l,s){let{slots:a,emit:o}=s;const e=V(),i=V(),_=V(),{rtlClasses:P}=K();function B(f){if(!e.value||!i.value)return;const m=A(f,e.value.$el,l.direction),r=A(f,i.value.$el,l.direction),b=Math.abs(m),T=Math.abs(r);return b<T||b===T&&m<0?e.value.$el:i.value.$el}const C=Q(l),t=X(l,"modelValue",void 0,f=>f?.length?f.map(m=>C.roundValue(m)):[0,0]),{activeThumbRef:c,hasLabels:W,max:F,min:D,mousePressed:q,onSliderMousedown:E,onSliderTouchstart:Z,position:M,trackContainerRef:G,disabled:$,readonly:g}=ee({props:l,steps:C,onSliderStart:()=>{if($.value||g.value){c.value?.blur();return}o("start",t.value)},onSliderEnd:f=>{let{value:m}=f;if($.value||g.value)c.value?.blur();else{const r=c.value===e.value?.$el?[m,t.value[1]]:[t.value[0],m];!l.strict&&r[0]<r[1]&&(t.value=r)}o("end",t.value)},onSliderMove:f=>{let{value:m}=f;const[r,b]=t.value;if($.value||g.value){c.value?.blur();return}!l.strict&&r===b&&r!==D.value&&(c.value=m>r?i.value?.$el:e.value?.$el,c.value?.focus()),c.value===e.value?.$el?t.value=[Math.min(m,b),b]:t.value=[r,Math.max(r,m)]},getActiveThumb:B}),{isFocused:U,focus:j,blur:I}=le(l),z=N(()=>M(t.value[0])),L=N(()=>M(t.value[1]));return ae(()=>{const f=Y.filterProps(l),m=!!(l.label||a.label||a.prepend);return u(Y,te({class:["v-slider","v-range-slider",{"v-slider--has-labels":!!a["tick-label"]||W.value,"v-slider--focused":U.value,"v-slider--pressed":q.value,"v-slider--disabled":$.value},P.value,l.class],style:l.style,ref:_},f,{focused:U.value}),{...a,prepend:m?r=>n(de,null,[a.label?.(r)??(l.label?u(ie,{class:"v-slider__label",text:l.label},null):void 0),a.prepend?.(r)]):void 0,default:r=>{let{id:b,messagesId:T}=r;return n("div",{class:"v-slider__container",onMousedown:g.value?void 0:E,onTouchstartPassive:g.value?void 0:Z},[n("input",{id:`${b.value}_start`,name:l.name||b.value,disabled:$.value,readonly:g.value,tabindex:"-1",value:t.value[0]},null),n("input",{id:`${b.value}_stop`,name:l.name||b.value,disabled:$.value,readonly:g.value,tabindex:"-1",value:t.value[1]},null),u(re,{ref:G,start:z.value,stop:L.value},{"tick-label":a["tick-label"]}),u(O,{ref:e,"aria-describedby":T.value,focused:U&&c.value===e.value?.$el,modelValue:t.value[0],"onUpdate:modelValue":S=>t.value=[S,t.value[1]],onFocus:S=>{j(),c.value=e.value?.$el,F.value!==D.value&&t.value[0]===t.value[1]&&t.value[1]===D.value&&S.relatedTarget!==i.value?.$el&&(e.value?.$el.blur(),i.value?.$el.focus())},onBlur:()=>{I(),c.value=void 0},min:D.value,max:t.value[1],position:z.value,ripple:l.ripple},{"thumb-label":a["thumb-label"]}),u(O,{ref:i,"aria-describedby":T.value,focused:U&&c.value===i.value?.$el,modelValue:t.value[1],"onUpdate:modelValue":S=>t.value=[t.value[0],S],onFocus:S=>{j(),c.value=i.value?.$el,F.value!==D.value&&t.value[0]===t.value[1]&&t.value[0]===F.value&&S.relatedTarget!==e.value?.$el&&(i.value?.$el.blur(),e.value?.$el.focus())},onBlur:()=>{I(),c.value=void 0},min:t.value[0],max:F.value,position:L.value,ripple:l.ripple},{"thumb-label":a["thumb-label"]})])}})}),se({focus:()=>e.value?.$el.focus()},_)}}),fe=h({__name:"DemoRangeSliderVertical",setup(l){const s=V([20,40]);return(a,o)=>(k(),R(y,{modelValue:v(s),"onUpdate:modelValue":o[0]||(o[0]=e=>w(s)?s.value=e:null),direction:"vertical"},null,8,["modelValue"]))}}),be=h({__name:"DemoRangeSliderThumbLabel",setup(l){const s=["Winter","Spring","Summer","Fall"],a=["tabler-snowflake","tabler-leaf","tabler-flame","tabler-droplet"],o=V([1,2]);return(e,i)=>(k(),R(y,{modelValue:v(o),"onUpdate:modelValue":i[0]||(i[0]=_=>w(o)?o.value=_:null),tick:s,min:"0",max:"3",step:1,"show-ticks":"always","thumb-label":"","tick-size":"4"},{"thumb-label":d(({modelValue:_})=>[u(ce,{icon:a[_]},null,8,["icon"])]),_:1},8,["modelValue"]))}}),Ve=h({__name:"DemoRangeSliderStep",setup(l){const s=V([20,40]);return(a,o)=>(k(),R(y,{modelValue:v(s),"onUpdate:modelValue":o[0]||(o[0]=e=>w(s)?s.value=e:null),step:"10"},null,8,["modelValue"]))}}),_e=h({__name:"DemoRangeSliderColor",setup(l){const s=V([10,60]);return(a,o)=>(k(),R(y,{modelValue:v(s),"onUpdate:modelValue":o[0]||(o[0]=e=>w(s)?s.value=e:null),color:"success"},null,8,["modelValue"]))}}),ge=h({__name:"DemoRangeSliderDisabled",setup(l){const s=V([30,60]);return(a,o)=>(k(),R(y,{modelValue:v(s),"onUpdate:modelValue":o[0]||(o[0]=e=>w(s)?s.value=e:null),disabled:"",label:"Disabled"},null,8,["modelValue"]))}}),Se=h({__name:"DemoRangeSliderBasic",setup(l){const s=V([10,60]);return(a,o)=>(k(),R(y,{modelValue:v(s),"onUpdate:modelValue":o[0]||(o[0]=e=>w(s)?s.value=e:null)},null,8,["modelValue"]))}}),he={ts:`<script setup lang="ts">
const sliderValues = ref([10, 60])
<\/script>

<template>
  <VRangeSlider v-model="sliderValues" />
</template>
`,js:`<script setup>
const sliderValues = ref([
  10,
  60,
])
<\/script>

<template>
  <VRangeSlider v-model="sliderValues" />
</template>
`},Re={ts:`<script lang="ts" setup>
const sliderValues = ref([10, 60])
<\/script>

<template>
  <VRangeSlider
    v-model="sliderValues"
    color="success"
  />
</template>
`,js:`<script setup>
const sliderValues = ref([
  10,
  60,
])
<\/script>

<template>
  <VRangeSlider
    v-model="sliderValues"
    color="success"
  />
</template>
`},ke={ts:`<script lang="ts" setup>
const slidersValues = ref([30, 60])
<\/script>

<template>
  <VRangeSlider
    v-model="slidersValues"
    disabled
    label="Disabled"
  />
</template>
`,js:`<script setup>
const slidersValues = ref([
  30,
  60,
])
<\/script>

<template>
  <VRangeSlider
    v-model="slidersValues"
    disabled
    label="Disabled"
  />
</template>
`},$e={ts:`<script lang="ts" setup>
const sliderValues = ref([20, 40])
<\/script>

<template>
  <VRangeSlider
    v-model="sliderValues"
    step="10"
  />
</template>
`,js:`<script setup>
const sliderValues = ref([
  20,
  40,
])
<\/script>

<template>
  <VRangeSlider
    v-model="sliderValues"
    step="10"
  />
</template>
`},xe={ts:`<script lang="ts" setup>
const seasons = ['Winter', 'Spring', 'Summer', 'Fall']
const icons = ['tabler-snowflake', 'tabler-leaf', 'tabler-flame', 'tabler-droplet']
const sliderValues = ref([1, 2])
<\/script>

<template>
  <VRangeSlider
    v-model="sliderValues"
    :tick="seasons"
    min="0"
    max="3"
    :step="1"
    show-ticks="always"
    thumb-label
    tick-size="4"
  >
    <template #thumb-label="{ modelValue }">
      <VIcon :icon="icons[modelValue]" />
    </template>
  </VRangeSlider>
</template>
`,js:`<script setup>
const seasons = [
  'Winter',
  'Spring',
  'Summer',
  'Fall',
]

const icons = [
  'tabler-snowflake',
  'tabler-leaf',
  'tabler-flame',
  'tabler-droplet',
]

const sliderValues = ref([
  1,
  2,
])
<\/script>

<template>
  <VRangeSlider
    v-model="sliderValues"
    :tick="seasons"
    min="0"
    max="3"
    :step="1"
    show-ticks="always"
    thumb-label
    tick-size="4"
  >
    <template #thumb-label="{ modelValue }">
      <VIcon :icon="icons[modelValue]" />
    </template>
  </VRangeSlider>
</template>
`},we={ts:`<script lang="ts" setup>
const sliderValues = ref([20, 40])
<\/script>

<template>
  <VRangeSlider
    v-model="sliderValues"
    direction="vertical"
  />
</template>
`,js:`<script setup>
const sliderValues = ref([
  20,
  40,
])
<\/script>

<template>
  <VRangeSlider
    v-model="sliderValues"
    direction="vertical"
  />
</template>
`},Me=h({__name:"range-slider",setup(l){return(s,a)=>{const o=Se,e=me,i=ge,_=_e,P=Ve,B=be,C=fe;return k(),R(pe,null,{default:d(()=>[u(x,{cols:"12",md:"6"},{default:d(()=>[u(e,{title:"Basic",code:v(he)},{default:d(()=>[a[0]||(a[0]=n("p",null,[p("The "),n("code",null,"v-slider"),p(" component is a better visualization of the number input.")],-1)),u(o)]),_:1},8,["code"])]),_:1}),u(x,{cols:"12",md:"6"},{default:d(()=>[u(e,{title:"Disabled",code:v(ke)},{default:d(()=>[a[1]||(a[1]=n("p",null,[p("You cannot interact with "),n("code",null,"disabled"),p(" sliders.")],-1)),u(i)]),_:1},8,["code"])]),_:1}),u(x,{cols:"12",md:"6"},{default:d(()=>[u(e,{title:"Color",code:v(Re)},{default:d(()=>[a[2]||(a[2]=n("p",null,[p("Use "),n("code",null,"color"),p(" prop to the sets the slider color. "),n("code",null,"track-color"),p(" prop to sets the color of slider's unfilled track.")],-1)),u(_)]),_:1},8,["code"])]),_:1}),u(x,{cols:"12",md:"6"},{default:d(()=>[u(e,{title:"Step",code:v($e)},{default:d(()=>[a[3]||(a[3]=n("p",null,[n("code",null,"v-range-slider"),p(" can have steps other than 1. This can be helpful for some applications where you need to adjust values with more or less accuracy.")],-1)),u(P)]),_:1},8,["code"])]),_:1}),u(x,{cols:"12",md:"6"},{default:d(()=>[u(e,{title:"Thumb label",code:v(xe)},{default:d(()=>[a[4]||(a[4]=n("p",null,[p(" Using the "),n("code",null,"tick-labels"),p(" prop along with the "),n("code",null,"thumb-label"),p(" slot, you can create a very customized solution. ")],-1)),u(B)]),_:1},8,["code"])]),_:1}),u(x,{cols:"12",md:"6"},{default:d(()=>[u(e,{title:"Vertical",code:v(we)},{default:d(()=>[a[5]||(a[5]=n("p",null,[p("You can use the "),n("code",null,"vertical"),p(" prop to switch sliders to a vertical orientation. If you need to change the height of the slider, use css.")],-1)),u(C)]),_:1},8,["code"])]),_:1})]),_:1})}}});export{Me as default};
