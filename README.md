# Chiarelli DDD + Yii2 App

Exemplo de projeto organizado segundo princípios **DDD (Domain / Application / Infrastructure)**, usando **Yii2** na camada de infraestrutura, com **testes unitários e funcionais**, além de **scripts/migrations** para criação de schema e seeds.

Este README inclui:

* 🚀 **Guia rápido** para subir a aplicação via Docker
* 🐳 Explicação do comportamento do container (testes + migrations automáticos)
* 📚 Resumo dos exercícios com links diretos para `docs/`

---

## 📦 Pré-requisitos

* **Docker 20.x** + **docker-compose** (ou Docker CLI com Compose v2)
* **4GB RAM** disponível (recomendado)
* **Porta 8080** livre no host — acesso em **[http://localhost:8080](http://localhost:8080)**

---

## 🚀 Execução rápida (via Docker)

1. **Clone o repositório**

```bash
git clone https://github.com/chiarelli/DddApp.git
cd DddApp
```

2. **Construa e suba os containers**

```bash
docker compose -f docker/docker-compose.yml up --build
```

O que acontece automaticamente:

* O container `db` (MySQL) sobe e aguarda healthcheck.
* O container `app` aguarda o DB e então:

  1. Executa **testes PHPUnit** (raiz do projeto)
  2. Executa **testes Codeception** (app Yii)
  3. Roda **migrations Yii**:

     ```bash
     php src/Infrastructure/Yii/yii migrate --interactive=0
     ```
  4. Só depois disso levanta o **Apache**.

Acesse em:

* [http://localhost:8080](http://localhost:8080)
* [http://0.0.0.0:8080](http://0.0.0.0:8080)

3. **Derrubar containers**

```bash
docker compose -f docker/docker-compose.yml down -v
```

---

## 💻 Execução local (sem Docker)

1. Instale **PHP 8.2+**, **Composer** e um **MySQL compatível**
2. Na raiz do projeto:

```bash
composer install
```

3. No app Yii:

```bash
cd src/Infrastructure/Yii
composer install
```

4. Configure `src/Infrastructure/Yii/config/db.php`
5. Rode migrations:

```bash
php src/Infrastructure/Yii/yii migrate
```

6. Configure seu Apache/nginx apontando o **DocumentRoot para**:
   `src/Infrastructure/Yii/web`

> No modo local, execute os testes manualmente quando quiser.

---

## 🧪 Testes

* **PHPUnit** (raiz):

  ```bash
  vendor/bin/phpunit --testdox
  ```

* **Codeception** (Yii):

  ```bash
  cd src/Infrastructure/Yii
  vendor/bin/codecept run
  ```

> No Docker, **todos os testes rodam automaticamente** antes das migrations.
> Se falhar, o container **não sobe**.

---

## 🌐 Porta e Binding

O compose expõe:

```
0.0.0.0:8080 -> container port 80
```

A aplicação responde em:

* [http://localhost:8080](http://localhost:8080)
* [http://0.0.0.0:8080](http://0.0.0.0:8080)

Se não responder, veja *Troubleshooting* abaixo.

---

## 📚 Resumo dos Exercícios (`docs/`)

* **Exercise 1 — Fundamentos Yii2 (registro de progresso)**
  [docs/exercise-1.md](docs/exercise-1.md)
  Implementação de login e criação de produto usando DDD Lite.

* **Exercise 2 — Migrations no Yii2**
  [docs/exercise-2.md](docs/exercise-2.md)
  Criação de `person` e pivot `customer_relationship_person` (N:N) com FKs e PK composta.

* **Exercise 3 — Fundamentos do Yii2 (DDD aplicado)**
  [docs/exercise-3.md](docs/exercise-3.md)
  VO Age, entidades Customer/Person, listagem, read repos, assemblers, views.

* **Exercise 4 — Conceitual (otimizações)**
  [docs/exercise-4.md](docs/exercise-4.md)
  Indexes, cache, read-replicas, CQRS.

* **Exercise 5 — Migrations + Performance + Cache**
  [docs/exercise-5.md](docs/exercise-5.md)
  Índices, read-model, caching avançado, TagDependency, seeds em larga escala.

---

## 🛠️ Troubleshooting

### Página não carrega?

**Verifique containers:**

```bash
docker compose -f docker/docker-compose.yml ps
```

**Logs do app:**

```bash
docker compose -f docker/docker-compose.yml logs -f app
```

**Status do DB:**

```bash
docker inspect --format='{{json .State.Health}}' <container>
```

Se falhar em testes ou migrations, aparecerá nos logs.

**Reiniciar do zero:**

```bash
docker compose -f docker/docker-compose.yml down -v
docker compose -f docker/docker-compose.yml up --build
```

### Porta 8080 ocupada?

* Verifique processos usando 8080
* Altere no compose:
  `0.0.0.0:<outraPorta>:80`

### Rodar migrations manualmente

```bash
php src/Infrastructure/Yii/yii migrate
```

---

## 💡 Notas operacionais

O entrypoint garante que o Apache só inicia **após**:

* DB pronto
* Testes passando
* Migrations aplicadas

Isso torna a imagem confiável para **CI/CD** e ambientes controlados.
