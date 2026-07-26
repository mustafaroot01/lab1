import{_ as b}from"./AppAutocomplete.vue_vue_type_script_setup_true_lang-DIuHREKl.js";import{d as v,r as S,g as d,o as h,m as s,b1 as _,f as l,b as a,_ as T,dt as G,w as I,q as y,e as i,t as r}from"./main-BRN2JBrw.js";import{V as A}from"./VAutocomplete-F5n_epbS.js";import{a as F}from"./avatar-1-mrBiEG2D.js";import{a as D}from"./avatar-2-J3iDMrW6.js";import{a as W}from"./avatar-3-CCfT4M2Q.js";import{a as R}from"./avatar-4-CkT0aFRW.js";import{a as O}from"./avatar-5-roKDnu6B.js";import{a as H}from"./avatar-6-D6_LJK0H.js";import{a as L}from"./avatar-7-BqLdN_QF.js";import{a as E}from"./avatar-8-x-cqZxtu.js";import{a as U}from"./VList-BZwg0Aw7.js";import{V as P}from"./VChip-DIy4dUl9.js";import{V as w,a as u}from"./VRow-Cw23LpgL.js";import{_ as q}from"./AppCardCode.vue_vue_type_style_index_0_lang-f1mNd_Lg.js";import"./VSelect-GGs43KVK.js";import"./VTextField-B-SafXsh.js";import"./autofocus-Db38RUhk.js";import"./VCounter-BXsWeC_3.js";import"./VField-CXZn0NIc.js";import"./VDivider-BJRlhygd.js";import"./VCheckboxBtn-BmEGxGs1.js";import"./VSelectionControl-C_9VPf01.js";import"./VAvatar-C5ZVrAt4.js";import"./filter-CD5uhF84.js";import"./ssrBoot-gLLOKfTl.js";import"./VSlideGroup-DOzzYFQv.js";import"./vue3-perfect-scrollbar-h9R898_l.js";import"./VCard-_AzuU6xP.js";import"./VCardText-BORcZDbM.js";const $=v({__name:"DemoAutocompleteValidation",setup(g){const t=["foo","bar","fizz","buzz"],e=S(["foo"]),p=[o=>!!o.length||"Select at least one option."];return(o,m)=>{const c=b;return h(),d(c,{modelValue:s(e),"onUpdate:modelValue":m[0]||(m[0]=n=>_(e)?e.value=n:null),items:t,rules:p,placeholder:"Select Option",multiple:""},null,8,["modelValue"])}}}),z=v({__name:"DemoAutocompleteStateSelector",setup(g){const t=S(!1),e=S(null),p=["Alabama","Alaska","American Samoa","Arizona","Arkansas","California","Colorado","Connecticut","Delaware","District of Columbia","Federated States of Micronesia","Florida","Georgia","Guam","Hawaii","Idaho","Illinois","Indiana","Iowa","Kansas","Kentucky","Louisiana","Maine","Marshall Islands","Maryland","Massachusetts","Michigan","Minnesota","Mississippi","Missouri","Montana","Nebraska","Nevada","New Hampshire","New Jersey","New Mexico","New York","North Carolina","North Dakota","Northern Mariana Islands","Ohio","Oklahoma","Oregon","Palau","Pennsylvania","Puerto Rico","Rhode Island","South Carolina","South Dakota","Tennessee","Texas","Utah","Vermont","Virgin Island","Virginia","Washington","West Virginia","Wisconsin","Wyoming"];return(o,m)=>{const c=b;return h(),d(c,{modelValue:s(e),"onUpdate:modelValue":m[1]||(m[1]=n=>_(e)?e.value=n:null),hint:s(t)?"Click the icon to save":"Click the icon to edit",placeholder:"Select Your State",items:p,readonly:!s(t),label:`State — ${s(t)?"Editable":"Readonly"}`,"persistent-hint":"","prepend-icon":"tabler-building","menu-props":{maxHeight:"200px"}},{append:l(()=>[a(G,{mode:"out-in"},{default:l(()=>[(h(),d(T,{key:`icon-${s(t)}`,color:s(t)?"success":"info",icon:s(t)?"tabler-checks":"tabler-edit-circle",onClick:m[0]||(m[0]=n=>t.value=!s(t))},null,8,["color","icon"]))]),_:1})]),_:1},8,["modelValue","hint","readonly","label"])}}}),Y=v({__name:"DemoAutocompleteAsyncItems",setup(g){const t=S(!1),e=S(),p=S(null),o=["Alabama","Alaska","American Samoa","Arizona","Arkansas","California","Colorado","Connecticut","Delaware","District of Columbia","Federated States of Micronesia","Florida","Georgia","Guam","Hawaii","Idaho","Illinois","Indiana","Iowa","Kansas","Kentucky","Louisiana","Maine","Marshall Islands","Maryland","Massachusetts","Michigan","Minnesota","Mississippi","Missouri","Montana","Nebraska","Nevada","New Hampshire","New Jersey","New Mexico","New York","North Carolina","North Dakota","Northern Mariana Islands","Ohio","Oklahoma","Oregon","Palau","Pennsylvania","Puerto Rico","Rhode Island","South Carolina","South Dakota","Tennessee","Texas","Utah","Vermont","Virgin Island","Virginia","Washington","West Virginia","Wisconsin","Wyoming"],m=S(o),c=n=>{t.value=!0,setTimeout(()=>{m.value=o.filter(f=>(f||"").toLowerCase().includes((n||"").toLowerCase())),t.value=!1},500)};return I(e,n=>{n&&n!==p.value&&c(n)}),(n,f)=>(h(),d(A,{modelValue:s(p),"onUpdate:modelValue":f[0]||(f[0]=C=>_(p)?p.value=C:null),search:s(e),"onUpdate:search":f[1]||(f[1]=C=>_(e)?e.value=C:null),loading:s(t),items:s(m),placeholder:"Search for a state",label:"What state are you from?",variant:"underlined","menu-props":{maxHeight:"200px"}},null,8,["modelValue","search","loading","items"]))}}),j=v({__name:"DemoAutocompleteSlots",setup(g){const t=S(["Sandra Adams","Britta Holt"]),e=[{name:"Sandra Adams",group:"Group 1",avatar:F},{name:"Ali Connors",group:"Group 1",avatar:D},{name:"Trevor Hansen",group:"Group 1",avatar:W},{name:"Tucker Smith",group:"Group 1",avatar:R},{name:"Britta Holt",group:"Group 2",avatar:O},{name:"Jane Smith ",group:"Group 2",avatar:H},{name:"John Smith",group:"Group 2",avatar:L},{name:"Sandra Williams",group:"Group 2",avatar:E}];return(p,o)=>{const m=b;return h(),d(m,{modelValue:s(t),"onUpdate:modelValue":o[0]||(o[0]=c=>_(t)?t.value=c:null),chips:"","closable-chips":"",multiple:"",items:e,"item-title":"name","item-value":"name",placeholder:"Select User",label:"Select"},{chip:l(({props:c,item:n})=>[a(P,y(c,{"prepend-avatar":n.raw.avatar,text:n.raw.name}),null,16,["prepend-avatar","text"])]),item:l(({props:c,item:n})=>[a(U,y(c,{"prepend-avatar":n?.raw?.avatar,title:n?.raw?.name,subtitle:n?.raw?.group}),null,16,["prepend-avatar","title","subtitle"])]),_:1},8,["modelValue"])}}}),B=v({__name:"DemoAutocompleteCustomFilter",setup(g){const t=[{name:"Florida",abbr:"FL",id:1},{name:"Georgia",abbr:"GA",id:2},{name:"Nebraska",abbr:"NE",id:3},{name:"California",abbr:"CA",id:4},{name:"New York",abbr:"NY",id:5}];function e(p,o,m){const c=m.raw.name.toLowerCase(),n=m.raw.abbr.toLowerCase(),f=o.toLowerCase();return c.includes(f)||n.includes(f)}return(p,o)=>{const m=b;return h(),d(m,{label:"States",items:t,"custom-filter":e,"item-title":"name","item-value":"abbr",placeholder:"Select State"})}}}),J=v({__name:"DemoAutocompleteChips",setup(g){const t=["California","Colorado","Florida","Georgia","Texas","Wyoming"];return(e,p)=>{const o=b;return h(),d(o,{label:"States",items:t,placeholder:"Select State",chips:"",multiple:"","closable-chips":""})}}}),K=v({__name:"DemoAutocompleteClearable",setup(g){const t=["California","Colorado","Florida","Georgia","Texas","Wyoming"];return(e,p)=>{const o=b;return h(),d(o,{label:"States",items:t,multiple:"",placeholder:"Select State",clearable:""})}}}),X=v({__name:"DemoAutocompleteMultiple",setup(g){const t=["California","Colorado","Florida","Georgia","Texas","Wyoming"];return(e,p)=>{const o=b;return h(),d(o,{label:"States",items:t,placeholder:"Select State",multiple:"",eager:""})}}}),Q=v({__name:"DemoAutocompleteVariant",setup(g){const t=["California","Colorado","Florida","Georgia","Texas","Wyoming"];return(e,p)=>(h(),d(w,null,{default:l(()=>[a(u,{cols:"12",md:"6"},{default:l(()=>[a(A,{variant:"solo",label:"Solo",items:t,placeholder:"Select State"})]),_:1}),a(u,{cols:"12",md:"6"},{default:l(()=>[a(A,{variant:"outlined",label:"Outlined",placeholder:"Select State",items:t})]),_:1}),a(u,{cols:"12",md:"6"},{default:l(()=>[a(A,{variant:"underlined",label:"Underlined",placeholder:"Select State",items:t})]),_:1}),a(u,{cols:"12",md:"6"},{default:l(()=>[a(A,{variant:"filled",label:"Filled",placeholder:"Select State",items:t})]),_:1}),a(u,{cols:"12",md:"6"},{default:l(()=>[a(A,{variant:"plain",label:"Plain",placeholder:"Select State",items:t})]),_:1})]),_:1}))}}),Z=v({__name:"DemoAutocompleteDensity",setup(g){const t=S("Florida"),e=["California","Colorado","Florida","Georgia","Texas","Wyoming"];return(p,o)=>{const m=b;return h(),d(m,{modelValue:s(t),"onUpdate:modelValue":o[0]||(o[0]=c=>_(t)?t.value=c:null),label:"States",density:"compact",placeholder:"Select State",items:e},null,8,["modelValue"])}}}),ee=v({__name:"DemoAutocompleteBasic",setup(g){const t=["California","Colorado","Florida","Georgia","Texas","Wyoming"];return(e,p)=>{const o=b;return h(),d(o,{label:"States",items:t,placeholder:"Select State"})}}}),ae={ts:`<script setup lang="ts">
const loading = ref(false)
const search = ref()
const select = ref(null)

const states = [
  'Alabama',
  'Alaska',
  'American Samoa',
  'Arizona',
  'Arkansas',
  'California',
  'Colorado',
  'Connecticut',
  'Delaware',
  'District of Columbia',
  'Federated States of Micronesia',
  'Florida',
  'Georgia',
  'Guam',
  'Hawaii',
  'Idaho',
  'Illinois',
  'Indiana',
  'Iowa',
  'Kansas',
  'Kentucky',
  'Louisiana',
  'Maine',
  'Marshall Islands',
  'Maryland',
  'Massachusetts',
  'Michigan',
  'Minnesota',
  'Mississippi',
  'Missouri',
  'Montana',
  'Nebraska',
  'Nevada',
  'New Hampshire',
  'New Jersey',
  'New Mexico',
  'New York',
  'North Carolina',
  'North Dakota',
  'Northern Mariana Islands',
  'Ohio',
  'Oklahoma',
  'Oregon',
  'Palau',
  'Pennsylvania',
  'Puerto Rico',
  'Rhode Island',
  'South Carolina',
  'South Dakota',
  'Tennessee',
  'Texas',
  'Utah',
  'Vermont',
  'Virgin Island',
  'Virginia',
  'Washington',
  'West Virginia',
  'Wisconsin',
  'Wyoming',
]

const items = ref(states)

const querySelections = (query: string) => {
  loading.value = true

  // Simulated ajax query
  setTimeout(() => {
    items.value = states.filter(state => (state || '').toLowerCase().includes((query || '').toLowerCase()))
    loading.value = false
  }, 500)
}

watch(search, query => {
  query && query !== select.value && querySelections(query)
})
<\/script>

<template>
  <VAutocomplete
    v-model="select"
    v-model:search="search"
    :loading="loading"
    :items="items"
    placeholder="Search for a state"
    label="What state are you from?"
    variant="underlined"
    :menu-props="{ maxHeight: '200px' }"
  />
</template>
`,js:`<script setup>
const loading = ref(false)
const search = ref()
const select = ref(null)

const states = [
  'Alabama',
  'Alaska',
  'American Samoa',
  'Arizona',
  'Arkansas',
  'California',
  'Colorado',
  'Connecticut',
  'Delaware',
  'District of Columbia',
  'Federated States of Micronesia',
  'Florida',
  'Georgia',
  'Guam',
  'Hawaii',
  'Idaho',
  'Illinois',
  'Indiana',
  'Iowa',
  'Kansas',
  'Kentucky',
  'Louisiana',
  'Maine',
  'Marshall Islands',
  'Maryland',
  'Massachusetts',
  'Michigan',
  'Minnesota',
  'Mississippi',
  'Missouri',
  'Montana',
  'Nebraska',
  'Nevada',
  'New Hampshire',
  'New Jersey',
  'New Mexico',
  'New York',
  'North Carolina',
  'North Dakota',
  'Northern Mariana Islands',
  'Ohio',
  'Oklahoma',
  'Oregon',
  'Palau',
  'Pennsylvania',
  'Puerto Rico',
  'Rhode Island',
  'South Carolina',
  'South Dakota',
  'Tennessee',
  'Texas',
  'Utah',
  'Vermont',
  'Virgin Island',
  'Virginia',
  'Washington',
  'West Virginia',
  'Wisconsin',
  'Wyoming',
]

const items = ref(states)

const querySelections = query => {
  loading.value = true

  // Simulated ajax query
  setTimeout(() => {
    items.value = states.filter(state => (state || '').toLowerCase().includes((query || '').toLowerCase()))
    loading.value = false
  }, 500)
}

watch(search, query => {
  query && query !== select.value && querySelections(query)
})
<\/script>

<template>
  <VAutocomplete
    v-model="select"
    v-model:search="search"
    :loading="loading"
    :items="items"
    placeholder="Search for a state"
    label="What state are you from?"
    variant="underlined"
    :menu-props="{ maxHeight: '200px' }"
  />
</template>
`},te={ts:`<script setup lang="ts">
const items = ['California', 'Colorado', 'Florida', 'Georgia', 'Texas', 'Wyoming']
<\/script>

<template>
  <AppAutocomplete
    label="States"
    :items="items"
    placeholder="Select State"
  />
</template>
`,js:`<script setup>
const items = [
  'California',
  'Colorado',
  'Florida',
  'Georgia',
  'Texas',
  'Wyoming',
]
<\/script>

<template>
  <AppAutocomplete
    label="States"
    :items="items"
    placeholder="Select State"
  />
</template>
`},oe={ts:`<script setup lang="ts">
const items = ['California', 'Colorado', 'Florida', 'Georgia', 'Texas', 'Wyoming']
<\/script>

<template>
  <AppAutocomplete
    label="States"
    :items="items"
    placeholder="Select State"
    chips
    multiple
    closable-chips
  />
</template>
`,js:`<script setup>
const items = [
  'California',
  'Colorado',
  'Florida',
  'Georgia',
  'Texas',
  'Wyoming',
]
<\/script>

<template>
  <AppAutocomplete
    label="States"
    :items="items"
    placeholder="Select State"
    chips
    multiple
    closable-chips
  />
</template>
`},le={ts:`<script setup lang="ts">
const items = ['California', 'Colorado', 'Florida', 'Georgia', 'Texas', 'Wyoming']
<\/script>

<template>
  <AppAutocomplete
    label="States"
    :items="items"
    multiple
    placeholder="Select State"
    clearable
  />
</template>
`,js:`<script setup>
const items = [
  'California',
  'Colorado',
  'Florida',
  'Georgia',
  'Texas',
  'Wyoming',
]
<\/script>

<template>
  <AppAutocomplete
    label="States"
    :items="items"
    multiple
    placeholder="Select State"
    clearable
  />
</template>
`},ie={ts:`<script setup lang="ts">
const states = [
  { name: 'Florida', abbr: 'FL', id: 1 },
  { name: 'Georgia', abbr: 'GA', id: 2 },
  { name: 'Nebraska', abbr: 'NE', id: 3 },
  { name: 'California', abbr: 'CA', id: 4 },
  { name: 'New York', abbr: 'NY', id: 5 },
]

function customFilter(itemTitle: any, queryText: any, item: any) {
  const textOne = item.raw.name.toLowerCase()
  const textTwo = item.raw.abbr.toLowerCase()
  const searchText = queryText.toLowerCase()

  return textOne.includes(searchText) || textTwo.includes(searchText)
}
<\/script>

<template>
  <AppAutocomplete
    label="States"
    :items="states"
    :custom-filter="customFilter"
    item-title="name"
    item-value="abbr"
    placeholder="Select State"
  />
</template>
`,js:`<script setup>
const states = [
  {
    name: 'Florida',
    abbr: 'FL',
    id: 1,
  },
  {
    name: 'Georgia',
    abbr: 'GA',
    id: 2,
  },
  {
    name: 'Nebraska',
    abbr: 'NE',
    id: 3,
  },
  {
    name: 'California',
    abbr: 'CA',
    id: 4,
  },
  {
    name: 'New York',
    abbr: 'NY',
    id: 5,
  },
]

function customFilter(itemTitle, queryText, item) {
  const textOne = item.raw.name.toLowerCase()
  const textTwo = item.raw.abbr.toLowerCase()
  const searchText = queryText.toLowerCase()
  
  return textOne.includes(searchText) || textTwo.includes(searchText)
}
<\/script>

<template>
  <AppAutocomplete
    label="States"
    :items="states"
    :custom-filter="customFilter"
    item-title="name"
    item-value="abbr"
    placeholder="Select State"
  />
</template>
`},se={ts:`<script setup lang="ts">
const select = ref('Florida')
const items = ['California', 'Colorado', 'Florida', 'Georgia', 'Texas', 'Wyoming']
<\/script>

<template>
  <AppAutocomplete
    v-model="select"
    label="States"
    density="compact"
    placeholder="Select State"
    :items="items"
  />
</template>
`,js:`<script setup>
const select = ref('Florida')

const items = [
  'California',
  'Colorado',
  'Florida',
  'Georgia',
  'Texas',
  'Wyoming',
]
<\/script>

<template>
  <AppAutocomplete
    v-model="select"
    label="States"
    density="compact"
    placeholder="Select State"
    :items="items"
  />
</template>
`},re={ts:`<script setup lang="ts">
const items = ['California', 'Colorado', 'Florida', 'Georgia', 'Texas', 'Wyoming']
<\/script>

<template>
  <AppAutocomplete
    label="States"
    :items="items"
    placeholder="Select State"
    multiple
    eager
  />
</template>
`,js:`<script setup>
const items = [
  'California',
  'Colorado',
  'Florida',
  'Georgia',
  'Texas',
  'Wyoming',
]
<\/script>

<template>
  <AppAutocomplete
    label="States"
    :items="items"
    placeholder="Select State"
    multiple
    eager
  />
</template>
`},ne={ts:`<script setup lang="ts">
import avatar1 from '@images/avatars/avatar-1.png'
import avatar2 from '@images/avatars/avatar-2.png'
import avatar3 from '@images/avatars/avatar-3.png'
import avatar4 from '@images/avatars/avatar-4.png'
import avatar5 from '@images/avatars/avatar-5.png'
import avatar6 from '@images/avatars/avatar-6.png'
import avatar7 from '@images/avatars/avatar-7.png'
import avatar8 from '@images/avatars/avatar-8.png'

const friends = ref(['Sandra Adams', 'Britta Holt'])

const people = [
  { name: 'Sandra Adams', group: 'Group 1', avatar: avatar1 },
  { name: 'Ali Connors', group: 'Group 1', avatar: avatar2 },
  { name: 'Trevor Hansen', group: 'Group 1', avatar: avatar3 },
  { name: 'Tucker Smith', group: 'Group 1', avatar: avatar4 },
  { name: 'Britta Holt', group: 'Group 2', avatar: avatar5 },
  { name: 'Jane Smith ', group: 'Group 2', avatar: avatar6 },
  { name: 'John Smith', group: 'Group 2', avatar: avatar7 },
  { name: 'Sandra Williams', group: 'Group 2', avatar: avatar8 },
]
<\/script>

<template>
  <AppAutocomplete
    v-model="friends"
    chips
    closable-chips
    multiple
    :items="people"
    item-title="name"
    item-value="name"
    placeholder="Select User"
    label="Select"
  >
    <template #chip="{ props, item }">
      <VChip
        v-bind="props"
        :prepend-avatar="item.raw.avatar"
        :text="item.raw.name"
      />
    </template>

    <template #item="{ props, item }">
      <VListItem
        v-bind="props"
        :prepend-avatar="item?.raw?.avatar"
        :title="item?.raw?.name"
        :subtitle="item?.raw?.group"
      />
    </template>
  </AppAutocomplete>
</template>
`,js:`<script setup>
import avatar1 from '@images/avatars/avatar-1.png'
import avatar2 from '@images/avatars/avatar-2.png'
import avatar3 from '@images/avatars/avatar-3.png'
import avatar4 from '@images/avatars/avatar-4.png'
import avatar5 from '@images/avatars/avatar-5.png'
import avatar6 from '@images/avatars/avatar-6.png'
import avatar7 from '@images/avatars/avatar-7.png'
import avatar8 from '@images/avatars/avatar-8.png'

const friends = ref([
  'Sandra Adams',
  'Britta Holt',
])

const people = [
  {
    name: 'Sandra Adams',
    group: 'Group 1',
    avatar: avatar1,
  },
  {
    name: 'Ali Connors',
    group: 'Group 1',
    avatar: avatar2,
  },
  {
    name: 'Trevor Hansen',
    group: 'Group 1',
    avatar: avatar3,
  },
  {
    name: 'Tucker Smith',
    group: 'Group 1',
    avatar: avatar4,
  },
  {
    name: 'Britta Holt',
    group: 'Group 2',
    avatar: avatar5,
  },
  {
    name: 'Jane Smith ',
    group: 'Group 2',
    avatar: avatar6,
  },
  {
    name: 'John Smith',
    group: 'Group 2',
    avatar: avatar7,
  },
  {
    name: 'Sandra Williams',
    group: 'Group 2',
    avatar: avatar8,
  },
]
<\/script>

<template>
  <AppAutocomplete
    v-model="friends"
    chips
    closable-chips
    multiple
    :items="people"
    item-title="name"
    item-value="name"
    placeholder="Select User"
    label="Select"
  >
    <template #chip="{ props, item }">
      <VChip
        v-bind="props"
        :prepend-avatar="item.raw.avatar"
        :text="item.raw.name"
      />
    </template>

    <template #item="{ props, item }">
      <VListItem
        v-bind="props"
        :prepend-avatar="item?.raw?.avatar"
        :title="item?.raw?.name"
        :subtitle="item?.raw?.group"
      />
    </template>
  </AppAutocomplete>
</template>
`},me={ts:`<script setup lang="ts">
const isEditing = ref(false)
const selectedState = ref(null)

const states = [
  'Alabama',
  'Alaska',
  'American Samoa',
  'Arizona',
  'Arkansas',
  'California',
  'Colorado',
  'Connecticut',
  'Delaware',
  'District of Columbia',
  'Federated States of Micronesia',
  'Florida',
  'Georgia',
  'Guam',
  'Hawaii',
  'Idaho',
  'Illinois',
  'Indiana',
  'Iowa',
  'Kansas',
  'Kentucky',
  'Louisiana',
  'Maine',
  'Marshall Islands',
  'Maryland',
  'Massachusetts',
  'Michigan',
  'Minnesota',
  'Mississippi',
  'Missouri',
  'Montana',
  'Nebraska',
  'Nevada',
  'New Hampshire',
  'New Jersey',
  'New Mexico',
  'New York',
  'North Carolina',
  'North Dakota',
  'Northern Mariana Islands',
  'Ohio',
  'Oklahoma',
  'Oregon',
  'Palau',
  'Pennsylvania',
  'Puerto Rico',
  'Rhode Island',
  'South Carolina',
  'South Dakota',
  'Tennessee',
  'Texas',
  'Utah',
  'Vermont',
  'Virgin Island',
  'Virginia',
  'Washington',
  'West Virginia',
  'Wisconsin',
  'Wyoming',
]
<\/script>

<template>
  <AppAutocomplete
    v-model="selectedState"
    :hint="!isEditing ? 'Click the icon to edit' : 'Click the icon to save'"
    placeholder="Select Your State"
    :items="states"
    :readonly="!isEditing"
    :label="\`State — \${isEditing ? 'Editable' : 'Readonly'}\`"
    persistent-hint
    prepend-icon="tabler-building"
    :menu-props="{ maxHeight: '200px' }"
  >
    <template #append>
      <VSlideXReverseTransition mode="out-in">
        <VIcon
          :key="\`icon-\${isEditing}\`"
          :color="isEditing ? 'success' : 'info'"
          :icon="isEditing ? 'tabler-checks' : 'tabler-edit-circle'"
          @click="isEditing = !isEditing"
        />
      </VSlideXReverseTransition>
    </template>
  </AppAutocomplete>
</template>
`,js:`<script setup>
const isEditing = ref(false)
const selectedState = ref(null)

const states = [
  'Alabama',
  'Alaska',
  'American Samoa',
  'Arizona',
  'Arkansas',
  'California',
  'Colorado',
  'Connecticut',
  'Delaware',
  'District of Columbia',
  'Federated States of Micronesia',
  'Florida',
  'Georgia',
  'Guam',
  'Hawaii',
  'Idaho',
  'Illinois',
  'Indiana',
  'Iowa',
  'Kansas',
  'Kentucky',
  'Louisiana',
  'Maine',
  'Marshall Islands',
  'Maryland',
  'Massachusetts',
  'Michigan',
  'Minnesota',
  'Mississippi',
  'Missouri',
  'Montana',
  'Nebraska',
  'Nevada',
  'New Hampshire',
  'New Jersey',
  'New Mexico',
  'New York',
  'North Carolina',
  'North Dakota',
  'Northern Mariana Islands',
  'Ohio',
  'Oklahoma',
  'Oregon',
  'Palau',
  'Pennsylvania',
  'Puerto Rico',
  'Rhode Island',
  'South Carolina',
  'South Dakota',
  'Tennessee',
  'Texas',
  'Utah',
  'Vermont',
  'Virgin Island',
  'Virginia',
  'Washington',
  'West Virginia',
  'Wisconsin',
  'Wyoming',
]
<\/script>

<template>
  <AppAutocomplete
    v-model="selectedState"
    :hint="!isEditing ? 'Click the icon to edit' : 'Click the icon to save'"
    placeholder="Select Your State"
    :items="states"
    :readonly="!isEditing"
    :label="\`State — \${isEditing ? 'Editable' : 'Readonly'}\`"
    persistent-hint
    prepend-icon="tabler-building"
    :menu-props="{ maxHeight: '200px' }"
  >
    <template #append>
      <VSlideXReverseTransition mode="out-in">
        <VIcon
          :key="\`icon-\${isEditing}\`"
          :color="isEditing ? 'success' : 'info'"
          :icon="isEditing ? 'tabler-checks' : 'tabler-edit-circle'"
          @click="isEditing = !isEditing"
        />
      </VSlideXReverseTransition>
    </template>
  </AppAutocomplete>
</template>
`},pe={ts:`<script setup lang="ts">
const items = ['foo', 'bar', 'fizz', 'buzz']
const values = ref(['foo'])
const nameRules = [(v: string) => !!v.length || 'Select at least one option.']
<\/script>

<template>
  <AppAutocomplete
    v-model="values"
    :items="items"
    :rules="nameRules"
    placeholder="Select Option"
    multiple
  />
</template>
`,js:`<script setup>
const items = [
  'foo',
  'bar',
  'fizz',
  'buzz',
]

const values = ref(['foo'])
const nameRules = [v => !!v.length || 'Select at least one option.']
<\/script>

<template>
  <AppAutocomplete
    v-model="values"
    :items="items"
    :rules="nameRules"
    placeholder="Select Option"
    multiple
  />
</template>
`},ce={ts:`<script setup lang="ts">
const items = ['California', 'Colorado', 'Florida', 'Georgia', 'Texas', 'Wyoming']
<\/script>

<template>
  <VRow>
    <VCol
      cols="12"
      md="6"
    >
      <!-- 👉 solo variant  -->
      <VAutocomplete
        variant="solo"
        label="Solo"
        :items="items"
        placeholder="Select State"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <!-- 👉 outlined variant -->
      <VAutocomplete
        variant="outlined"
        label="Outlined"
        placeholder="Select State"
        :items="items"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <!-- 👉 underlined variant -->
      <VAutocomplete
        variant="underlined"
        label="Underlined"
        placeholder="Select State"
        :items="items"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <!-- 👉 filled variant  -->
      <VAutocomplete
        variant="filled"
        label="Filled"
        placeholder="Select State"
        :items="items"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <!--  👉 plain variant -->
      <VAutocomplete
        variant="plain"
        label="Plain"
        placeholder="Select State"
        :items="items"
      />
    </VCol>
  </VRow>
</template>
`,js:`<script setup>
const items = [
  'California',
  'Colorado',
  'Florida',
  'Georgia',
  'Texas',
  'Wyoming',
]
<\/script>

<template>
  <VRow>
    <VCol
      cols="12"
      md="6"
    >
      <!-- 👉 solo variant  -->
      <VAutocomplete
        variant="solo"
        label="Solo"
        :items="items"
        placeholder="Select State"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <!-- 👉 outlined variant -->
      <VAutocomplete
        variant="outlined"
        label="Outlined"
        placeholder="Select State"
        :items="items"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <!-- 👉 underlined variant -->
      <VAutocomplete
        variant="underlined"
        label="Underlined"
        placeholder="Select State"
        :items="items"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <!-- 👉 filled variant  -->
      <VAutocomplete
        variant="filled"
        label="Filled"
        placeholder="Select State"
        :items="items"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <!--  👉 plain variant -->
      <VAutocomplete
        variant="plain"
        label="Plain"
        placeholder="Select State"
        :items="items"
      />
    </VCol>
  </VRow>
</template>
`},Pe=v({__name:"autocomplete",setup(g){return(t,e)=>{const p=ee,o=q,m=Z,c=Q,n=X,f=K,C=J,V=B,x=j,k=Y,M=z,N=$;return h(),d(w,{class:"match-height"},{default:l(()=>[a(u,{cols:"12",md:"6"},{default:l(()=>[a(o,{title:"Basic",code:s(te)},{default:l(()=>[e[0]||(e[0]=i("p",null,[r(" The "),i("code",null," v-autocomplete "),r(" component offers simple and flexible type-ahead functionality. This is useful when searching large sets of data or even dynamically fetching information from an API. ")],-1)),a(p)]),_:1},8,["code"])]),_:1}),a(u,{cols:"12",md:"6"},{default:l(()=>[a(o,{title:"Density",code:s(se)},{default:l(()=>[e[1]||(e[1]=i("p",null,[r(" You can use "),i("code",null," density "),r(" prop to adjusts vertical spacing within the component. Available options are: "),i("code",null,"default"),r(", "),i("code",null,"comfortable"),r(", and "),i("code",null,"compact"),r(". ")],-1)),a(m)]),_:1},8,["code"])]),_:1}),a(u,{cols:"12",md:"12"},{default:l(()=>[a(o,{title:"Variant",code:s(ce)},{default:l(()=>[e[2]||(e[2]=i("p",null,[r("Use "),i("code",null,"Solo"),r(", "),i("code",null,"Outlined"),r(", "),i("code",null,"Underlined"),r(", "),i("code",null,"Filled"),r(" and "),i("code",null,"Plain"),r(" options of "),i("code",null,"variant"),r(" prop to change the look of Autocomplete. ")],-1)),a(c)]),_:1},8,["code"])]),_:1}),a(u,{cols:"12",md:"6"},{default:l(()=>[a(o,{title:"Multiple",code:s(re)},{default:l(()=>[e[3]||(e[3]=i("p",null,[r("Use "),i("code",null,"multiple"),r(" prop to select multiple. Accepts array for value")],-1)),a(n)]),_:1},8,["code"])]),_:1}),a(u,{cols:"12",md:"6"},{default:l(()=>[a(o,{title:"Clearable",code:s(le)},{default:l(()=>[e[4]||(e[4]=i("p",null,[r("Use "),i("code",null,"clearable"),r(" prop to add input clear functionality.")],-1)),a(f)]),_:1},8,["code"])]),_:1}),a(u,{cols:"12",md:"6"},{default:l(()=>[a(o,{title:"Chips",code:s(oe)},{default:l(()=>[e[5]||(e[5]=i("p",null,[r("Use "),i("code",null," chips "),r(" prop to use chips in select.")],-1)),a(C)]),_:1},8,["code"])]),_:1}),a(u,{cols:"12",md:"6"},{default:l(()=>[a(o,{title:"Custom-Filter",code:s(ie)},{default:l(()=>[e[6]||(e[6]=i("p",null,[r("The "),i("code",null," custom-filter "),r(" prop can be used to filter each individual item with custom logic.In example we will filter state based on their name and abbreviations ")],-1)),a(V)]),_:1},8,["code"])]),_:1}),a(u,{cols:"12",md:"6"},{default:l(()=>[a(o,{title:"Slots",code:s(ne)},{default:l(()=>[e[7]||(e[7]=i("p",null,"With the power of slots, you can customize the visual output of the select. In this example we add a profile picture for both the chips and list items using their props. ",-1)),a(x)]),_:1},8,["code"])]),_:1}),a(u,{cols:"12",md:"6"},{default:l(()=>[a(o,{title:"Async items",code:s(ae)},{default:l(()=>[e[8]||(e[8]=i("p",null,"Sometimes you need to load data externally based upon a search query. ",-1)),a(k)]),_:1},8,["code"])]),_:1}),a(u,{cols:"12",md:"6"},{default:l(()=>[a(o,{title:"State Selector",code:s(me)},{default:l(()=>[e[9]||(e[9]=i("p",null,"Using a combination of v-autocomplete slots and transitions, you can create a stylish toggle able autocomplete field such as below state selector.",-1)),a(M)]),_:1},8,["code"])]),_:1}),a(u,{cols:"12",md:"6"},{default:l(()=>[a(o,{title:"validation",code:s(pe)},{default:l(()=>[e[10]||(e[10]=i("p",null,[r("Use "),i("code",null,"rules"),r(" prop to validate autocomplete. Accepts a mixed array of types function, boolean and string. Functions pass an input value as an argument and must return either true / false or a string containing an error message.")],-1)),a(N)]),_:1},8,["code"])]),_:1})]),_:1})}}});export{Pe as default};
