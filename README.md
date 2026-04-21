# Invoice Recovery (Segunda Via) - WHMCS Addon

O **Invoice Recovery (Segunda Via)** é um módulo avançado para WHMCS que facilita a recuperação e o pagamento de faturas pendentes pelos seus clientes de forma rápida, segura e sem a necessidade de login manual completo, oferecendo uma interface moderna e otimizada.

## 🚀 Funcionalidades Principais

### 🔍 Busca e Recuperação Inteligente

* **Busca Flexível:** Permite que o cliente localize faturas pendentes utilizando seu **E-mail** ou **CPF/CNPJ**.
* **Validação Automática:** Sistema integrado de validação de formatos para CPF e CNPJ.
* **Interface AJAX:** Consultas rápidas em tempo real sem recarregamento de página.

### 💳 Integração de Pagamentos Expressos

* **Botões Diretos:** Atalhos rápidos para pagamento via **PIX**, **Boleto** e **Cartão de Crédito**.
* **Mapeamento de Gateways:** Configure qual gateway do seu WHMCS será acionado para cada tipo de pagamento.
* **Atualização Automática:** Ao clicar em um método de pagamento, o módulo atualiza automaticamente a fatura para o gateway correspondente antes do redirecionamento.

### 🛡️ Segurança e Controle de Acesso

* **Login SSO Restrito:** Gera um token de acesso seguro para o cliente visualizar a fatura, limitando a navegação apenas à página da fatura (caso o cliente tente acessar outras áreas da conta, a sessão é encerrada).
* **Rate Limiting (Anti-Brute Force):** Proteção baseada em IP com limite configurável de tentativas e tempo de bloqueio para evitar abusos.
* **Bloqueio por Cliente:** Opção de desativar a funcionalidade de 2ª via para clientes específicos através de campos personalizados.
* **Controle Centralizado:** Ative ou desative o portal e métodos de pagamento específicos diretamente na configuração do addon.

### 🎨 Experiência do Usuário (UI/UX) Premium

* **Design Moderno:** Interface "Eye-candy" com suporte total a **Modo Claro (Light)** e **Modo Escuro (Dark)**.
* **Responsividade:** Otimizado para dispositivos móveis, tablets e desktops.
* **Micro-animações:** Transições suaves para uma experiência mais fluida e profissional.

### 🌍 Internacionalização

* **Multi-idioma:** Suporte nativo para **Português (BR)** e **Inglês**.
* **Detecção Automática:** Identifica o idioma da sessão do usuário no WHMCS.

---

## 🛠️ Instalação

1. Faça o upload da pasta `modules/addons/invoice_recovery` para o diretório `/modules/addons/` do seu WHMCS.
2. Faça o upload do arquivo `segunda-via.php` para a **raiz** da sua instalação WHMCS.
3. No painel administrativo do WHMCS, vá em **System Settings > Addon Modules**.
4. Localize o **Invoice Recovery (2ª via)** e clique em **Activate**.
5. Clique em **Configure** para preencher as credenciais de API e mapear os campos e gateways.

## ⚙️ Configuração Necessária

Para o funcionamento correto das funções de pagamento e SSO:

1. Vá em **System Settings > API Credentials**.
2. Crie um novo par de credenciais e insira o **Identifier** e **Secret** nas configurações do módulo.
3. Certifique-se de que o usuário administrador vinculado às credenciais tenha permissões para `UpdateInvoice` e `CreateSsoToken`.

## 📂 Estrutura do Projeto

* `segunda-via.php`: Arquivo principal da interface do cliente.
* `/modules/addons/invoice_recovery/`:
  * `invoice_recovery.php`: Lógica backend e configurações do addon.
  * `/lang/`: Arquivos de tradução (PT-BR, EN).
  * `/templates/`: Template Smarty (`clientarea.tpl`).
  * `/assets/`: Estilização CSS e recursos visuais.
  * `/hooks/`: Hooks de segurança para restrição de sessão.

---