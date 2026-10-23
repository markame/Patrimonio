# Escola Patrimônio

Sistema responsivo de gestão patrimonial escolar em **Laravel 13, PHP 8.3+, Bootstrap 5.3 e MySQL 8**. As classes de domínio e os controladores estão em português do Brasil.

## Funcionalidades

- Acesso administrativo com login, proteção CSRF e limite de tentativas.
- Painel com totais reais de itens em uso, em manutenção e chamados pendentes.
- Cadastro e edição de nome, marca, número de série, foto, voltagem, localização, categoria, situação e observações internas. A data é registrada automaticamente.
- Número patrimonial sequencial e único (`PAT-000001`), sem reutilização. A geração usa a chave do banco em uma transação, evitando duplicações em cadastros simultâneos.
- Pesquisa por nome, número, série e localização; filtro por situação e paginação.
- Etiqueta individual de **80 × 45 mm**, com número e QR Code gerado localmente em SVG. Imprimir em papel adesivo, escala 100%, sem cabeçalhos e rodapés.
- Página pública com identificador UUID para consulta da ficha e histórico pelo QR Code. Não exige conta, mas solicita nome completo e e-mail ou telefone antes de liberar a consulta. A identificação é mantida na sessão e pode ser encerrada pelo botão Trocar identificação.
- Relatos de reparo, mau funcionamento ou informação de reparo usam o nome e o contato da identificação da sessão, sem permitir alterá-los pelo envio do formulário. Cada envio gera um protocolo (`OS-000001`). Limite de cinco envios por minuto por origem.
- Atendimento administrativo com prioridade, situação e solução obrigatória para concluir. Conclusões e reaberturas são registradas no histórico.
- Contatos dos solicitantes, observações internas e notas de atendimento ficam restritos à administração. O histórico público apresenta movimentações e situações dos chamados.

## Executar no Laragon

O projeto está em `C:\laragon\www\Patrimonio`. Neste ambiente o MySQL foi configurado com o banco `patrimonio_escolar` e seis itens identificados como demonstração.

1. Inicie o MySQL pelo Laragon.
2. No terminal do Laragon, execute `php artisan serve --host=localhost --port=8000` ou, no PowerShell, `powershell -ExecutionPolicy Bypass -File .\iniciar.ps1`.
3. Abra [http://localhost:8000](http://localhost:8000).
4. Crie o primeiro acesso com `php artisan escola:administrador seu-email@escola.com --nome="Seu nome"`. O comando pede uma senha de pelo menos dez caracteres, sem exibi-la.

Não há senha padrão nem cadastro público. Somente o administrador pode acessar Usuários e cadastrar novas contas. Novas contas têm perfil comum e não podem criar usuários. A primeira conta existente foi preservada como administradora. O comando escola:administrador serve apenas à configuração inicial, quando ainda não há usuários.

Para usar um domínio virtual do Laragon, configure o **DocumentRoot para a pasta `public`**, nunca para a raiz do projeto, e ajuste `APP_URL` no `.env`.

## Instalação em outro ambiente

Requisitos: PHP 8.3+ com PDO MySQL, mbstring, intl, DOM/XML, fileinfo, GD e as extensões exigidas pelo Laravel; Composer e MySQL. A extensão ZIP facilita a instalação. Não é necessário Node nem compilar assets: o Bootstrap está em `public/vendor/bootstrap`.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Crie um banco MySQL UTF-8 e configure no `.env`:

```dotenv
APP_NAME="Escola Patrimônio"
APP_URL=http://localhost:8000
APP_LOCALE=pt_BR
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=patrimonio_escolar
DB_USERNAME=root
DB_PASSWORD=
SESSION_DRIVER=file
CACHE_STORE=file
```

```powershell
php artisan migrate
php artisan storage:link
php artisan escola:administrador seu-email@escola.com
php artisan serve
```

`php artisan db:seed --class=DemonstracaoSeeder` é opcional e insere somente exemplos, sem criar usuários. O seeder é idempotente e identifica os exemplos pela série `DEMO-*`.

## QR Code em celulares

Antes de imprimir etiquetas definitivas, configure `APP_URL` com um endereço que os celulares consigam acessar. `localhost` aponta para o próprio celular e não para o servidor da escola.

Na rede local, use o IP do computador servidor, por exemplo `APP_URL=http://192.168.1.50:8000`, e inicie com `php artisan serve --host=0.0.0.0 --port=8000`, autorizando a porta no firewall da rede privada. Em produção use um domínio HTTPS. Depois de alterar o endereço, execute `php artisan config:clear` e reimprima as etiquetas. O endereço está gravado dentro do QR Code.

O QR Code é um link padrão: leitores de QR Code das câmeras compatíveis abrem a página. A câmera precisa reconhecer QR Codes e ter acesso à rede do servidor. O sistema não exige um aplicativo próprio.

## Fotos e impressão

Fotos opcionais em JPEG, PNG ou WebP, até 5 MB. Configure também `upload_max_filesize=6M` e `post_max_size=8M` no PHP que atende o site. O script `iniciar.ps1` já aplica esses limites ao servidor local. No Windows, a criação de `public/storage` pode precisar de terminal elevado ou Modo de Desenvolvedor. Confira se o vínculo existe após `storage:link`.

A etiqueta usa medidas físicas CSS. Confira uma impressão de teste antes de colar. Na lista de patrimônios, Baixar etiquetas A4 gera um PDF com todos os itens em ordem numérica, da esquerda para a direita e de cima para baixo. São 10 etiquetas de 90 × 48 mm por página, duas colunas e cinco linhas, com margens de 10 mm. Imprima em escala 100%. Para folhas pré-cortadas, confira a compatibilidade das medidas com o papel usado. A geração usa dompdf/dompdf; ao publicar, atualize composer.json, composer.lock e as dependências da pasta vendor ou execute composer install --no-dev no servidor.

## Testes

```powershell
php vendor/phpunit/phpunit/phpunit --testdox
```

Os testes usam SQLite em memória, separado do MySQL real. Cobrem geração de número, upload, histórico, busca, série única, proteção de rotas, login/logout, QR Code, abertura pública e privacidade, conclusão e reabertura de ordens de serviço. As migrações também foram aplicadas ao MySQL local.

## Uso em produção

Configure HTTPS, `APP_ENV=production`, `APP_DEBUG=false`, credenciais próprias do MySQL, backups e permissões de escrita para `storage` e `bootstrap/cache`. A página pública expõe a ficha e o histórico de quem possui a etiqueta; mantenha esses registros livres de informações pessoais. O servidor embutido do PHP serve apenas para desenvolvimento. Use Apache/Nginx com a raiz em `public`.

A situação do item é gerenciada na ficha patrimonial e é independente da situação de cada chamado: concluir um chamado não muda automaticamente o item para “Em uso”, pois ele pode ter outros reparos pendentes. A baixa preserva o item e seu histórico; não há exclusão destrutiva pela interface.
