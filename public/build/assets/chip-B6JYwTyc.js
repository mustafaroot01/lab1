import{d as x,r as V,g as C,o as c,b0 as $,m as o,b1 as k,f as l,b as e,t,ah as w,_ as v,ai as z,aj as L,c as h,e as n,l as y}from"./main-BRN2JBrw.js";import{a as A,b as M,d as J,V as T}from"./VList-BZwg0Aw7.js";import{V as j}from"./VListItemAction-D6mRtA7P.js";import{V as a}from"./VChip-DIy4dUl9.js";import{_ as N}from"./AppCombobox.vue_vue_type_script_setup_true_lang-DUelD_fg.js";import{_}from"./_plugin-vue_export-helper-DlAUqK2U.js";import{a as B}from"./avatar-1-mrBiEG2D.js";import{a as R}from"./avatar-2-J3iDMrW6.js";import{a as F}from"./avatar-3-CCfT4M2Q.js";import{a as U}from"./avatar-4-CkT0aFRW.js";import{V as I}from"./VAvatar-C5ZVrAt4.js";import{_ as Y}from"./AppCardCode.vue_vue_type_style_index_0_lang-f1mNd_Lg.js";import{V as O,a as d}from"./VRow-Cw23LpgL.js";import"./ssrBoot-gLLOKfTl.js";import"./VDivider-BJRlhygd.js";import"./VSlideGroup-DOzzYFQv.js";import"./VSelect-GGs43KVK.js";import"./VTextField-B-SafXsh.js";import"./autofocus-Db38RUhk.js";import"./VCounter-BXsWeC_3.js";import"./VField-CXZn0NIc.js";import"./VCheckboxBtn-BmEGxGs1.js";import"./VSelectionControl-C_9VPf01.js";import"./filter-CD5uhF84.js";import"./vue3-perfect-scrollbar-h9R898_l.js";import"./VCard-_AzuU6xP.js";import"./VCardText-BORcZDbM.js";const q=x({__name:"DemoChipExpandable",setup(m){const i=V(!1);return(r,u)=>(c(),C($,{modelValue:o(i),"onUpdate:modelValue":u[1]||(u[1]=p=>k(i)?i.value=p:null),transition:"scale-transition"},{activator:l(({props:p})=>[e(a,z(L(p)),{default:l(()=>[...u[2]||(u[2]=[t(" VueJS ",-1)])]),_:1},16)]),default:l(()=>[e(T,null,{default:l(()=>[e(A,null,{append:l(()=>[e(j,{class:"ms-3"},{default:l(()=>[e(w,{icon:"",variant:"text",size:"x-small",color:"default",onClick:u[0]||(u[0]=p=>i.value=!1)},{default:l(()=>[e(v,{size:"20",icon:"tabler-x"})]),_:1})]),_:1})]),default:l(()=>[e(M,{class:"mb-2"},{default:l(()=>[...u[3]||(u[3]=[t(" VueJS ",-1)])]),_:1}),e(J,null,{default:l(()=>[...u[4]||(u[4]=[t("The Progressive JavaScript Framework",-1)])]),_:1})]),_:1})]),_:1})]),_:1},8,["modelValue"]))}}),G=x({__name:"DemoChipInSelects",setup(m){const i=V(["Programming","Playing games","Sleeping"]),r=V(["Streaming","Eating","Programming","Playing games","Sleeping"]);return(u,p)=>{const b=N;return c(),C(b,{modelValue:o(i),"onUpdate:modelValue":p[0]||(p[0]=g=>k(i)?i.value=g:null),chips:"",clearable:"",multiple:"","closable-chips":"","clear-icon":"tabler-circle-x",items:o(r),label:"Your favorite hobbies","prepend-icon":"tabler-filter"},null,8,["modelValue","items"])}}}),H={},K={class:"demo-space-x"};function Q(m,i){return c(),h("div",K,[e(a,{size:"x-small"},{default:l(()=>[...i[0]||(i[0]=[t(" x-small chip ",-1)])]),_:1}),e(a,{size:"small"},{default:l(()=>[...i[1]||(i[1]=[t(" small chip ",-1)])]),_:1}),e(a,{size:"default"},{default:l(()=>[...i[2]||(i[2]=[t(" Default ",-1)])]),_:1}),e(a,{size:"large"},{default:l(()=>[...i[3]||(i[3]=[t(" large chip ",-1)])]),_:1}),e(a,{size:"x-large"},{default:l(()=>[...i[4]||(i[4]=[t(" x-large chip ",-1)])]),_:1})])}const X=_(H,[["render",Q]]),Z={class:"demo-space-x"},ii=x({__name:"DemoChipWithAvatar",setup(m){return(i,r)=>(c(),h("div",Z,[e(a,null,{default:l(()=>[e(I,{start:"",image:o(B)},null,8,["image"]),r[0]||(r[0]=n("span",null,"John Doe",-1))]),_:1}),e(a,null,{default:l(()=>[e(I,{start:"",image:o(R)},null,8,["image"]),r[1]||(r[1]=n("span",null,"Darcy Nooser",-1))]),_:1}),e(a,{pill:"",label:!1,"prepend-avatar":o(F)},{default:l(()=>[...r[2]||(r[2]=[n("span",null,"Felicia Risker",-1)])]),_:1},8,["prepend-avatar"]),e(a,{pill:"",label:!1},{default:l(()=>[e(I,{start:"",image:o(U)},null,8,["image"]),r[3]||(r[3]=n("span",null,"Minnie Mostly",-1))]),_:1})]))}}),ei={},li={class:"demo-space-x"};function ti(m,i){return c(),h("div",li,[e(a,null,{default:l(()=>[e(v,{start:"",icon:"tabler-user"}),i[0]||(i[0]=t(" Account ",-1))]),_:1}),e(a,{color:"primary"},{default:l(()=>[e(v,{start:"",icon:"tabler-star"}),i[1]||(i[1]=t(" Premium ",-1))]),_:1}),e(a,{color:"secondary"},{default:l(()=>[e(v,{start:"",icon:"tabler-cake"}),i[2]||(i[2]=t(" 1 Year ",-1))]),_:1}),e(a,{color:"success"},{default:l(()=>[e(v,{start:"",icon:"tabler-bell"}),i[3]||(i[3]=t(" Notification ",-1))]),_:1}),e(a,{color:"info"},{default:l(()=>[e(v,{start:"",icon:"tabler-messages"}),i[4]||(i[4]=t(" Message ",-1))]),_:1}),e(a,{color:"warning"},{default:l(()=>[e(v,{start:"",icon:"tabler-alert-triangle"}),i[5]||(i[5]=t(" Warning ",-1))]),_:1}),e(a,{color:"error"},{default:l(()=>[e(v,{start:"",icon:"tabler-alert-circle"}),i[6]||(i[6]=t(" Error ",-1))]),_:1})])}const ai=_(ei,[["render",ti]]),ri={class:"demo-space-x"},oi=x({__name:"DemoChipClosable",setup(m){const i=V(!0),r=V(!0),u=V(!0),p=V(!0),b=V(!0),g=V(!0),S=V(!0);return(D,s)=>(c(),h("div",ri,[o(i)?(c(),C(a,{key:0,closable:"","onClick:close":s[0]||(s[0]=f=>i.value=!o(i))},{default:l(()=>[...s[7]||(s[7]=[t(" Default ",-1)])]),_:1})):y("",!0),o(r)?(c(),C(a,{key:1,closable:"",color:"primary","onClick:close":s[1]||(s[1]=f=>r.value=!o(r))},{default:l(()=>[...s[8]||(s[8]=[t(" Primary ",-1)])]),_:1})):y("",!0),o(u)?(c(),C(a,{key:2,closable:"",color:"secondary","onClick:close":s[2]||(s[2]=f=>u.value=!o(u))},{default:l(()=>[...s[9]||(s[9]=[t(" Secondary ",-1)])]),_:1})):y("",!0),o(p)?(c(),C(a,{key:3,closable:"",color:"success","onClick:close":s[3]||(s[3]=f=>p.value=!o(p))},{default:l(()=>[...s[10]||(s[10]=[t(" Success ",-1)])]),_:1})):y("",!0),o(b)?(c(),C(a,{key:4,closable:"",color:"info","onClick:close":s[4]||(s[4]=f=>b.value=!o(b))},{default:l(()=>[...s[11]||(s[11]=[t(" Info ",-1)])]),_:1})):y("",!0),o(g)?(c(),C(a,{key:5,closable:"",color:"warning","onClick:close":s[5]||(s[5]=f=>g.value=!o(g))},{default:l(()=>[...s[12]||(s[12]=[t(" Warning ",-1)])]),_:1})):y("",!0),o(S)?(c(),C(a,{key:6,closable:"",color:"error","onClick:close":s[6]||(s[6]=f=>S.value=!o(S))},{default:l(()=>[...s[13]||(s[13]=[t(" Error ",-1)])]),_:1})):y("",!0)]))}}),si={},ni={class:"demo-space-x"};function pi(m,i){return c(),h("div",ni,[e(a,{label:!1},{default:l(()=>[...i[0]||(i[0]=[t(" Default ",-1)])]),_:1}),e(a,{label:!1,color:"primary"},{default:l(()=>[...i[1]||(i[1]=[t(" Primary ",-1)])]),_:1}),e(a,{label:!1,color:"secondary"},{default:l(()=>[...i[2]||(i[2]=[t(" Secondary ",-1)])]),_:1}),e(a,{label:!1,color:"success"},{default:l(()=>[...i[3]||(i[3]=[t(" Success ",-1)])]),_:1}),e(a,{label:!1,color:"info"},{default:l(()=>[...i[4]||(i[4]=[t(" Info ",-1)])]),_:1}),e(a,{label:!1,color:"warning"},{default:l(()=>[...i[5]||(i[5]=[t(" Warning ",-1)])]),_:1}),e(a,{label:!1,color:"error"},{default:l(()=>[...i[6]||(i[6]=[t(" Error ",-1)])]),_:1})])}const ci=_(si,[["render",pi]]),ui={},mi={class:"demo-space-x"};function di(m,i){return c(),h("div",mi,[e(a,{variant:"outlined"},{default:l(()=>[...i[0]||(i[0]=[t(" Default ",-1)])]),_:1}),e(a,{color:"primary",variant:"outlined"},{default:l(()=>[...i[1]||(i[1]=[t(" Primary ",-1)])]),_:1}),e(a,{color:"secondary",variant:"outlined"},{default:l(()=>[...i[2]||(i[2]=[t(" Secondary ",-1)])]),_:1}),e(a,{color:"success",variant:"outlined"},{default:l(()=>[...i[3]||(i[3]=[t(" Success ",-1)])]),_:1}),e(a,{color:"info",variant:"outlined"},{default:l(()=>[...i[4]||(i[4]=[t(" Info ",-1)])]),_:1}),e(a,{color:"warning",variant:"outlined"},{default:l(()=>[...i[5]||(i[5]=[t(" Warning ",-1)])]),_:1}),e(a,{color:"error",variant:"outlined"},{default:l(()=>[...i[6]||(i[6]=[t(" Error ",-1)])]),_:1})])}const Vi=_(ui,[["render",di]]),Ci={},fi={class:"demo-space-x"};function vi(m,i){return c(),h("div",fi,[e(a,{variant:"elevated"},{default:l(()=>[...i[0]||(i[0]=[t(" Default ",-1)])]),_:1}),e(a,{color:"primary",variant:"elevated"},{default:l(()=>[...i[1]||(i[1]=[t(" Primary ",-1)])]),_:1}),e(a,{color:"secondary",variant:"elevated"},{default:l(()=>[...i[2]||(i[2]=[t(" Secondary ",-1)])]),_:1}),e(a,{color:"success",variant:"elevated"},{default:l(()=>[...i[3]||(i[3]=[t(" Success ",-1)])]),_:1}),e(a,{color:"info",variant:"elevated"},{default:l(()=>[...i[4]||(i[4]=[t(" Info ",-1)])]),_:1}),e(a,{color:"warning",variant:"elevated"},{default:l(()=>[...i[5]||(i[5]=[t(" Warning ",-1)])]),_:1}),e(a,{color:"error",variant:"elevated"},{default:l(()=>[...i[6]||(i[6]=[t(" Error ",-1)])]),_:1})])}const hi=_(Ci,[["render",vi]]),bi={},gi={class:"demo-space-x"};function yi(m,i){return c(),h("div",gi,[e(a,null,{default:l(()=>[...i[0]||(i[0]=[t(" Default ",-1)])]),_:1}),e(a,{color:"primary"},{default:l(()=>[...i[1]||(i[1]=[t(" Primary ",-1)])]),_:1}),e(a,{color:"secondary"},{default:l(()=>[...i[2]||(i[2]=[t(" Secondary ",-1)])]),_:1}),e(a,{color:"success"},{default:l(()=>[...i[3]||(i[3]=[t(" Success ",-1)])]),_:1}),e(a,{color:"info"},{default:l(()=>[...i[4]||(i[4]=[t(" Info ",-1)])]),_:1}),e(a,{color:"warning"},{default:l(()=>[...i[5]||(i[5]=[t(" Warning ",-1)])]),_:1}),e(a,{color:"error"},{default:l(()=>[...i[6]||(i[6]=[t(" Error ",-1)])]),_:1})])}const _i=_(bi,[["render",yi]]),Si={ts:`<script lang="ts" setup>
const isDefaultChipVisible = ref(true)
const isPrimaryChipVisible = ref(true)
const isSecondaryChipVisible = ref(true)
const isSuccessChipVisible = ref(true)
const isInfoChipVisible = ref(true)
const isWarningChipVisible = ref(true)
const isErrorChipVisible = ref(true)
<\/script>

<template>
  <div class="demo-space-x">
    <VChip
      v-if="isDefaultChipVisible"
      closable
      @click:close="isDefaultChipVisible = !isDefaultChipVisible"
    >
      Default
    </VChip>

    <VChip
      v-if="isPrimaryChipVisible"
      closable
      color="primary"
      @click:close="isPrimaryChipVisible = !isPrimaryChipVisible"
    >
      Primary
    </VChip>

    <VChip
      v-if="isSecondaryChipVisible"
      closable
      color="secondary"
      @click:close="isSecondaryChipVisible = !isSecondaryChipVisible"
    >
      Secondary
    </VChip>

    <VChip
      v-if="isSuccessChipVisible"
      closable
      color="success"
      @click:close="isSuccessChipVisible = !isSuccessChipVisible"
    >
      Success
    </VChip>

    <VChip
      v-if="isInfoChipVisible"
      closable
      color="info"
      @click:close="isInfoChipVisible = !isInfoChipVisible"
    >
      Info
    </VChip>

    <VChip
      v-if="isWarningChipVisible"
      closable
      color="warning"
      @click:close="isWarningChipVisible = !isWarningChipVisible"
    >
      Warning
    </VChip>

    <VChip
      v-if="isErrorChipVisible"
      closable
      color="error"
      @click:close="isErrorChipVisible = !isErrorChipVisible"
    >
      Error
    </VChip>
  </div>
</template>
`,js:`<script setup>
const isDefaultChipVisible = ref(true)
const isPrimaryChipVisible = ref(true)
const isSecondaryChipVisible = ref(true)
const isSuccessChipVisible = ref(true)
const isInfoChipVisible = ref(true)
const isWarningChipVisible = ref(true)
const isErrorChipVisible = ref(true)
<\/script>

<template>
  <div class="demo-space-x">
    <VChip
      v-if="isDefaultChipVisible"
      closable
      @click:close="isDefaultChipVisible = !isDefaultChipVisible"
    >
      Default
    </VChip>

    <VChip
      v-if="isPrimaryChipVisible"
      closable
      color="primary"
      @click:close="isPrimaryChipVisible = !isPrimaryChipVisible"
    >
      Primary
    </VChip>

    <VChip
      v-if="isSecondaryChipVisible"
      closable
      color="secondary"
      @click:close="isSecondaryChipVisible = !isSecondaryChipVisible"
    >
      Secondary
    </VChip>

    <VChip
      v-if="isSuccessChipVisible"
      closable
      color="success"
      @click:close="isSuccessChipVisible = !isSuccessChipVisible"
    >
      Success
    </VChip>

    <VChip
      v-if="isInfoChipVisible"
      closable
      color="info"
      @click:close="isInfoChipVisible = !isInfoChipVisible"
    >
      Info
    </VChip>

    <VChip
      v-if="isWarningChipVisible"
      closable
      color="warning"
      @click:close="isWarningChipVisible = !isWarningChipVisible"
    >
      Warning
    </VChip>

    <VChip
      v-if="isErrorChipVisible"
      closable
      color="error"
      @click:close="isErrorChipVisible = !isErrorChipVisible"
    >
      Error
    </VChip>
  </div>
</template>
`},xi={ts:`<template>
  <div class="demo-space-x">
    <VChip>
      Default
    </VChip>

    <VChip color="primary">
      Primary
    </VChip>

    <VChip color="secondary">
      Secondary
    </VChip>

    <VChip color="success">
      Success
    </VChip>

    <VChip color="info">
      Info
    </VChip>

    <VChip color="warning">
      Warning
    </VChip>

    <VChip color="error">
      Error
    </VChip>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VChip>
      Default
    </VChip>

    <VChip color="primary">
      Primary
    </VChip>

    <VChip color="secondary">
      Secondary
    </VChip>

    <VChip color="success">
      Success
    </VChip>

    <VChip color="info">
      Info
    </VChip>

    <VChip color="warning">
      Warning
    </VChip>

    <VChip color="error">
      Error
    </VChip>
  </div>
</template>
`},Ii={ts:`<template>
  <div class="demo-space-x">
    <VChip variant="elevated">
      Default
    </VChip>

    <VChip
      color="primary"
      variant="elevated"
    >
      Primary
    </VChip>

    <VChip
      color="secondary"
      variant="elevated"
    >
      Secondary
    </VChip>

    <VChip
      color="success"
      variant="elevated"
    >
      Success
    </VChip>

    <VChip
      color="info"
      variant="elevated"
    >
      Info
    </VChip>

    <VChip
      color="warning"
      variant="elevated"
    >
      Warning
    </VChip>

    <VChip
      color="error"
      variant="elevated"
    >
      Error
    </VChip>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VChip variant="elevated">
      Default
    </VChip>

    <VChip
      color="primary"
      variant="elevated"
    >
      Primary
    </VChip>

    <VChip
      color="secondary"
      variant="elevated"
    >
      Secondary
    </VChip>

    <VChip
      color="success"
      variant="elevated"
    >
      Success
    </VChip>

    <VChip
      color="info"
      variant="elevated"
    >
      Info
    </VChip>

    <VChip
      color="warning"
      variant="elevated"
    >
      Warning
    </VChip>

    <VChip
      color="error"
      variant="elevated"
    >
      Error
    </VChip>
  </div>
</template>
`},Di={ts:`<script lang="ts" setup>
const isMenuVisible = ref(false)
<\/script>

<template>
  <VMenu
    v-model="isMenuVisible"
    transition="scale-transition"
  >
    <!-- v-menu activator -->
    <template #activator="{ props }">
      <VChip v-bind="props">
        VueJS
      </VChip>
    </template>

    <!-- v-menu list -->
    <VList>
      <VListItem>
        <VListItemTitle class="mb-2">
          VueJS
        </VListItemTitle>
        <VListItemSubtitle>The Progressive JavaScript Framework</VListItemSubtitle>

        <template #append>
          <VListItemAction class="ms-3">
            <VBtn
              icon
              variant="text"
              size="x-small"
              color="default"
              @click="isMenuVisible = false"
            >
              <VIcon
                size="20"
                icon="tabler-x"
              />
            </VBtn>
          </VListItemAction>
        </template>
      </VListItem>
    </VList>
  </VMenu>
</template>
`,js:`<script setup>
const isMenuVisible = ref(false)
<\/script>

<template>
  <VMenu
    v-model="isMenuVisible"
    transition="scale-transition"
  >
    <!-- v-menu activator -->
    <template #activator="{ props }">
      <VChip v-bind="props">
        VueJS
      </VChip>
    </template>

    <!-- v-menu list -->
    <VList>
      <VListItem>
        <VListItemTitle class="mb-2">
          VueJS
        </VListItemTitle>
        <VListItemSubtitle>The Progressive JavaScript Framework</VListItemSubtitle>

        <template #append>
          <VListItemAction class="ms-3">
            <VBtn
              icon
              variant="text"
              size="x-small"
              color="default"
              @click="isMenuVisible = false"
            >
              <VIcon
                size="20"
                icon="tabler-x"
              />
            </VBtn>
          </VListItemAction>
        </template>
      </VListItem>
    </VList>
  </VMenu>
</template>
`},ki={ts:`<script lang="ts" setup>
const chips = ref(['Programming', 'Playing games', 'Sleeping'])
const items = ref(['Streaming', 'Eating', 'Programming', 'Playing games', 'Sleeping'])
<\/script>

<template>
  <AppCombobox
    v-model="chips"
    chips
    clearable
    multiple
    closable-chips
    clear-icon="tabler-circle-x"
    :items="items"
    label="Your favorite hobbies"
    prepend-icon="tabler-filter"
  />
</template>
`,js:`<script setup>
const chips = ref([
  'Programming',
  'Playing games',
  'Sleeping',
])

const items = ref([
  'Streaming',
  'Eating',
  'Programming',
  'Playing games',
  'Sleeping',
])
<\/script>

<template>
  <AppCombobox
    v-model="chips"
    chips
    clearable
    multiple
    closable-chips
    clear-icon="tabler-circle-x"
    :items="items"
    label="Your favorite hobbies"
    prepend-icon="tabler-filter"
  />
</template>
`},Pi={ts:`<template>
  <div class="demo-space-x">
    <VChip variant="outlined">
      Default
    </VChip>

    <VChip
      color="primary"
      variant="outlined"
    >
      Primary
    </VChip>

    <VChip
      color="secondary"
      variant="outlined"
    >
      Secondary
    </VChip>

    <VChip
      color="success"
      variant="outlined"
    >
      Success
    </VChip>

    <VChip
      color="info"
      variant="outlined"
    >
      Info
    </VChip>

    <VChip
      color="warning"
      variant="outlined"
    >
      Warning
    </VChip>

    <VChip
      color="error"
      variant="outlined"
    >
      Error
    </VChip>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VChip variant="outlined">
      Default
    </VChip>

    <VChip
      color="primary"
      variant="outlined"
    >
      Primary
    </VChip>

    <VChip
      color="secondary"
      variant="outlined"
    >
      Secondary
    </VChip>

    <VChip
      color="success"
      variant="outlined"
    >
      Success
    </VChip>

    <VChip
      color="info"
      variant="outlined"
    >
      Info
    </VChip>

    <VChip
      color="warning"
      variant="outlined"
    >
      Warning
    </VChip>

    <VChip
      color="error"
      variant="outlined"
    >
      Error
    </VChip>
  </div>
</template>
`},Ei={ts:`<template>
  <div class="demo-space-x">
    <VChip :label="false">
      Default
    </VChip>

    <VChip
      :label="false"
      color="primary"
    >
      Primary
    </VChip>

    <VChip
      :label="false"
      color="secondary"
    >
      Secondary
    </VChip>

    <VChip
      :label="false"
      color="success"
    >
      Success
    </VChip>

    <VChip
      :label="false"
      color="info"
    >
      Info
    </VChip>

    <VChip
      :label="false"
      color="warning"
    >
      Warning
    </VChip>

    <VChip
      :label="false"
      color="error"
    >
      Error
    </VChip>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VChip :label="false">
      Default
    </VChip>

    <VChip
      :label="false"
      color="primary"
    >
      Primary
    </VChip>

    <VChip
      :label="false"
      color="secondary"
    >
      Secondary
    </VChip>

    <VChip
      :label="false"
      color="success"
    >
      Success
    </VChip>

    <VChip
      :label="false"
      color="info"
    >
      Info
    </VChip>

    <VChip
      :label="false"
      color="warning"
    >
      Warning
    </VChip>

    <VChip
      :label="false"
      color="error"
    >
      Error
    </VChip>
  </div>
</template>
`},Wi={ts:`<template>
  <div class="demo-space-x">
    <VChip size="x-small">
      x-small chip
    </VChip>

    <VChip size="small">
      small chip
    </VChip>

    <VChip size="default">
      Default
    </VChip>

    <VChip size="large">
      large chip
    </VChip>

    <VChip size="x-large">
      x-large chip
    </VChip>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VChip size="x-small">
      x-small chip
    </VChip>

    <VChip size="small">
      small chip
    </VChip>

    <VChip size="default">
      Default
    </VChip>

    <VChip size="large">
      large chip
    </VChip>

    <VChip size="x-large">
      x-large chip
    </VChip>
  </div>
</template>
`},$i={ts:`<script setup lang="ts">
import avatar1 from '@images/avatars/avatar-1.png'
import avatar2 from '@images/avatars/avatar-2.png'
import avatar3 from '@images/avatars/avatar-3.png'
import avatar4 from '@images/avatars/avatar-4.png'
<\/script>

<template>
  <div class="demo-space-x">
    <VChip>
      <VAvatar
        start
        :image="avatar1"
      />
      <span>John Doe</span>
    </VChip>

    <VChip>
      <VAvatar
        start
        :image="avatar2"
      />
      <span>Darcy Nooser</span>
    </VChip>

    <VChip
      pill
      :label="false"
      :prepend-avatar="avatar3"
    >
      <span>Felicia Risker</span>
    </VChip>

    <VChip
      pill
      :label="false"
    >
      <VAvatar
        start
        :image="avatar4"
      />
      <span>Minnie Mostly</span>
    </VChip>
  </div>
</template>
`,js:`<script setup>
import avatar1 from '@images/avatars/avatar-1.png'
import avatar2 from '@images/avatars/avatar-2.png'
import avatar3 from '@images/avatars/avatar-3.png'
import avatar4 from '@images/avatars/avatar-4.png'
<\/script>

<template>
  <div class="demo-space-x">
    <VChip>
      <VAvatar
        start
        :image="avatar1"
      />
      <span>John Doe</span>
    </VChip>

    <VChip>
      <VAvatar
        start
        :image="avatar2"
      />
      <span>Darcy Nooser</span>
    </VChip>

    <VChip
      pill
      :label="false"
      :prepend-avatar="avatar3"
    >
      <span>Felicia Risker</span>
    </VChip>

    <VChip
      pill
      :label="false"
    >
      <VAvatar
        start
        :image="avatar4"
      />
      <span>Minnie Mostly</span>
    </VChip>
  </div>
</template>
`},wi={ts:`<template>
  <div class="demo-space-x">
    <VChip>
      <VIcon
        start
        icon="tabler-user"
      />
      Account
    </VChip>

    <VChip color="primary">
      <VIcon
        start
        icon="tabler-star"
      />
      Premium
    </VChip>

    <VChip color="secondary">
      <VIcon
        start
        icon="tabler-cake"
      />
      1 Year
    </VChip>

    <VChip color="success">
      <VIcon
        start
        icon="tabler-bell"
      />
      Notification
    </VChip>

    <VChip color="info">
      <VIcon
        start
        icon="tabler-messages"
      />
      Message
    </VChip>

    <VChip color="warning">
      <VIcon
        start
        icon="tabler-alert-triangle"
      />
      Warning
    </VChip>

    <VChip color="error">
      <VIcon
        start
        icon="tabler-alert-circle"
      />
      Error
    </VChip>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VChip>
      <VIcon
        start
        icon="tabler-user"
      />
      Account
    </VChip>

    <VChip color="primary">
      <VIcon
        start
        icon="tabler-star"
      />
      Premium
    </VChip>

    <VChip color="secondary">
      <VIcon
        start
        icon="tabler-cake"
      />
      1 Year
    </VChip>

    <VChip color="success">
      <VIcon
        start
        icon="tabler-bell"
      />
      Notification
    </VChip>

    <VChip color="info">
      <VIcon
        start
        icon="tabler-messages"
      />
      Message
    </VChip>

    <VChip color="warning">
      <VIcon
        start
        icon="tabler-alert-triangle"
      />
      Warning
    </VChip>

    <VChip color="error">
      <VIcon
        start
        icon="tabler-alert-circle"
      />
      Error
    </VChip>
  </div>
</template>
`},oe=x({__name:"chip",setup(m){return(i,r)=>{const u=_i,p=Y,b=hi,g=Vi,S=ci,D=oi,s=ai,f=ii,P=X,E=G,W=q;return c(),C(O,{class:"match-height"},{default:l(()=>[e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"Color",code:o(xi)},{default:l(()=>[r[0]||(r[0]=n("p",null,[t("Use "),n("code",null,"color"),t(" prop to change the background color of chips.")],-1)),e(u)]),_:1},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"Elevated",code:o(Ii)},{default:l(()=>[r[1]||(r[1]=n("p",null,[t("Use "),n("code",null,"elevated"),t(" variant option to create filled chips.")],-1)),e(b)]),_:1},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"Outlined",code:o(Pi)},{default:l(()=>[r[2]||(r[2]=n("p",null,[t("Use "),n("code",null,"outlined"),t(" variant option to create outline border chips.")],-1)),e(g)]),_:1},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"Rounded",code:o(Ei)},{default:l(()=>[r[3]||(r[3]=n("p",null,[t("To use the rounded chip, set "),n("code",null,"label"),t(" props value to "),n("strong",null,"false"),t(".")],-1)),e(S)]),_:1},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"Closable",code:o(Si)},{default:l(()=>[r[4]||(r[4]=n("p",null,[t("Closable chips can be controlled with a "),n("code",null,"v-model"),t(".")],-1)),e(D)]),_:1},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"With Icon",code:o(wi)},{default:l(()=>[r[5]||(r[5]=n("p",null,"Chips can use text or any icon available in the Material Icons font library.",-1)),e(s)]),_:1},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"With Avatar",code:o($i)},{default:l(()=>[r[6]||(r[6]=n("p",null,[t("Use "),n("code",null,"pill"),t(" prop to remove the "),n("code",null,"v-avatar"),t(" padding.")],-1)),e(f)]),_:1},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"Sizes",code:o(Wi)},{default:l(()=>[r[7]||(r[7]=n("p",null,[t("The "),n("code",null,"v-chip"),t(" component can have various sizes from "),n("code",null,"x-small"),t(" to "),n("code",null,"x-large"),t(".")],-1)),e(P)]),_:1},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"In Selects",code:o(ki)},{default:l(()=>[r[8]||(r[8]=n("p",null,[t("Selects can use "),n("code",null,"chips"),t(" to display the selected data. Try adding your own tags below.")],-1)),e(E)]),_:1},8,["code"])]),_:1}),e(d,{cols:"12",md:"6"},{default:l(()=>[e(p,{title:"Expandable",code:o(Di)},{default:l(()=>[r[9]||(r[9]=n("p",null,[t("Chips can be combined with "),n("code",null,"v-menu"),t(" to enable a specific set of actions for a chip.")],-1)),e(W)]),_:1},8,["code"])]),_:1})]),_:1})}}});export{oe as default};
