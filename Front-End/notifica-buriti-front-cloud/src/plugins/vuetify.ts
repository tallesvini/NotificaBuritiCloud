// src/plugins/vuetify.ts
import { createVuetify } from 'vuetify';
import 'vuetify/lib/styles/main.css';
import { ref } from 'vue';

// Componentes e diretivas podem ser importados conforme a necessidade
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import { VTreeview } from 'vuetify/labs/VTreeview'
import '@mdi/font/css/materialdesignicons.css';

// Referência reativa para o tema
export const themeDark = ref(true);

// Traduções providas pelo Vuetify
import { pt } from "vuetify/locale";

const customComponents = {
  ...components,
  VTreeview,
};

// Configuração do Vuetify
const vuetify = createVuetify({
  locale: {
    locale: "pt",
    messages: { pt },
  },
  theme: {
    defaultTheme: themeDark.value ? 'dark' : 'light',
  },
  directives,
  components: customComponents,
  icons: {
    defaultSet: 'mdi', // 📌 Definir os ícones padrão como MDI
  },
});

export default vuetify;