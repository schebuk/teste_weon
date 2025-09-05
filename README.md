📥 Instalação
1. Clone o repositório
```bash
git clone https://github.com/schebuk/teste_weon
```
cd api-ecommerce
2. Configure o ambiente
```bash
cp .env.example .env
```
⚙️ Configuração
Edite o arquivo .env:
```env
APP_NAME=EcommerceAPI
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=ecommerce
DB_USERNAME=laravel
DB_PASSWORD=password

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
```
🚀 Execução
1. Inicie os containers
```bash
docker-compose up -d --build
```
2. Configure a aplicação
```bash
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
docker-compose exec app chmod -R 775 storage bootstrap/cache
```
3. Verifique a aplicação
Acesse: http://localhost:8000

🧪 Testes
Configure o ambiente de teste
```bash
docker-compose exec app cp .env .env.testing
docker-compose exec app sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' .env.testing
docker-compose exec app sed -i 's/DB_DATABASE=ecommerce/DB_DATABASE=:memory:/' .env.testing
```
Execute os testes
```bash
docker-compose exec app php artisan test
```
📡 Endpoints da API
🔐 Autenticação
POST /api/auth/login - Login de usuário

👥 Usuários (Autenticado)
GET /api/users - Listar usuários

POST /api/users - Criar usuário

GET /api/users/{id} - Detalhes do usuário

PUT /api/users/{id} - Atualizar usuário

DELETE /api/users/{id} - Deletar usuário

📦 Pedidos (Autenticado)
GET /api/orders - Listar pedidos

POST /api/orders - Criar pedido

GET /api/orders/{id} - Detalhes do pedido

PUT /api/orders/{id} - Atualizar pedido

DELETE /api/orders/{id} - Deletar pedido

💡 Exemplo de uso:
``` bash
# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "teste@email.com", "senha": "senha123"}'

# Criar usuário
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -d '{"nome": "João Silva", "email": "joao@email.com", "senha": "senha123"}'

# Criar pedido (com token)
curl -X POST http://localhost:8000/api/orders \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <seu-token>" \
  -d '{"descricao": "Notebook Dell", "valor": 3500.50, "moeda": "BRL"}'

# Listar pedidos (com token)
curl -X GET http://localhost:8000/api/orders \
  -H "Authorization: Bearer <seu-token>"
  
```
🐛 Solução de Problemas

🔧 Erro de porta em uso:
Altere no docker-compose.yml:

```yaml
ports:
  - "3307:3306"
```
🔧 Erro de permissões:
```bash
docker-compose exec app chmod -R 775 storage bootstrap/cache
```
🔧 Reconstruir containers:
```bash
docker-compose down
docker-compose up -d --build
```
🔧 Verificar logs:
```bash
docker-compose logs app
docker-compose logs nginx
docker-compose logs db
```
🔧 Acessar banco de dados:
```bash
docker-compose exec db mysql -u laravel -ppassword ecommerce
```
🎯 Comandos Úteis
```bash
# Parar os containers
docker-compose down

# Reiniciar os containers
docker-compose restart

# Acessar terminal do container
docker-compose exec app bash

# Verificar status dos containers
docker-compose ps

# Verificar logs em tempo real
docker-compose logs -f app

# Executar testes específicos
docker-compose exec app php artisan test tests/Feature/AuthTest.php
docker-compose exec app php artisan test tests/Feature/UserTest.php
docker-compose exec app php artisan test tests/Feature/OrderTest.php
```