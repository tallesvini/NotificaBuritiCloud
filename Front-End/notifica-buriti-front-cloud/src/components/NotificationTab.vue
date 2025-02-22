<template>
  <div width="100%" height="100%" style="background-color: #525659; width: 100%;">
    <div class="d-flex justify-space-between align-center"
      style="position: fixed; width: 100%; height: 8vh; background-color: #323639; position: relative;">
      <div></div>
      <div class="d-flex justify-center align-center flex-column mt-2">
        <h2 style="color: #97AF83;">
          {{ title }}
        </h2>
        <h2 style="color: #FFF;">
           {{ description }}
        </h2>
      </div>
      <div></div>
    </div>

    <div class="d-flex justify-center" style="height: calc(100vh - 20vh); overflow-y: auto;">
      <iframe id="iframeContent" :src="pdfUrl" style="width: 100vw; height: 100%; border: none;"></iframe>
    </div>

    <div class="d-flex justify-center align-center pb-12 mb-4 pt-6">
      <v-btn class="mx-2" append-icon="mdi-link-variant" variant="outlined" color="#FFF" :href="urlAttachment"
        target="_blank" rel="noopener noreferrer">
        Acessar Detalhes
      </v-btn>
      <v-btn class="mx-2" append-icon="mdi-check-circle" variant="flat" color="#F27405" @click="markAsSeenNotification">
        Estou ciente
      </v-btn>
    </div>

    <div class="d-flex justify-end px-4 mt-5 py-1"
      style="position: fixed; bottom: 0; left: 0; width: 100%; background-color: #525659; border-top: 1px solid #323639;">
      <p style="font-size: 14px; color: #FFFFFF; font-weight: 400;">
        Identificador: {{ identifierKey }}
      </p>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import jsPDF from "jspdf";
import { useRoute } from "vue-router"; // Importa Vue Router

export default defineComponent({
  data() {
    return {
      socket: null as WebSocket | null,
      pdfUrl: "", 
      statusMessage: "Conectando...",
      identifierKey: "", // Exemplo de identificador\
      contentHtml: null,
      title: "" as string,
      description: "" as string
    };
  },
  created() {
    const urlParams = new URLSearchParams(window.location.search);
    this.identifierKey = urlParams.get("nid") || "Nenhum nid encontrado";
    this.connectWebSocket();
  },
  methods: {
    // 📌 Conectar ao WebSocket automaticamente
    connectWebSocket() {
      this.socket = new WebSocket("ws://192.168.100.175:8080/chat"); // Substitua pelo seu servidor

      this.socket.onopen = () => {
        this.statusMessage = "✅ Conectado ao WebSocket.";
        console.log("✅ Conectado ao WebSocket!");
        this.sendMessage("searching_for_id");
      };

      this.socket.onmessage = (event) => {
        try {
          const jsonData = JSON.parse(event.data);
          console.log("📩 Mensagem recebida:", jsonData.message);
          this.title = jsonData.message.title;
          this.description =  jsonData.message.description;
          this.convertHtmlToPdf(jsonData.message.body);
        } catch (error) {
          console.error("❌ Erro ao processar a mensagem JSON:", error);
        }
      };

      this.socket.onclose = () => {
        this.statusMessage = "❌ Desconectado.";
        console.log("❌ Desconectado do WebSocket.");
      };
    },

    // 📌 Função para enviar código ao ID 49
    sendMessage(typeRequest: string) {
      if (!this.socket || this.socket.readyState !== WebSocket.OPEN) {
        console.log("⚠️ Conexão WebSocket não está aberta.");
        return;
      }

      const messageData = {
        to: 49,
        message: this.identifierKey,
        type: typeRequest
      };

      this.socket.send(JSON.stringify(messageData));
      console.log("📤 Código enviado para ID 49.");
    },

    // 📌 Função para converter HTML para PDF e exibir no iframe
    convertHtmlToPdf(htmlContent: string) {
      if (!htmlContent.trim()) {
        console.error("❌ HTML está vazio! O PDF não pode ser gerado.");
        return;
      }

      const doc = new jsPDF({
        unit: "mm",
        format: "a4",
        orientation: "portrait",
      });

      doc.html(htmlContent, {
        callback: (doc) => {
          const pdfBlob = doc.output("blob");
          this.pdfUrl = URL.createObjectURL(pdfBlob);
          console.log("✅ PDF gerado e exibido.");
        },
        x: 10,
        y: 10,
        width: 180,
        windowWidth: 800,
      });
    },

    markAsSeenNotification() {
      this.sendMessage("mark_as_seen")
    }
  },
  
});
</script>

<style scoped>
.v-card {
  max-width: 600px;
  margin: auto;
}
</style>
