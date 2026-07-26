import{d as f,g as B,o as V,b0 as l,f as e,b as t,e as p,ah as m,ai as c,aj as d,q as g,t as a,b8 as L,r as y,a as k,m as v,b1 as C,c as b,F as h,i as D,v as P}from"./main-BRN2JBrw.js";import{V as u,a as S}from"./VList-BZwg0Aw7.js";import{a as I}from"./avatar-1-mrBiEG2D.js";import{V as A}from"./VDivider-BJRlhygd.js";import{V as $}from"./VCardText-BORcZDbM.js";import{d as j,V as F}from"./VCard-_AzuU6xP.js";import{_ as E}from"./AppCardCode.vue_vue_type_style_index_0_lang-f1mNd_Lg.js";import{V as R,a as _}from"./VRow-Cw23LpgL.js";import"./ssrBoot-gLLOKfTl.js";import"./VAvatar-C5ZVrAt4.js";import"./vue3-perfect-scrollbar-h9R898_l.js";const G=f({__name:"DemoMenuActivatorAndTooltip",setup(O){const i=[{title:"Option 1",value:"Option 1"},{title:"Option 2",value:"Option 2"},{title:"Option 3",value:"Option 3"}];return(s,o)=>(V(),B(l,{location:"top"},{activator:e(({props:n})=>[t(L,{location:"top"},{activator:e(({props:r})=>[t(m,c(d(g(n,r))),{default:e(()=>[...o[0]||(o[0]=[a(" Dropdown w/ Tooltip ",-1)])]),_:1},16)]),default:e(()=>[o[1]||(o[1]=p("span",null,"I am a Tooltip",-1))]),_:2},1024)]),default:e(()=>[t(u,{items:i})]),_:1}))}}),H=f({__name:"DemoMenuPopover",setup(O){const i=y(!1);return(s,o)=>{const n=k("IconBtn");return V(),B(l,{modelValue:v(i),"onUpdate:modelValue":o[0]||(o[0]=r=>C(i)?i.value=r:null),location:"top"},{activator:e(({props:r})=>[t(m,c(d(r)),{default:e(()=>[...o[1]||(o[1]=[a(" Menu as Popover ",-1)])]),_:1},16)]),default:e(()=>[t(F,{"max-width":"300"},{default:e(()=>[t(u,null,{default:e(()=>[t(S,{"prepend-avatar":v(I),title:"John Leider",subtitle:"Founder of Vuetify",class:"mx-0"},null,8,["prepend-avatar"])]),_:1}),t(A),t($,null,{default:e(()=>[...o[2]||(o[2]=[a(" Gingerbread bear claw cake. Soufflé candy sesame snaps chocolate ice cream cake. Dessert candy canes oat cake pudding cupcake. Bear claw sweet wafer bonbon dragée toffee. ",-1)])]),_:1}),t(j,null,{default:e(()=>[t(n,{icon:"tabler-heart"}),t(n,{icon:"tabler-bookmark"}),t(n,{icon:"tabler-thumb-down"})]),_:1})]),_:1})]),_:1},8,["modelValue"])}}}),J=f({__name:"DemoMenuOpenOnHover",setup(O){const i=[{title:"Option 1",value:"Option 1"},{title:"Option 2",value:"Option 2"},{title:"Option 3",value:"Option 3"}];return(s,o)=>(V(),B(l,{"open-on-hover":""},{activator:e(({props:n})=>[t(m,c(d(n)),{default:e(()=>[...o[0]||(o[0]=[a(" On hover ",-1)])]),_:1},16)]),default:e(()=>[t(u,{items:i})]),_:1}))}}),N={class:"demo-space-x"},X=f({__name:"DemoMenuLocation",setup(O){const i=[{title:"Option 1",value:"Option 1"},{title:"Option 2",value:"Option 2"},{title:"Option 3",value:"Option 3"}];return(s,o)=>(V(),b("div",N,[t(l,{location:"top"},{activator:e(({props:n})=>[t(m,c(d(n)),{default:e(()=>[...o[0]||(o[0]=[a(" Top ",-1)])]),_:1},16)]),default:e(()=>[t(u,{items:i})]),_:1}),t(l,{location:"bottom"},{activator:e(({props:n})=>[t(m,c(d(n)),{default:e(()=>[...o[1]||(o[1]=[a(" Bottom ",-1)])]),_:1},16)]),default:e(()=>[t(u,{items:i})]),_:1}),t(l,{location:"start"},{activator:e(({props:n})=>[t(m,c(d(n)),{default:e(()=>[...o[2]||(o[2]=[a(" Start ",-1)])]),_:1},16)]),default:e(()=>[t(u,{items:i})]),_:1}),t(l,{location:"end"},{activator:e(({props:n})=>[t(m,c(d(n)),{default:e(()=>[...o[3]||(o[3]=[a(" End ",-1)])]),_:1},16)]),default:e(()=>[t(u,{items:i})]),_:1})]))}}),Y={class:"demo-space-x"},U=f({__name:"DemoMenuCustomTransitions",setup(O){const i=[{title:"Option 1",value:"Option 1"},{title:"Option 2",value:"Option 2"},{title:"Option 3",value:"Option 3"}];return(s,o)=>(V(),b("div",Y,[t(l,{transition:"scale-transition"},{activator:e(({props:n})=>[t(m,c(d(n)),{default:e(()=>[...o[0]||(o[0]=[a(" Scale Transition ",-1)])]),_:1},16)]),default:e(()=>[t(u,{items:i})]),_:1}),t(l,{transition:"slide-x-transition"},{activator:e(({props:n})=>[t(m,c(d(n)),{default:e(()=>[...o[1]||(o[1]=[a(" Slide X Transition ",-1)])]),_:1},16)]),default:e(()=>[t(u,{items:i})]),_:1}),t(l,{transition:"slide-y-transition"},{activator:e(({props:n})=>[t(m,c(d(n)),{default:e(()=>[...o[2]||(o[2]=[a(" Slide Y Transition ",-1)])]),_:1},16)]),default:e(()=>[t(u,{items:i})]),_:1})]))}}),q={class:"demo-space-x"},z=f({__name:"DemoMenuBasic",setup(O){const i=["primary","secondary","success","info","warning","error"],s=[{title:"Option 1",value:"Option 1"},{title:"Option 2",value:"Option 2"},{title:"Option 3",value:"Option 3"}];return(o,n)=>(V(),b("div",q,[(V(),b(h,null,D(i,r=>t(l,{key:r},{activator:e(({props:M})=>[t(m,g({color:r},{ref_for:!0},M),{default:e(()=>[a(P(r),1)]),_:2},1040,["color"])]),default:e(()=>[t(u,{items:s})]),_:2},1024)),64))]))}}),W={ts:`<script lang="ts" setup>
import { mergeProps } from 'vue'

const items = [{ title: 'Option 1', value: 'Option 1' }, { title: 'Option 2', value: 'Option 2' }, { title: 'Option 3', value: 'Option 3' }]
<\/script>

<template>
  <VMenu location="top">
    <template #activator="{ props: menuProps }">
      <VTooltip location="top">
        <template #activator="{ props: tooltipProps }">
          <VBtn v-bind="mergeProps(menuProps, tooltipProps)">
            Dropdown w/ Tooltip
          </VBtn>
        </template>
        <span>I am a Tooltip</span>
      </VTooltip>
    </template>

    <VList :items="items" />
  </VMenu>
</template>
`,js:`<script setup>
import { mergeProps } from 'vue'

const items = [
  {
    title: 'Option 1',
    value: 'Option 1',
  },
  {
    title: 'Option 2',
    value: 'Option 2',
  },
  {
    title: 'Option 3',
    value: 'Option 3',
  },
]
<\/script>

<template>
  <VMenu location="top">
    <template #activator="{ props: menuProps }">
      <VTooltip location="top">
        <template #activator="{ props: tooltipProps }">
          <VBtn v-bind="mergeProps(menuProps, tooltipProps)">
            Dropdown w/ Tooltip
          </VBtn>
        </template>
        <span>I am a Tooltip</span>
      </VTooltip>
    </template>

    <VList :items="items" />
  </VMenu>
</template>
`},K={ts:`<script lang="ts" setup>
const menusVariant = ['primary', 'secondary', 'success', 'info', 'warning', 'error']
const items = [{ title: 'Option 1', value: 'Option 1' }, { title: 'Option 2', value: 'Option 2' }, { title: 'Option 3', value: 'Option 3' }]
<\/script>

<template>
  <div class="demo-space-x">
    <VMenu
      v-for="menu in menusVariant"
      :key="menu"
    >
      <template #activator="{ props }">
        <VBtn
          :color="menu"
          v-bind="props"
        >
          {{ menu }}
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>
  </div>
</template>
`,js:`<script setup>
const menusVariant = [
  'primary',
  'secondary',
  'success',
  'info',
  'warning',
  'error',
]

const items = [
  {
    title: 'Option 1',
    value: 'Option 1',
  },
  {
    title: 'Option 2',
    value: 'Option 2',
  },
  {
    title: 'Option 3',
    value: 'Option 3',
  },
]
<\/script>

<template>
  <div class="demo-space-x">
    <VMenu
      v-for="menu in menusVariant"
      :key="menu"
    >
      <template #activator="{ props }">
        <VBtn
          :color="menu"
          v-bind="props"
        >
          {{ menu }}
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>
  </div>
</template>
`},Q={ts:`<script lang="ts" setup>
const items = [{ title: 'Option 1', value: 'Option 1' }, { title: 'Option 2', value: 'Option 2' }, { title: 'Option 3', value: 'Option 3' }]
<\/script>

<template>
  <div class="demo-space-x">
    <VMenu transition="scale-transition">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          Scale Transition
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>

    <VMenu transition="slide-x-transition">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          Slide X Transition
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>

    <VMenu transition="slide-y-transition">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          Slide Y Transition
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>
  </div>
</template>
`,js:`<script setup>
const items = [
  {
    title: 'Option 1',
    value: 'Option 1',
  },
  {
    title: 'Option 2',
    value: 'Option 2',
  },
  {
    title: 'Option 3',
    value: 'Option 3',
  },
]
<\/script>

<template>
  <div class="demo-space-x">
    <VMenu transition="scale-transition">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          Scale Transition
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>

    <VMenu transition="slide-x-transition">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          Slide X Transition
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>

    <VMenu transition="slide-y-transition">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          Slide Y Transition
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>
  </div>
</template>
`},Z={ts:`<script lang="ts" setup>
const items = [{ title: 'Option 1', value: 'Option 1' }, { title: 'Option 2', value: 'Option 2' }, { title: 'Option 3', value: 'Option 3' }]
<\/script>

<template>
  <div class="demo-space-x">
    <VMenu location="top">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          Top
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>

    <VMenu location="bottom">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          Bottom
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>

    <VMenu location="start">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          Start
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>

    <VMenu location="end">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          End
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>
  </div>
</template>
`,js:`<script setup>
const items = [
  {
    title: 'Option 1',
    value: 'Option 1',
  },
  {
    title: 'Option 2',
    value: 'Option 2',
  },
  {
    title: 'Option 3',
    value: 'Option 3',
  },
]
<\/script>

<template>
  <div class="demo-space-x">
    <VMenu location="top">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          Top
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>

    <VMenu location="bottom">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          Bottom
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>

    <VMenu location="start">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          Start
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>

    <VMenu location="end">
      <template #activator="{ props }">
        <VBtn v-bind="props">
          End
        </VBtn>
      </template>

      <VList :items="items" />
    </VMenu>
  </div>
</template>
`},tt={ts:`<script lang="ts" setup>
const items = [{ title: 'Option 1', value: 'Option 1' }, { title: 'Option 2', value: 'Option 2' }, { title: 'Option 3', value: 'Option 3' }]
<\/script>

<template>
  <VMenu open-on-hover>
    <template #activator="{ props }">
      <VBtn v-bind="props">
        On hover
      </VBtn>
    </template>

    <VList :items="items" />
  </VMenu>
</template>
`,js:`<script setup>
const items = [
  {
    title: 'Option 1',
    value: 'Option 1',
  },
  {
    title: 'Option 2',
    value: 'Option 2',
  },
  {
    title: 'Option 3',
    value: 'Option 3',
  },
]
<\/script>

<template>
  <VMenu open-on-hover>
    <template #activator="{ props }">
      <VBtn v-bind="props">
        On hover
      </VBtn>
    </template>

    <VList :items="items" />
  </VMenu>
</template>
`},et={ts:`<script lang="ts" setup>
import avatar1 from '@images/avatars/avatar-1.png'

const menu = ref(false)
<\/script>

<template>
  <VMenu
    v-model="menu"
    location="top"
  >
    <template #activator="{ props }">
      <VBtn v-bind="props">
        Menu as Popover
      </VBtn>
    </template>

    <VCard max-width="300">
      <VList>
        <VListItem
          :prepend-avatar="avatar1"
          title="John Leider"
          subtitle="Founder of Vuetify"
          class="mx-0"
        />
      </VList>

      <VDivider />

      <VCardText>
        Gingerbread bear claw cake. Soufflé candy sesame snaps chocolate ice cream cake.
        Dessert candy canes oat cake pudding cupcake. Bear claw sweet wafer bonbon dragée toffee.
      </VCardText>

      <VCardActions>
        <IconBtn icon="tabler-heart" />
        <IconBtn icon="tabler-bookmark" />
        <IconBtn icon="tabler-thumb-down" />
      </VCardActions>
    </VCard>
  </VMenu>
</template>
`,js:`<script setup>
import avatar1 from '@images/avatars/avatar-1.png'

const menu = ref(false)
<\/script>

<template>
  <VMenu
    v-model="menu"
    location="top"
  >
    <template #activator="{ props }">
      <VBtn v-bind="props">
        Menu as Popover
      </VBtn>
    </template>

    <VCard max-width="300">
      <VList>
        <VListItem
          :prepend-avatar="avatar1"
          title="John Leider"
          subtitle="Founder of Vuetify"
          class="mx-0"
        />
      </VList>

      <VDivider />

      <VCardText>
        Gingerbread bear claw cake. Soufflé candy sesame snaps chocolate ice cream cake.
        Dessert candy canes oat cake pudding cupcake. Bear claw sweet wafer bonbon dragée toffee.
      </VCardText>

      <VCardActions>
        <IconBtn icon="tabler-heart" />
        <IconBtn icon="tabler-bookmark" />
        <IconBtn icon="tabler-thumb-down" />
      </VCardActions>
    </VCard>
  </VMenu>
</template>
`},dt=f({__name:"menu",setup(O){return(i,s)=>{const o=z,n=E,r=U,M=X,w=J,T=H,x=G;return V(),B(R,{class:"match-height"},{default:e(()=>[t(_,{cols:"12",md:"6"},{default:e(()=>[t(n,{title:"Basic",code:v(K)},{default:e(()=>[s[0]||(s[0]=p("p",null," Remember to put the element that activates the menu in the activator slot. ",-1)),t(o)]),_:1},8,["code"])]),_:1}),t(_,{cols:"12",md:"6"},{default:e(()=>[t(n,{title:"Custom transitions",code:v(Q)},{default:e(()=>[s[1]||(s[1]=p("p",null,[a("Vuetify comes with 3 standard transitions, "),p("code",null,"scale"),a(", "),p("code",null,"slide-x"),a(" and "),p("code",null,"slide-y"),a(". Use "),p("code",null,"transition"),a(" prop to add transition to a menu.")],-1)),t(r)]),_:1},8,["code"])]),_:1}),t(_,{cols:"12",md:"6"},{default:e(()=>[t(n,{title:"Location",code:v(Z)},{default:e(()=>[s[2]||(s[2]=p("p",null,[a("Menu can be offset relative to the activator by using the "),p("code",null,"location"),a(" prop.")],-1)),t(M)]),_:1},8,["code"])]),_:1}),t(_,{cols:"12",md:"6"},{default:e(()=>[t(n,{title:"Open on hover",code:v(tt)},{default:e(()=>[s[3]||(s[3]=p("p",null,[a("Menus can be accessed using hover instead of clicking with the "),p("code",null,"open-on-hover"),a(" prop.")],-1)),t(w)]),_:1},8,["code"])]),_:1}),t(_,{cols:"12",md:"6"},{default:e(()=>[t(n,{title:"Popover",code:v(et)},{default:e(()=>[s[4]||(s[4]=p("p",null,"A menu can be configured to be static when opened, allowing it to function as a popover. This can be useful when there are multiple interactive items within the menu contents.",-1)),t(T)]),_:1},8,["code"])]),_:1}),t(_,{cols:"12",md:"6"},{default:e(()=>[t(n,{title:"Activator and tooltip",code:v(W)},{default:e(()=>[s[5]||(s[5]=p("p",null,[a("With the new "),p("code",null,"v-slot"),a(" syntax, nested activators such as those seen with a "),p("code",null,"v-menu"),a(" and "),p("code",null,"v-tooltip"),a(" attached to the same activator button, need a particular setup in order to function correctly")],-1)),t(x)]),_:1},8,["code"])]),_:1})]),_:1})}}});export{dt as default};
