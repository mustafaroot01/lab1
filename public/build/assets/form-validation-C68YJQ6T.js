import{r as u,b as O,i as D,a as Y,c as x,l as $,p as S,d as j,f as I,e as N,u as J}from"./validators-BJEf8OaU.js";import{d as y,r as i,g,o as P,m as e,ba as U,f as o,b as l,b1 as V,ah as B,t as q,e as L}from"./main-BRN2JBrw.js";import{_ as E}from"./AppTextField.vue_vue_type_script_setup_true_lang-Dprt9PnY.js";import{V as k}from"./VForm-COS15z-9.js";import{a as d,V as A}from"./VRow-Cw23LpgL.js";import{_ as z}from"./AppCardCode.vue_vue_type_style_index_0_lang-f1mNd_Lg.js";import"./helpers-BGv4x_9E.js";import"./VTextField-B-SafXsh.js";import"./autofocus-Db38RUhk.js";import"./VCounter-BXsWeC_3.js";import"./VField-CXZn0NIc.js";import"./vue3-perfect-scrollbar-h9R898_l.js";import"./VCard-_AzuU6xP.js";import"./VAvatar-C5ZVrAt4.js";import"./VCardText-BORcZDbM.js";import"./VDivider-BJRlhygd.js";const G=y({__name:"DemoFormValidationValidationTypes",setup(T){const w=i(""),c=i(""),F=i(""),n=i(""),p=i(""),f=i(""),h=i(""),m=i(""),s=i(""),v=i(""),b=i(""),R=i(""),M=i();return(r,a)=>{const C=E;return P(),g(e(k),{ref_key:"refForm",ref:M,onSubmit:a[13]||(a[13]=U(()=>{},["prevent"]))},{default:o(()=>[l(A,null,{default:o(()=>[l(d,{cols:"12",md:"6"},{default:o(()=>[l(C,{modelValue:e(w),"onUpdate:modelValue":a[0]||(a[0]=t=>V(w)?w.value=t:null),"persistent-placeholder":"",placeholder:"This field is required",rules:["requiredValidator"in r?r.requiredValidator:e(u)]},null,8,["modelValue","rules"])]),_:1}),l(d,{cols:"12",md:"6"},{default:o(()=>[l(C,{modelValue:e(c),"onUpdate:modelValue":a[1]||(a[1]=t=>V(c)?c.value=t:null),"persistent-placeholder":"",placeholder:"Enter Number between 10 & 20",rules:["requiredValidator"in r?r.requiredValidator:e(u),("betweenValidator"in r?r.betweenValidator:e(O))(e(c),10,20)]},null,8,["modelValue","rules"])]),_:1}),l(d,{cols:"12",md:"6"},{default:o(()=>[l(C,{modelValue:e(F),"onUpdate:modelValue":a[2]||(a[2]=t=>V(F)?F.value=t:null),"persistent-placeholder":"",placeholder:"Must only consist of numbers",rules:["requiredValidator"in r?r.requiredValidator:e(u),"integerValidator"in r?r.integerValidator:e(D)]},null,8,["modelValue","rules"])]),_:1}),l(d,{cols:"12",md:"6"},{default:o(()=>[l(C,{modelValue:e(n),"onUpdate:modelValue":a[3]||(a[3]=t=>V(n)?n.value=t:null),"persistent-placeholder":"",placeholder:"Must match the specified regular expression : ^([0-9]+)$ - numbers only",rules:["requiredValidator"in r?r.requiredValidator:e(u),("regexValidator"in r?r.regexValidator:e(Y))(e(n),"^([0-9]+)$")]},null,8,["modelValue","rules"])]),_:1}),l(d,{cols:"12",md:"6"},{default:o(()=>[l(C,{modelValue:e(p),"onUpdate:modelValue":a[4]||(a[4]=t=>V(p)?p.value=t:null),"persistent-placeholder":"",placeholder:"Only alphabetic characters",rules:["requiredValidator"in r?r.requiredValidator:e(u),"alphaValidator"in r?r.alphaValidator:e(x)]},null,8,["modelValue","rules"])]),_:1}),l(d,{cols:"12",md:"6"},{default:o(()=>[l(C,{modelValue:e(f),"onUpdate:modelValue":a[5]||(a[5]=t=>V(f)?f.value=t:null),"persistent-placeholder":"",placeholder:"Length must be exactly 3 characters.",rules:["requiredValidator"in r?r.requiredValidator:e(u),("lengthValidator"in r?r.lengthValidator:e($))(e(f),3)]},null,8,["modelValue","rules"])]),_:1}),l(d,{cols:"12",md:"6"},{default:o(()=>[l(C,{modelValue:e(h),"onUpdate:modelValue":a[6]||(a[6]=t=>V(h)?h.value=t:null),"persistent-placeholder":"",placeholder:"Password Input Field",type:"password",rules:["requiredValidator"in r?r.requiredValidator:e(u),"passwordValidator"in r?r.passwordValidator:e(S)],autocomplete:"on"},null,8,["modelValue","rules"])]),_:1}),l(d,{cols:"12",md:"6"},{default:o(()=>[l(C,{modelValue:e(m),"onUpdate:modelValue":a[7]||(a[7]=t=>V(m)?m.value=t:null),"persistent-placeholder":"",placeholder:"The digits field must be numeric and exactly contain 3 digits",rules:["requiredValidator"in r?r.requiredValidator:e(u),("lengthValidator"in r?r.lengthValidator:e($))(e(m),3),"integerValidator"in r?r.integerValidator:e(D)]},null,8,["modelValue","rules"])]),_:1}),l(d,{cols:"12",md:"6"},{default:o(()=>[l(C,{modelValue:e(s),"onUpdate:modelValue":a[8]||(a[8]=t=>V(s)?s.value=t:null),"persistent-placeholder":"",placeholder:"Repeat password must match",type:"password",rules:["requiredValidator"in r?r.requiredValidator:e(u),("confirmedValidator"in r?r.confirmedValidator:e(j))(e(s),e(h))],autocomplete:"on"},null,8,["modelValue","rules"])]),_:1}),l(d,{cols:"12",md:"6"},{default:o(()=>[l(C,{modelValue:e(v),"onUpdate:modelValue":a[9]||(a[9]=t=>V(v)?v.value=t:null),"persistent-placeholder":"",placeholder:"Only alphabetic characters, numbers, dashes or underscores",rules:["requiredValidator"in r?r.requiredValidator:e(u),"alphaDashValidator"in r?r.alphaDashValidator:e(I)]},null,8,["modelValue","rules"])]),_:1}),l(d,{cols:"12",md:"6"},{default:o(()=>[l(C,{modelValue:e(b),"onUpdate:modelValue":a[10]||(a[10]=t=>V(b)?b.value=t:null),"persistent-placeholder":"",placeholder:"Must be a valid email",rules:["requiredValidator"in r?r.requiredValidator:e(u),"emailValidator"in r?r.emailValidator:e(N)]},null,8,["modelValue","rules"])]),_:1}),l(d,{cols:"12",md:"6"},{default:o(()=>[l(C,{modelValue:e(R),"onUpdate:modelValue":a[11]||(a[11]=t=>V(R)?R.value=t:null),"persistent-placeholder":"",placeholder:"Must be a valid url",rules:["requiredValidator"in r?r.requiredValidator:e(u),"urlValidator"in r?r.urlValidator:e(J)]},null,8,["modelValue","rules"])]),_:1}),l(d,{cols:"12"},{default:o(()=>[l(B,{type:"submit",onClick:a[12]||(a[12]=t=>e(M)?.validate())},{default:o(()=>[...a[14]||(a[14]=[q(" Submit ",-1)])]),_:1})]),_:1})]),_:1})]),_:1},512)}}}),H=y({__name:"DemoFormValidationValidatingMultipleRules",setup(T){const w=i(),c=i(),F=i(),n=i(),p=i(),f=i(!1),h=i(!1);return(m,s)=>{const v=E;return P(),g(e(k),{ref_key:"refForm",ref:F,onSubmit:U(()=>{},["prevent"])},{default:o(()=>[l(A,null,{default:o(()=>[l(d,{cols:"12",md:"6"},{default:o(()=>[l(v,{modelValue:e(w),"onUpdate:modelValue":s[0]||(s[0]=b=>V(w)?w.value=b:null),label:"Name",placeholder:"Your Name",rules:["requiredValidator"in m?m.requiredValidator:e(u)]},null,8,["modelValue","rules"])]),_:1}),l(d,{cols:"12",md:"6"},{default:o(()=>[l(v,{modelValue:e(c),"onUpdate:modelValue":s[1]||(s[1]=b=>V(c)?c.value=b:null),label:"Email",placeholder:"Your Email",rules:["requiredValidator"in m?m.requiredValidator:e(u),"emailValidator"in m?m.emailValidator:e(N)]},null,8,["modelValue","rules"])]),_:1}),l(d,{cols:"12",md:"6"},{default:o(()=>[l(v,{modelValue:e(n),"onUpdate:modelValue":s[2]||(s[2]=b=>V(n)?n.value=b:null),label:"Password",type:e(f)?"text":"password","append-inner-icon":e(f)?"tabler-eye-off":"tabler-eye",placeholder:"Enter Password",rules:["requiredValidator"in m?m.requiredValidator:e(u),"passwordValidator"in m?m.passwordValidator:e(S)],autocomplete:"on","onClick:appendInner":s[3]||(s[3]=b=>f.value=!e(f))},null,8,["modelValue","type","append-inner-icon","rules"])]),_:1}),l(d,{cols:"12",md:"6"},{default:o(()=>[l(v,{modelValue:e(p),"onUpdate:modelValue":s[4]||(s[4]=b=>V(p)?p.value=b:null),label:"Confirm Password",autocomplete:"confirm-password",type:e(h)?"text":"password",placeholder:"Confirm Password","append-inner-icon":e(p)?"tabler-eye-off":"tabler-eye",rules:["requiredValidator"in m?m.requiredValidator:e(u),("confirmedValidator"in m?m.confirmedValidator:e(j))(e(p),e(n))],"onClick:appendInner":s[5]||(s[5]=b=>h.value=!e(h))},null,8,["modelValue","type","append-inner-icon","rules"])]),_:1}),l(d,{cols:"12"},{default:o(()=>[l(B,{type:"submit",onClick:s[6]||(s[6]=b=>e(F)?.validate())},{default:o(()=>[...s[7]||(s[7]=[q(" Submit ",-1)])]),_:1})]),_:1})]),_:1})]),_:1},512)}}}),K=y({__name:"DemoFormValidationSimpleFormValidation",setup(T){const w=i(""),c=i(""),F=i();return(n,p)=>{const f=E;return P(),g(e(k),{ref_key:"refForm",ref:F,onSubmit:U(()=>{},["prevent"])},{default:o(()=>[l(A,null,{default:o(()=>[l(d,{cols:"12",md:"6"},{default:o(()=>[l(f,{modelValue:e(w),"onUpdate:modelValue":p[0]||(p[0]=h=>V(w)?w.value=h:null),label:"First Name",placeholder:"John",rules:["requiredValidator"in n?n.requiredValidator:e(u)]},null,8,["modelValue","rules"])]),_:1}),l(d,{cols:"12",md:"6"},{default:o(()=>[l(f,{modelValue:e(c),"onUpdate:modelValue":p[1]||(p[1]=h=>V(c)?c.value=h:null),label:"Email",placeholder:"john@email.com",rules:["requiredValidator"in n?n.requiredValidator:e(u),"emailValidator"in n?n.emailValidator:e(N)]},null,8,["modelValue","rules"])]),_:1}),l(d,{cols:"12"},{default:o(()=>[l(B,{type:"submit",onClick:p[2]||(p[2]=h=>e(F)?.validate())},{default:o(()=>[...p[3]||(p[3]=[q(" Submit ",-1)])]),_:1})]),_:1})]),_:1})]),_:1},512)}}}),Q={ts:`<script lang="ts" setup>
import { VForm } from 'vuetify/components/VForm'

const firstName = ref('')
const email = ref('')

const refForm = ref<VForm>()
<\/script>

<template>
  <VForm
    ref="refForm"
    @submit.prevent="() => {}"
  >
    <VRow>
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="firstName"
          label="First Name"
          placeholder="John"
          :rules="[requiredValidator]"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="email"
          label="Email"
          placeholder="john@email.com"
          :rules="[requiredValidator, emailValidator]"
        />
      </VCol>

      <VCol cols="12">
        <VBtn
          type="submit"
          @click="refForm?.validate()"
        >
          Submit
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`,js:`<script setup>
import { VForm } from 'vuetify/components/VForm'

const firstName = ref('')
const email = ref('')
const refForm = ref()
<\/script>

<template>
  <VForm
    ref="refForm"
    @submit.prevent="() => {}"
  >
    <VRow>
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="firstName"
          label="First Name"
          placeholder="John"
          :rules="[requiredValidator]"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="email"
          label="Email"
          placeholder="john@email.com"
          :rules="[requiredValidator, emailValidator]"
        />
      </VCol>

      <VCol cols="12">
        <VBtn
          type="submit"
          @click="refForm?.validate()"
        >
          Submit
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`},W={ts:`<script lang="ts" setup>
import { VForm } from 'vuetify/components/VForm'

const name = ref()
const email = ref()
const refForm = ref<VForm>()
const password = ref()
const confirmPassword = ref()
const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
<\/script>

<template>
  <VForm
    ref="refForm"
    @submit.prevent="() => {}"
  >
    <VRow>
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="name"
          label="Name"
          placeholder="Your Name"
          :rules="[requiredValidator]"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="email"
          label="Email"
          placeholder="Your Email"
          :rules="[requiredValidator, emailValidator]"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="password"
          label="Password"
          :type="isPasswordVisible ? 'text' : 'password'"
          :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
          placeholder="Enter Password"
          :rules="[requiredValidator, passwordValidator]"
          autocomplete="on"
          @click:append-inner="isPasswordVisible = !isPasswordVisible"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="confirmPassword"
          label="Confirm Password"
          autocomplete="confirm-password"
          :type="isConfirmPasswordVisible ? 'text' : 'password'"
          placeholder="Confirm Password"
          :append-inner-icon="confirmPassword ? 'tabler-eye-off' : 'tabler-eye'"
          :rules="[requiredValidator, confirmedValidator(confirmPassword, password)]"
          @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
        />
      </VCol>

      <VCol cols="12">
        <VBtn
          type="submit"
          @click="refForm?.validate()"
        >
          Submit
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`,js:`<script setup>
import { VForm } from 'vuetify/components/VForm'

const name = ref()
const email = ref()
const refForm = ref()
const password = ref()
const confirmPassword = ref()
const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
<\/script>

<template>
  <VForm
    ref="refForm"
    @submit.prevent="() => {}"
  >
    <VRow>
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="name"
          label="Name"
          placeholder="Your Name"
          :rules="[requiredValidator]"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="email"
          label="Email"
          placeholder="Your Email"
          :rules="[requiredValidator, emailValidator]"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="password"
          label="Password"
          :type="isPasswordVisible ? 'text' : 'password'"
          :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
          placeholder="Enter Password"
          :rules="[requiredValidator, passwordValidator]"
          autocomplete="on"
          @click:append-inner="isPasswordVisible = !isPasswordVisible"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="confirmPassword"
          label="Confirm Password"
          autocomplete="confirm-password"
          :type="isConfirmPasswordVisible ? 'text' : 'password'"
          placeholder="Confirm Password"
          :append-inner-icon="confirmPassword ? 'tabler-eye-off' : 'tabler-eye'"
          :rules="[requiredValidator, confirmedValidator(confirmPassword, password)]"
          @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
        />
      </VCol>

      <VCol cols="12">
        <VBtn
          type="submit"
          @click="refForm?.validate()"
        >
          Submit
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`},X={ts:`<script lang="ts" setup>
import { VForm } from 'vuetify/components/VForm'

const requiredField = ref('')
const numberBetween10to20 = ref('')
const onlyConsistNumber = ref('')
const matchRegularEx = ref('')
const onlyAlphabeticCharacters = ref('')
const specifiedLength = ref('')
const password = ref('')
const digits = ref('')
const repeatPassword = ref('')
const onlyAlphabeticNumbersDashesUnderscores = ref('')
const email = ref('')
const validURL = ref('')
const refForm = ref<VForm>()
<\/script>

<template>
  <VForm
    ref="refForm"
    @submit.prevent
  >
    <VRow>
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="requiredField"
          persistent-placeholder
          placeholder="This field is required"
          :rules="[requiredValidator]"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="numberBetween10to20"
          persistent-placeholder
          placeholder="Enter Number between 10 & 20"
          :rules="[requiredValidator, betweenValidator(numberBetween10to20, 10, 20)]"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="onlyConsistNumber"
          persistent-placeholder
          placeholder="Must only consist of numbers"
          :rules="[requiredValidator, integerValidator]"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="matchRegularEx"
          persistent-placeholder
          placeholder="Must match the specified regular expression : ^([0-9]+)$ - numbers only"
          :rules="[requiredValidator, regexValidator(matchRegularEx, '^([0-9]+)$')]"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="onlyAlphabeticCharacters"
          persistent-placeholder
          placeholder="Only alphabetic characters"
          :rules="[requiredValidator, alphaValidator]"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="specifiedLength"
          persistent-placeholder
          placeholder="Length must be exactly 3 characters."
          :rules="[requiredValidator, lengthValidator(specifiedLength, 3)]"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="password"
          persistent-placeholder
          placeholder="Password Input Field"
          type="password"
          :rules="[requiredValidator, passwordValidator]"
          autocomplete="on"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="digits"
          persistent-placeholder
          placeholder="The digits field must be numeric and exactly contain 3 digits"
          :rules="[requiredValidator, lengthValidator(digits, 3), integerValidator]"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="repeatPassword"
          persistent-placeholder
          placeholder="Repeat password must match"
          type="password"
          :rules="[requiredValidator, confirmedValidator(repeatPassword, password)]"
          autocomplete="on"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="onlyAlphabeticNumbersDashesUnderscores"
          persistent-placeholder
          placeholder="Only alphabetic characters, numbers, dashes or underscores"
          :rules="[requiredValidator, alphaDashValidator]"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="email"
          persistent-placeholder
          placeholder="Must be a valid email"
          :rules="[requiredValidator, emailValidator]"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="validURL"
          persistent-placeholder
          placeholder="Must be a valid url"
          :rules="[requiredValidator, urlValidator]"
        />
      </VCol>

      <VCol cols="12">
        <VBtn
          type="submit"
          @click="refForm?.validate()"
        >
          Submit
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`,js:`<script setup>
import { VForm } from 'vuetify/components/VForm'

const requiredField = ref('')
const numberBetween10to20 = ref('')
const onlyConsistNumber = ref('')
const matchRegularEx = ref('')
const onlyAlphabeticCharacters = ref('')
const specifiedLength = ref('')
const password = ref('')
const digits = ref('')
const repeatPassword = ref('')
const onlyAlphabeticNumbersDashesUnderscores = ref('')
const email = ref('')
const validURL = ref('')
const refForm = ref()
<\/script>

<template>
  <VForm
    ref="refForm"
    @submit.prevent
  >
    <VRow>
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="requiredField"
          persistent-placeholder
          placeholder="This field is required"
          :rules="[requiredValidator]"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="numberBetween10to20"
          persistent-placeholder
          placeholder="Enter Number between 10 & 20"
          :rules="[requiredValidator, betweenValidator(numberBetween10to20, 10, 20)]"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="onlyConsistNumber"
          persistent-placeholder
          placeholder="Must only consist of numbers"
          :rules="[requiredValidator, integerValidator]"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="matchRegularEx"
          persistent-placeholder
          placeholder="Must match the specified regular expression : ^([0-9]+)$ - numbers only"
          :rules="[requiredValidator, regexValidator(matchRegularEx, '^([0-9]+)$')]"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="onlyAlphabeticCharacters"
          persistent-placeholder
          placeholder="Only alphabetic characters"
          :rules="[requiredValidator, alphaValidator]"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="specifiedLength"
          persistent-placeholder
          placeholder="Length must be exactly 3 characters."
          :rules="[requiredValidator, lengthValidator(specifiedLength, 3)]"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="password"
          persistent-placeholder
          placeholder="Password Input Field"
          type="password"
          :rules="[requiredValidator, passwordValidator]"
          autocomplete="on"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="digits"
          persistent-placeholder
          placeholder="The digits field must be numeric and exactly contain 3 digits"
          :rules="[requiredValidator, lengthValidator(digits, 3), integerValidator]"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="repeatPassword"
          persistent-placeholder
          placeholder="Repeat password must match"
          type="password"
          :rules="[requiredValidator, confirmedValidator(repeatPassword, password)]"
          autocomplete="on"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="onlyAlphabeticNumbersDashesUnderscores"
          persistent-placeholder
          placeholder="Only alphabetic characters, numbers, dashes or underscores"
          :rules="[requiredValidator, alphaDashValidator]"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="email"
          persistent-placeholder
          placeholder="Must be a valid email"
          :rules="[requiredValidator, emailValidator]"
        />
      </VCol>

      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="validURL"
          persistent-placeholder
          placeholder="Must be a valid url"
          :rules="[requiredValidator, urlValidator]"
        />
      </VCol>

      <VCol cols="12">
        <VBtn
          type="submit"
          @click="refForm?.validate()"
        >
          Submit
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`},ce=y({__name:"form-validation",setup(T){return(w,c)=>{const F=K,n=z,p=H,f=G;return P(),g(A,null,{default:o(()=>[l(d,{cols:"12"},{default:o(()=>[l(n,{title:"Simple Form Validation",code:e(Q)},{default:o(()=>[c[0]||(c[0]=L("p",null,[q("Use "),L("code",null,"Rules"),q(" prop to validate the input.")],-1)),l(F)]),_:1},8,["code"])]),_:1}),l(d,{cols:"12"},{default:o(()=>[l(n,{title:"Validating Multiple Rules",code:e(W)},{default:o(()=>[l(p)]),_:1},8,["code"])]),_:1}),l(d,{cols:"12"},{default:o(()=>[l(n,{title:"Validation Types",code:e(X)},{default:o(()=>[l(f)]),_:1},8,["code"])]),_:1})]),_:1})}}});export{ce as default};
